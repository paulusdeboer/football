<?php

namespace App\Services;

use RuntimeException;

class WhapiException extends RuntimeException
{
    public function __construct(public readonly string $reason, public readonly bool $uncertain = false)
    {
        // Never include provider payloads, tokens or underlying HTTP exceptions.
        parent::__construct($reason);
    }
}
