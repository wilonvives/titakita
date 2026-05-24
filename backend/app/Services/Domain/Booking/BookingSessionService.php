<?php

declare(strict_types=1);

namespace TitaKita\Services\Domain\Booking;

use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Throwable;
use TitaKita\DomainObjects\Enums\EventType;
use TitaKita\DomainObjects\Enums\ProductPriceType;
use TitaKita\DomainObjects\Enums\ProductType;
use TitaKita\DomainObjects\Enums\ScheduleScopeType;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\Generated\EventDomainObjectAbstract;
use TitaKita\DomainObjects\Generated\ProductCategoryDomainObjectAbstract;
use TitaKita\DomainObjects\Generated\ProductDomainObjectAbstract;
use TitaKita\DomainObjects\Generated\ProductPriceDomainObjectAbstract;
use TitaKita\DomainObjects\Generated\ScheduleDomainObjectAbstract;
use TitaKita\DomainObjects\ProductCategoryDomainObject;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\DomainObjects\ScheduleDomainObject;
use TitaKita\Helper\DateHelper;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductCategoryRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductPriceRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;
use TitaKita\Repository\Interfaces\ScheduleRepositoryInterface;
use TitaKita\Services\Domain\Booking\Exception\SessionHasBookingsException;
use TitaKita\Services\Domain\Booking\Exception\SessionNotFoundException;

