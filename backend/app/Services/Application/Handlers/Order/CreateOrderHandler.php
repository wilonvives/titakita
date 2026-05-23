<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Order;

use TitaKita\DomainObjects\AffiliateDomainObject;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\EventSettingDomainObject;
use TitaKita\DomainObjects\Generated\AffiliateDomainObjectAbstract;
use TitaKita\DomainObjects\Generated\PromoCodeDomainObjectAbstract;
use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\DomainObjects\PromoCodeDomainObject;
use TitaKita\DomainObjects\Status\AffiliateStatus;
use TitaKita\DomainObjects\Status\EventStatus;
use TitaKita\Repository\Interfaces\AffiliateRepositoryInterface;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\PromoCodeRepositoryInterface;
use TitaKita\Services\Application\Handlers\Order\DTO\CreateOrderPublicDTO;
use TitaKita\Services\Domain\Order\OrderItemProcessingService;
use TitaKita\Services\Domain\Order\OrderManagementService;
use TitaKita\Services\Domain\Product\AvailableProductQuantitiesFetchService;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateOrderHandler
{
    public function __construct(
        private readonly EventRepositoryInterface               $eventRepository,
        private readonly PromoCodeRepositoryInterface           $promoCodeRepository,
        private readonly AffiliateRepositoryInterface           $affiliateRepository,
        private readonly OrderManagementService                 $orderManagementService,
        private readonly OrderItemProcessingService             $orderItemProcessingService,
        private readonly AvailableProductQuantitiesFetchService $availableProductQuantitiesFetchService,
        private readonly DatabaseManager                        $databaseManager,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(
        int                  $eventId,
        CreateOrderPublicDTO $createOrderPublicDTO,
        bool                 $deleteExistingOrdersForSession = true
    ): OrderDomainObject
    {
        return $this->databaseManager->transaction(function () use ($eventId, $createOrderPublicDTO, $deleteExistingOrdersForSession) {
            $this->databaseManager->statement('SELECT pg_advisory_xact_lock(?)', [$eventId]);

            $event = $this->eventRepository
                ->loadRelation(EventSettingDomainObject::class)
                ->findById($eventId);

            $this->validateEventStatus($event, $createOrderPublicDTO);

            $promoCode = $this->getPromoCode($createOrderPublicDTO, $eventId);
            $affiliate = $this->getAffiliate($createOrderPublicDTO, $eventId);

            if ($deleteExistingOrdersForSession) {
                $this->orderManagementService->deleteExistingOrders($eventId, $createOrderPublicDTO->session_identifier);
            }

            $this->validateProductAvailability($eventId, $createOrderPublicDTO);

            $order = $this->orderManagementService->createNewOrder(
                eventId: $eventId,
                event: $event,
                timeOutMinutes: $event->getEventSettings()?->getOrderTimeoutInMinutes(),
                locale: $createOrderPublicDTO->order_locale,
                promoCode: $promoCode,
                affiliate: $affiliate,
                sessionId: $createOrderPublicDTO->session_identifier,
            );

            $orderItems = $this->orderItemProcessingService->process(
                order: $order,
                productsOrderDetails: $createOrderPublicDTO->products,
                event: $event,
                promoCode: $promoCode,
            );

            return $this->orderManagementService->updateOrderTotals($order, $orderItems);
        });
    }

    private function getPromoCode(CreateOrderPublicDTO $createOrderPublicDTO, int $eventId): ?PromoCodeDomainObject
    {
        if ($createOrderPublicDTO->promo_code === null) {
            return null;
        }

        $promoCode = $this->promoCodeRepository->findFirstWhere([
            PromoCodeDomainObjectAbstract::CODE => strtolower(trim($createOrderPublicDTO->promo_code)),
            PromoCodeDomainObjectAbstract::EVENT_ID => $eventId,
        ]);

        if ($promoCode?->isValid()) {
            return $promoCode;
        }

        return null;
    }

    private function getAffiliate(CreateOrderPublicDTO $createOrderPublicDTO, int $eventId): ?AffiliateDomainObject
    {
        if ($createOrderPublicDTO->affiliate_code === null) {
            return null;
        }

        return $this->affiliateRepository->findFirstWhere([
            AffiliateDomainObjectAbstract::CODE => strtoupper(trim($createOrderPublicDTO->affiliate_code)),
            AffiliateDomainObjectAbstract::EVENT_ID => $eventId,
            AffiliateDomainObjectAbstract::STATUS => AffiliateStatus::ACTIVE->value,
        ]);
    }

    public function validateEventStatus(EventDomainObject $event, CreateOrderPublicDTO $createOrderPublicDTO): void
    {
        if (!$createOrderPublicDTO->is_user_authenticated && $event->getStatus() !== EventStatus::LIVE->name) {
            throw new UnauthorizedException(
                __('This event is not live.')
            );
        }
    }

    /**
     * @throws ValidationException
     */
    private function validateProductAvailability(int $eventId, CreateOrderPublicDTO $createOrderPublicDTO): void
    {
        $availability = $this->availableProductQuantitiesFetchService
            ->getAvailableProductQuantities($eventId, ignoreCache: true);

        foreach ($createOrderPublicDTO->products as $product) {
            foreach ($product->quantities as $priceQuantity) {
                if ($priceQuantity->quantity <= 0) {
                    continue;
                }

                $available = $availability->productQuantities
                    ->where('product_id', $product->product_id)
                    ->where('price_id', $priceQuantity->price_id)
                    ->first()?->quantity_available ?? 0;

                if ($priceQuantity->quantity > $available) {
                    throw ValidationException::withMessages([
                        'products' => __('Not enough products available. Please try again.'),
                    ]);
                }
            }
        }
    }
}
