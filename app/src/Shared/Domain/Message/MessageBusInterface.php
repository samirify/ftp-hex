<?php
declare(strict_types=1);

namespace App\Shared\Domain\Message;

interface MessageBusInterface
{
    /**
     * @param MessageInterface $message
     *
     * @return mixed
     */
    public function dispatch(MessageInterface $message): mixed;
}
