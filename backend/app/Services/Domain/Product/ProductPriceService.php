<?php

namespace TitaKita\Services\Domain\Product;

use TitaKita\DomainObjects\Enums\PromoCodeDiscountTypeEnum;
use TitaKita\DomainObjects\Enums\ProductPriceType;
use TitaKita\DomainObjects\PromoCodeDomainObject;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\Helper\Currency;
use TitaKita\Services\Domain\Product\DTO\OrderProductPriceDTO;
use TitaKita\Services\Domain\Product\DTO\PriceDTO;

class ProductPriceService
{
    public function getIndividualPrice(
        ProductDomainObject      $product,
        ProductPriceDomainObject $price,
        ?PromoCodeDomainObject   $promoCode
    ): float
    {
        return $this->getPrice($product, new OrderProductPriceDTO(
            quantity: 1,
            price_id: $price->getId(),
        ), $promoCode)->price;
    }

    public function getPrice(
        ProductDomainObject    $product,
        OrderProductPriceDTO   $productOrderDetail,
        ?PromoCodeDomainObject $promoCode
    ): PriceDTO
    {
        $price = $this->determineProductPrice($product, $productOrderDetail);

        if ($product->getType() === ProductPriceType::FREE->name) {
            return new PriceDTO(0.00);
        }

        if ($product->getType() === ProductPriceType::DONATION->name) {
            return new PriceDTO($price);
        }

        if (!$promoCode || !$promoCode->appliesToProduct($product)) {
            return new PriceDTO($price);
        }

        if ($promoCode->getDiscountType() === PromoCodeDiscountTypeEnum::NONE->name) {
            return new PriceDTO($price);
        }

        if ($promoCode->isFixedDiscount()) {
            $discountPrice = Currency::round($price - $promoCode->getDiscount());
        } elseif ($promoCode->isPercentageDiscount()) {
            $discountPrice = Currency::round(
                $price - ($price * ($promoCode->getDiscount() / 100))
            );
        } else {
            $discountPrice = $price;
        }

        return new PriceDTO(
            price: max(0, $discountPrice),
            price_before_discount: $price
        );
    }

    private function determineProductPrice(ProductDomainObject $product, OrderProductPriceDTO $productOrderDetails): float
    {
        return match ($product->getType()) {
            ProductPriceType::DONATION->name => max($product->getPrice(), $productOrderDetails->price),
            ProductPriceType::PAID->name => $product->getPrice(),
            ProductPriceType::FREE->name => 0.00,
            ProductPriceType::TIERED->name => $product->getPriceById($productOrderDetails->price_id)?->getPrice()
        };
    }
}
