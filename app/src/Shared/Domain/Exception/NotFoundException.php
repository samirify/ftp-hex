<?php
declare(strict_types=1);

namespace App\Shared\Domain\Exception;

use Psr\Container\NotFoundExceptionInterface;
use Exception;

/**
 *  Class NotFoundException
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{
    protected $code = 404;
}