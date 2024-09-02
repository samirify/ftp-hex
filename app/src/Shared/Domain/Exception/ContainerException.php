<?php
declare(strict_types=1);

namespace App\Shared\Domain\Exception;

use Psr\Container\ContainerExceptionInterface;
use Exception;

/**
 * Class ContainerException
 */
class ContainerException extends Exception implements ContainerExceptionInterface
{
}