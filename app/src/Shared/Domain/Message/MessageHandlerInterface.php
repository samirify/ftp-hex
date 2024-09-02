<?php
declare(strict_types=1);

namespace App\Shared\Domain\Message;

interface MessageHandlerInterface
{
    /**
     * @param MessageInterface $message
     *
     * @return mixed
     */
    public function handle(MessageInterface $message): mixed;
}
