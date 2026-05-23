<?php

namespace TitaKita\Services\Domain\Booking;

use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Throwable;
use TitaKita\DomainObjects\Enums\ProductPriceType;
use TitaKita\DomainObjects\Enums\ProductType;
use TitaKita\DomainObjects\Enums\ScheduleScopeType;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\Generated\ProductCategoryDomainObjectAbstract;
use TitaKita\DomainObjects\Generated\ProductDomainObjectAbstract;
use TitaKita\DomainObjects\ProductCategoryDomainObject;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\DomainObjects\ScheduleDomainObject;
use TitaKita\Helper\DateHelper;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductCategoryRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductPriceRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;
use TitaKita\Services\Domain\Booking\Exception\ScheduleRangeTooLongException;

class ScheduleGenerationService
{
    private const MAX_RANGE_MONTHS = 3;

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductPriceRepositoryInterface $productPriceRepository,
        private readonly EventRepositoryInterface $eventRepository,
        private readonly ProductCategoryRepositoryInterface $productCategoryRepository,
        private readonly DatabaseManager $databaseManager,
    ) {}

    /**
     * Turn a Schedule's configuration into the set of (date x start_time) session-products.
     *
     * Idempotent: regenerating reconciles the set. Session-products that already have
     * bookings are never removed; empty session-products no longer in the target set are deleted.
     *
     * @throws ScheduleRangeTooLongException
     * @throws Throwable
     */
    public function generate(ScheduleDomainObject $schedule): void
    {
        $event = $this->eventRepository->findById($schedule->getEventId());
        $timezone = $event->getTimezone() ?? config('app.default_timezone');
        $categoryId = $this->resolveDefaultCategoryId($event->getId());

        $targets = $this->buildTargetSessions($schedule, $timezone);

        $this->databaseManager->transaction(function () use ($schedule, $event, $timezone, $categoryId, $targets) {
            $this->reconcile($schedule, $event, $timezone, $categoryId, $targets);
        });
    }

    /**
     * Session-products must belong to the event's default category, otherwise the public
     * event endpoint (which buckets products under their category) silently drops them and
     * checkout can't resolve the product to collect attendee details.
     */
    private function resolveDefaultCategoryId(int $eventId): int
    {
        return $this->productCategoryRepository
            ->findWhere([ProductCategoryDomainObjectAbstract::EVENT_ID => $eventId])
            ->sortBy(fn (ProductCategoryDomainObject $category) => $category->getOrder())
            ->first()
            ->getId();
    }

    /**
     * @return array<string, array{date: string, start_time: string}> keyed by the UTC start datetime
     *
     * @throws ScheduleRangeTooLongException
     */
    private function buildTargetSessions(ScheduleDomainObject $schedule, string $timezone): array
    {
        $dates = $this->resolveDates($schedule);
        $startTimes = $this->toArray($schedule->getStartTimes());

        $targets = [];

        foreach ($dates as $date) {
            foreach ($startTimes as $startTime) {
                $utcStart = $this->toUtcDateTime($date, $startTime, $timezone);
                $targets[$utcStart] = [
                    'date' => $date,
                    'start_time' => $startTime,
                ];
            }
        }

        return $targets;
    }

    /**
     * @return string[] list of Y-m-d dates the schedule covers
     *
     * @throws ScheduleRangeTooLongException
     */
    private function resolveDates(ScheduleDomainObject $schedule): array
    {
        $scope = ScheduleScopeType::from($schedule->getScopeType());

        return match ($scope) {
            ScheduleScopeType::SINGLE_DAY => [
                $this->normaliseDate($schedule->getRangeStartDate()),
            ],
            ScheduleScopeType::SPECIFIC_DATES => array_map(
                fn (string $date) => $this->normaliseDate($date),
                $this->toArray($schedule->getSpecificDates()),
            ),
            ScheduleScopeType::RECURRING_WEEKLY => $this->resolveRecurringDates($schedule),
        };
    }

    /**
     * @return string[]
     *
     * @throws ScheduleRangeTooLongException
     */
    private function resolveRecurringDates(ScheduleDomainObject $schedule): array
    {
        $start = Carbon::parse($this->normaliseDate($schedule->getRangeStartDate()));
        $end = Carbon::parse($this->normaliseDate($schedule->getRangeEndDate()));

        if ($start->copy()->addMonthsNoOverflow(self::MAX_RANGE_MONTHS)->lessThan($end)) {
            throw new ScheduleRangeTooLongException(
                __('Recurring schedules cannot span more than :months months.', [
                    'months' => self::MAX_RANGE_MONTHS,
                ])
            );
        }

        $weekdays = array_map('intval', $this->toArray($schedule->getWeekdays()));
        $dates = [];

        for ($cursor = $start->copy(); $cursor->lessThanOrEqualTo($end); $cursor->addDay()) {
            if (in_array($cursor->dayOfWeek, $weekdays, true)) {
                $dates[] = $cursor->format('Y-m-d');
            }
        }

        return $dates;
    }

    /**
     * @param  array<string, array{date: string, start_time: string}>  $targets
     *
     * @throws Throwable
     */
    private function reconcile(
        ScheduleDomainObject $schedule,
        EventDomainObject $event,
        string $timezone,
        int $categoryId,
        array $targets,
    ): void {
        $existing = $this->productRepository
            ->loadRelation(ProductPriceDomainObject::class)
            ->findWhere([ProductDomainObjectAbstract::SCHEDULE_ID => $schedule->getId()]);

        $existingByStart = [];
        foreach ($existing as $product) {
            $existingByStart[$this->utcKey($product->getSessionStartAt())] = $product;
        }

        foreach ($existing as $product) {
            $key = $this->utcKey($product->getSessionStartAt());

            if (isset($targets[$key])) {
                continue;
            }

            if ($this->hasBookings($product)) {
                continue;
            }

            $this->removeSessionProduct($product);
        }

        $order = $this->nextOrder($event->getId());

        foreach ($targets as $utcStart => $target) {
            if (isset($existingByStart[$utcStart])) {
                continue;
            }

            $this->createSessionProduct(
                schedule: $schedule,
                event: $event,
                categoryId: $categoryId,
                timezone: $timezone,
                date: $target['date'],
                startTime: $target['start_time'],
                order: $order++,
            );
        }
    }

    private function createSessionProduct(
        ScheduleDomainObject $schedule,
        EventDomainObject $event,
        int $categoryId,
        string $timezone,
        string $date,
        string $startTime,
        int $order,
    ): void {
        $localStart = Carbon::parse($date.' '.$startTime, $timezone);
        $localEnd = $localStart->copy()->addMinutes($schedule->getSessionDurationMinutes());

        $product = $this->productRepository->create([
            'title' => $this->buildTitle($localStart, $localEnd),
            'type' => ProductPriceType::FREE->name,
            'product_type' => ProductType::TICKET->name,
            'product_category_id' => $categoryId,
            'order' => $order,
            'event_id' => $event->getId(),
            'schedule_id' => $schedule->getId(),
            'session_start_at' => DateHelper::convertToUTC($date.' '.$startTime, $timezone),
            'session_end_at' => DateHelper::convertToUTC($localEnd->format('Y-m-d H:i:s'), $timezone),
        ]);

        $this->productPriceRepository->create([
            'product_id' => $product->getId(),
            'price' => 0,
            'label' => $this->buildTitle($localStart, $localEnd),
            'initial_quantity_available' => $schedule->getCapacityPerSession(),
            'is_hidden' => false,
            'order' => 1,
        ]);
    }

    private function nextOrder(int $eventId): int
    {
        return ($this->productRepository
            ->findWhere([ProductDomainObjectAbstract::EVENT_ID => $eventId])
            ->max(static fn (ProductDomainObject $product) => $product->getOrder()) ?? 0) + 1;
    }

    private function removeSessionProduct(ProductDomainObject $product): void
    {
        $this->productPriceRepository->deleteWhere([
            'product_id' => $product->getId(),
        ]);

        $this->productRepository->deleteWhere([
            ProductDomainObjectAbstract::ID => $product->getId(),
        ]);
    }

    private function hasBookings(ProductDomainObject $product): bool
    {
        $prices = $product->getProductPrices();

        if ($prices instanceof Collection) {
            foreach ($prices as $price) {
                if ($price->getQuantitySold() > 0) {
                    return true;
                }
            }
        }

        return $this->productRepository->hasAssociatedOrders($product->getId());
    }

    private function buildTitle(Carbon $localStart, Carbon $localEnd): string
    {
        return sprintf(
            '%s %s–%s',
            $localStart->format('Y-m-d'),
            $localStart->format('H:i'),
            $localEnd->format('H:i'),
        );
    }

    private function toUtcDateTime(string $date, string $startTime, string $timezone): string
    {
        return $this->utcKey(DateHelper::convertToUTC($date.' '.$startTime, $timezone));
    }

    private function utcKey(?string $dateTime): string
    {
        return Carbon::parse((string) $dateTime, 'UTC')->format('Y-m-d H:i:s');
    }

    private function normaliseDate(?string $date): string
    {
        return Carbon::parse((string) $date)->format('Y-m-d');
    }

    /**
     * @return array<int, mixed>
     */
    private function toArray(array|string|null $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return (array) json_decode($value, true);
        }

        return [];
    }
}
