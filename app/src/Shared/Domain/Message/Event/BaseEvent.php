<?php
declare(strict_types=1);

namespace App\Shared\Domain\Message\Event;

use App\Shared\Domain\Exception\InvalidEventException;
use App\Shared\Domain\Message\MessageInterface;

abstract class BaseEvent implements MessageInterface
{
    protected $data;

    /**
     * BaseCommand constructor.
     *
     * @param mixed $data
     *
     * @throws InvalidEventException
     */
    public function __construct(mixed $data = null)
    {
        $this->data = $data;

        if (!$this->isValid()) {
            throw new InvalidEventException();
        }
    }

    /**
     * @return bool
     *
     * @throws InvalidEventException
     */
    abstract protected function isValid(): bool;
}