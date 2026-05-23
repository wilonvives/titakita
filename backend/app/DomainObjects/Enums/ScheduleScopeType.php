<?php

namespace TitaKita\DomainObjects\Enums;

enum ScheduleScopeType: string
{
    use BaseEnum;

    case SINGLE_DAY = 'single_day';
    case RECURRING_WEEKLY = 'recurring_weekly';
    case SPECIFIC_DATES = 'specific_dates';
}
