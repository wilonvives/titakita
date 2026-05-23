<?php

namespace TitaKita\DomainObjects\Enums;

enum OrganizerHomepageVisibility: string
{
    use BaseEnum;

    case PUBLIC = 'PUBLIC';
    case PASSWORD_PROTECTED = 'PASSWORD_PROTECTED';
}
