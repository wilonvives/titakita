<?php

namespace TitaKita\DomainObjects\Interfaces;

interface IsFilterable
{
    /**
     * @return array<string>
     */
    public static function getAllowedFilterFields(): array;
}
