<?php

namespace TitaKita\Services\Domain\Message\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;
use TitaKita\DomainObjects\Enums\MessagingTierViolationEnum;

class MessagingTierViolationDTO extends BaseDataObject
{
    /**
     * @param int $accountId
     * @param string $tierName
     * @param MessagingTierViolationEnum[] $violations
     */
    public function __construct(
        public readonly int $accountId,
        public readonly string $tierName,
        public readonly array $violations,
    ) {
    }

    public function getFirstViolationMessage(): string
    {
        return $this->violations[0]->getMessage();
    }
}
