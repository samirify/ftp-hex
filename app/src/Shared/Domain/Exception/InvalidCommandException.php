<?php
declare(strict_types=1);

namespace App\Shared\Domain\Exception;

use Exception;

class InvalidCommandException extends Exception
{
    protected $code = 400;
    protected $message = "Invalid Command!";
}