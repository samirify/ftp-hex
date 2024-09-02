<?php
declare(strict_types=1);

namespace App\Shared\Infrastructure\Adapters\Common\Message;

use App\Shared\Domain\Message\MessageBusInterface;
use App\Shared\Domain\Message\MessageHandlerInterface;
use App\Shared\Domain\Message\MessageInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus as MessengerMessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Transport\Sender\SendersLocatorInterface;

use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpSender;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\Connection;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;

abstract class MessageBus implements MessageBusInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @param MessageInterface $message
     * @param MessageHandlerInterface $handler
     *
     * @return mixed
     */
    protected function processSyncMsg(MessageInterface $message, MessageHandlerInterface $handler): mixed
    {
        $bus = new MessengerMessageBus([
            new HandleMessageMiddleware(new HandlersLocator([
                get_class($message) => [$handler],
            ])),
        ]);

        $envelope = $bus->dispatch($message);

        $handledStamp = $envelope->last(HandledStamp::class);

        return $handledStamp->getResult();
    }

    /**
     * @param MessageInterface $message
     * @param MessageHandlerInterface $handler
     *
     * @return void
     */
    protected function processAsyncMsg(MessageInterface $message, MessageHandlerInterface $handler): void
    {
        $messageClass = get_class($message);

        $sendersLocator = new class implements SendersLocatorInterface {
            public function getSenders(Envelope $envelope): iterable
            {
                $connection = Connection::fromDsn($_ENV['MESSENGER_TRANSPORT_DSN']);
                return [
                    'async' => new AmqpSender($connection)
                ];
            }
        };

        $bus = new MessengerMessageBus([
            new SendMessageMiddleware($sendersLocator),
            new HandleMessageMiddleware(new HandlersLocator([
                $messageClass => [$handler],
            ])),
        ]);

        $bus->dispatch($message);
    }

    /**
     * Check if Async Queue has been disabled in the configuration
     *
     * @return bool
     */
    protected function asyncDisabled(): bool
    {
        return isset($_ENV['ASYNC_QUEUE']) && strtolower($_ENV['ASYNC_QUEUE']) === 'disabled';
    }
}
