<?php
declare(strict_types=1);

namespace App\Shared\Infrastructure\Adapters\Common\Message;

use App\Shared\Domain\Exception\UnsetMessageHandlerException;
use App\Shared\Domain\Message\MessageHandlerInterface;
use App\Shared\Domain\Message\MessageInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class MessageHandler implements MessageHandlerInterface
{
    /** @var array  */
    private array $appConfig = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(
        protected readonly ContainerInterface $container,
    )
    {
        $this->appConfig = include(BASEPATH . '../config/Config.php');
    }

    /**
     * __invoke method is needed to implement Symfony Messenger, however we could've
     * still used the "handle" method directly for any other implementation!
     *
     * @param MessageInterface $message
     *
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws UnsetMessageHandlerException
     */
    public function __invoke(MessageInterface $message): mixed
    {
        return $this->handle($message);
    }

    /**
     * @param MessageInterface $message
     *
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws UnsetMessageHandlerException
     */
    public function handle(MessageInterface $message): mixed
    {
        $classConfig = $this->appConfig[get_class($message)] ?? [];

        if (!isset($classConfig['handlerClass'])) {
            throw new UnsetMessageHandlerException("Handler must be set");
        }

        $handlerClass = $this->container->get($classConfig['handlerClass']);

        return $handlerClass->handle($message);
    }
}
