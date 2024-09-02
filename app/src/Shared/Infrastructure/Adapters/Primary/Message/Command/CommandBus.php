<?php
declare(strict_types=1);

namespace App\Shared\Infrastructure\Adapters\Primary\Message\Command;

use App\Shared\Application\Ports\Primary\Message\Command\CommandBusInterface;
use App\Shared\Domain\Message\MessageInterface;
use App\Shared\Infrastructure\Adapters\Common\Message\MessageBus;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;


class CommandBus extends MessageBus implements CommandBusInterface
{
    /** @var array|mixed  */
    private array $appConfig = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
        $this->appConfig = include(BASEPATH . '../config/Config.php');
        parent::__construct($container);
    }

    /**
     * @param MessageInterface $message
     *
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function dispatch(MessageInterface $message): mixed
    {
        $messageClass = get_class($message);
        $messageConfig = $this->appConfig[$messageClass] ?? [];

        $handler = $this->container->get(CommandHandler::class);

        if (!$this->asyncDisabled() && isset($messageConfig['async']) && $messageConfig['async'] === true) {
            $this->processAsyncMsg($message, $handler);
            return null;
        } else {
            return $this->processSyncMsg($message, $handler);
        }
    }
}
