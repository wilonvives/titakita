<?php

namespace TitaKita\DomainObjects;

use TitaKita\DomainObjects\Generated\WaitlistEntryDomainObjectAbstract;
use TitaKita\DomainObjects\Interfaces\IsFilterable;
use TitaKita\DomainObjects\Interfaces\IsSortable;
use TitaKita\DomainObjects\SortingAndFiltering\AllowedSorts;

class WaitlistEntryDomainObject extends WaitlistEntryDomainObjectAbstract implements IsSortable, IsFilterable
{
    public ?OrderDomainObject $order = null;
    public ?ProductPriceDomainObject $productPrice = null;

    public static function getDefaultSort(): string
    {
        return static::POSITION;
    }

    public static function getDefaultSortDirection(): string
    {
        return 'asc';
    }

    public static function getAllowedFilterFields(): array
    {
        return [
            self::STATUS,
            self::PRODUCT_PRICE_ID,
            self::EMAIL,
        ];
    }

    public static function getAllowedSorts(): AllowedSorts
    {
        return new AllowedSorts(
            [
                self::POSITION => [
                    'asc' => __('Position ascending'),
                    'desc' => __('Position descending'),
                ],
                self::CREATED_AT => [
                    'asc' => __('Oldest first'),
                    'desc' => __('Newest first'),
                ],
                self::STATUS => [
                    'asc' => __('Status A-Z'),
                    'desc' => __('Status Z-A'),
                ],
            ]
        );
    }

    public function setOrder(?OrderDomainObject $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function getOrder(): ?OrderDomainObject
    {
        return $this->order;
    }

    public function setProductPrice(?ProductPriceDomainObject $productPrice): self
    {
        $this->productPrice = $productPrice;
        return $this;
    }

    public function getProductPrice(): ?ProductPriceDomainObject
    {
        return $this->productPrice;
    }

}
