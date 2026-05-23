<?php

namespace TitaKita\DomainObjects\Enums;

enum QuestionBelongsTo
{
    use BaseEnum;

    case PRODUCT;
    case ORDER;
}
