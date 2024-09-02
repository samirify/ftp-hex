<?php
declare(strict_types=1);

namespace App\Shared\Domain\Message\Command;

use App\Shared\Domain\Exception\InvalidCommandException;
use App\Shared\Domain\Message\MessageInterface;

abstract class BaseCommand implements MessageInterface
{
    protected $data;

    /**
     * BaseCommand constructor.
     *
     * @param mixed $data
     *
     * @throws InvalidCommandException
     */
    public function __construct(mixed $data = null)
    {
        $this->data = $data;

        if (!$this->isValid()) {
            throw new InvalidCommandException();
        }
    }

    /**
     * @return bool
     *
     * @throws InvalidCommandException
     */
    abstract protected function isValid(): bool;
}