class BookingSessionService
{
    private const MAX_REPEAT_MONTHS = 3;

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductPriceRepositoryInterface $productPriceRepository,
        private readonly ProductCategoryRepositoryInterface $productCategoryRepository,
        private readonly ScheduleRepositoryInterface $scheduleRepository,
        private readonly EventRepositoryInterface $eventRepository,
        private readonly DatabaseManager $databaseManager,
    ) {}

    /**
     * Find-or-create the event's single schedule row that holds the booking defaults.
     * Also marks the event as a booking event.
     */
    public function ensureSchedule(int $eventId, int $defaultDurationMinutes, ?int $defaultCapacity): ScheduleDomainObject
    {
        $existing = $this->scheduleRepository->findFirstWhere([
            ScheduleDomainObjectAbstract::EVENT_ID => $eventId,
        ]);

        if ($existing !== null) {
            $this->scheduleRepository->updateFromArray($existing->getId(), [
                ScheduleDomainObjectAbstract::SESSION_DURATION_MINUTES => $defaultDurationMinutes,
                ScheduleDomainObjectAbstract::CAPACITY_PER_SESSION => $defaultCapacity,
            ]);
            $scheduleId = $existing->getId();
        } else {
            $scheduleId = $this->scheduleRepository->create([
                ScheduleDomainObjectAbstract::EVENT_ID => $eventId,
                ScheduleDomainObjectAbstract::SESSION_DURATION_MINUTES => $defaultDurationMinutes,
                ScheduleDomainObjectAbstract::CAPACITY_PER_SESSION => $defaultCapacity,
                ScheduleDomainObjectAbstract::SCOPE_TYPE => ScheduleScopeType::SPECIFIC_DATES->value,
                ScheduleDomainObjectAbstract::START_TIMES => [],
                ScheduleDomainObjectAbstract::WEEKDAYS => null,
                ScheduleDomainObjectAbstract::RANGE_START_DATE => null,
                ScheduleDomainObjectAbstract::RANGE_END_DATE => null,
                ScheduleDomainObjectAbstract::SPECIFIC_DATES => null,
            ])->getId();
        }

        $this->eventRepository->updateFromArray($eventId, [
            EventDomainObjectAbstract::EVENT_TYPE => EventType::BOOKING->value,
        ]);

        /** @var ScheduleDomainObject $schedule */
        $schedule = $this->scheduleRepository->findById($scheduleId);

        return $schedule;
    }

    /**
     * Create one or more session-products for the given date(s).
     *
     * @throws Throwable
     */
    public function createSessions(
        int $eventId,
        string $date,
        string $startTime,
        int $durationMinutes,
        ?int $capacity,
        ?string $description,
        bool $repeatWeekly,
    ): void {
        $this->databaseManager->transaction(function () use (
            $eventId,
            $date,
            $startTime,
            $durationMinutes,
            $capacity,
            $description,
            $repeatWeekly,
        ): void {
            $schedule = $this->ensureSchedule($eventId, $durationMinutes, $capacity);

            $event = $this->eventRepository->findById($eventId);
            $timezone = $event->getTimezone() ?? config('app.default_timezone');
            $categoryId = $this->resolveDefaultCategoryId($eventId);

            $existing = $this->productRepository
                ->findWhere([ProductDomainObjectAbstract::SCHEDULE_ID => $schedule->getId()]);

            $existingByStart = [];
            foreach ($existing as $product) {
                $existingByStart[$this->utcKey($product->getSessionStartAt())] = true;
            }

            $order = $this->nextOrder($eventId);

            foreach ($this->buildTargetDates($date, $repeatWeekly) as $targetDate) {
                $utcStart = $this->utcKey(DateHelper::convertToUTC($targetDate.' '.$startTime, $timezone));

                if (isset($existingByStart[$utcStart])) {
                    continue;
                }

                $this->createSessionProduct(
                    schedule: $schedule,
                    event: $event,
                    categoryId: $categoryId,
                    timezone: $timezone,
                    date: $targetDate,
                    startTime: $startTime,
                    durationMinutes: $durationMinutes,
                    capacity: $capacity,
                    description: $description,
                    order: $order++,
                );

                $existingByStart[$utcStart] = true;
            }
        });
    }

    /**
     * @throws SessionNotFoundException
     */
    public function updateSession(
        int $eventId,
        int $productId,
        string $date,
        string $startTime,
        int $durationMinutes,
        ?int $capacity,
        ?string $description,
    ): void {
        $product = $this->findSessionForEvent($eventId, $productId);

        $event = $this->eventRepository->findById($eventId);
        $timezone = $event->getTimezone() ?? config('app.default_timezone');

        $localStart = Carbon::parse($date.' '.$startTime, $timezone);
        $localEnd = $localStart->copy()->addMinutes($durationMinutes);

        $this->productRepository->updateFromArray($product->getId(), [
            ProductDomainObjectAbstract::TITLE => $this->buildTitle($localStart, $localEnd),
            ProductDomainObjectAbstract::DESCRIPTION => $description,
            ProductDomainObjectAbstract::SESSION_START_AT => DateHelper::convertToUTC($date.' '.$startTime, $timezone),
            ProductDomainObjectAbstract::SESSION_END_AT => DateHelper::convertToUTC($localEnd->format('Y-m-d H:i:s'), $timezone),
        ]);

        $this->productPriceRepository->updateWhere(
            [
                ProductPriceDomainObjectAbstract::LABEL => $this->buildTitle($localStart, $localEnd),
                ProductPriceDomainObjectAbstract::INITIAL_QUANTITY_AVAILABLE => $capacity,
            ],
            [ProductPriceDomainObjectAbstract::PRODUCT_ID => $product->getId()],
        );
    }

    /**
     * @throws SessionNotFoundException
     * @throws SessionHasBookingsException
     */
    public function deleteSession(int $eventId, int $productId): void
    {
        $product = $this->findSessionForEvent($eventId, $productId);

        if ($this->hasBookings($product)) {
            throw new SessionHasBookingsException(
                __('This session has bookings and cannot be deleted.')
            );
        }

        $this->removeSessionProduct($product);
    }

    /**
     * @throws SessionNotFoundException
     */
    private function findSessionForEvent(int $eventId, int $productId): ProductDomainObject
    {
        /** @var ProductDomainObject|null $product */
        $product = $this->productRepository
            ->loadRelation(ProductPriceDomainObject::class)
            ->findFirstWhere([
                ProductDomainObjectAbstract::ID => $productId,
                ProductDomainObjectAbstract::EVENT_ID => $eventId,
            ]);

        if ($product === null || $product->getScheduleId() === null) {
            throw new SessionNotFoundException(
                __('Session not found.')
            );
        }

        return $product;
    }

    /**
     * @return string[]
     */
    private function buildTargetDates(string $date, bool $repeatWeekly): array
    {
        if (! $repeatWeekly) {
            return [Carbon::parse($date)->format('Y-m-d')];
        }

        $start = Carbon::parse($date);
        $end = $start->copy()->addMonthsNoOverflow(self::MAX_REPEAT_MONTHS);

        $dates = [];
        for ($cursor = $start->copy(); $cursor->lessThanOrEqualTo($end); $cursor->addDays(7)) {
            $dates[] = $cursor->format('Y-m-d');
        }

        return $dates;
    }

    private function createSessionProduct(
        ScheduleDomainObject $schedule,
        EventDomainObject $event,
        int $categoryId,
        string $timezone,
        string $date,
        string $startTime,
        int $durationMinutes,
        ?int $capacity,
        ?string $description,
        int $order,
    ): void {
        $localStart = Carbon::parse($date.' '.$startTime, $timezone);
        $localEnd = $localStart->copy()->addMinutes($durationMinutes);

        $product = $this->productRepository->create([
            'title' => $this->buildTitle($localStart, $localEnd),
            'description' => $description,
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
            'initial_quantity_available' => $capacity,
            'is_hidden' => false,
            'order' => 1,
        ]);
    }

    /**
     * Session-products must belong to the event's default category, otherwise the public
     * event endpoint (which buckets products under their category) silently drops them.
     */
    private function resolveDefaultCategoryId(int $eventId): int
    {
        return $this->productCategoryRepository
            ->findWhere([ProductCategoryDomainObjectAbstract::EVENT_ID => $eventId])
            ->sortBy(fn (ProductCategoryDomainObject $category) => $category->getOrder())
            ->first()
            ->getId();
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

    private function utcKey(?string $dateTime): string
    {
        return Carbon::parse((string) $dateTime, 'UTC')->format('Y-m-d H:i:s');
    }
}
