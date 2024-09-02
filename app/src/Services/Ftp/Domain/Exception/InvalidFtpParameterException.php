<?php
declare(strict_types=1);

namespace App\Services\Ftp\Domain\Exception;

use Exception;

class InvalidFtpParameterException extends Exception
{
    protected $code = 400;
    protected $message = "Invalid Ftp parameter!";
}