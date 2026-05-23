<?php

namespace TitaKita\DomainObjects\Status;

enum OutgoingMessageStatus
{
    case SENT;
    case FAILED;
}
