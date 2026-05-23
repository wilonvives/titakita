<?php

namespace TitaKita\Exceptions;

use Exception;

class EmailTemplateValidationException extends Exception
{
    public array $validationErrors = [];
}