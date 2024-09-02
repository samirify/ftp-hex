<?php

declare(strict_types = 1);

namespace App;

use App\Services\Ftp\Application\MessageHandler\Command\ProcessUploadFilesHandler;
use App\Services\Ftp\Application\MessageHandler\Command\UploadFilesHandler;
use App\Services\Ftp\Application\MessageHandler\Event\UploadFilesFailureHandler;
use App\Services\Ftp\Application\MessageHandler\Event\UploadFilesSuccessHandler;
use App\Services\Ftp\Services\Protocols\Ftp;
use App\Shared\Application\Ports\Primary\Message\Command\CommandBusInterface;
use App\Shared\Application\Ports\Secondary\Comms\MailerInterface;
use App\Shared\Application\Ports\Secondary\Message\Event\EventBusInterface;
use App\Shared\Domain\Services\Logger;
use App\Shared\Domain\Services\LoggerInterface;
use App\Shared\Infrastructure\Adapters\Primary\Message\Command\CommandBus;
use App\Shared\Infrastructure\Adapters\Primary\Message\Command\CommandHandler;
use App\Shared\Infrastructure\Adapters\Secondary\Comms\MailerAdapter;
use App\Shared\Infrastructure\Adapters\Secondary\Message\Event\EventBus;
use App\Shared\Infrastructure\Adapters\Secondary\Message\Event\EventHandler;
use DI\Container;
use Dotenv\Dotenv;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

// You can switch Container implementations as long as they conform to PSR-11 ContainerInterface
// More info here: https://www.php-fig.org/psr/psr-11/
// I chose the popular php-di package here, as an example

// https://php-di.org/
//use App\Shared\Domain\Services\Container;

class App
{
    /** @var ContainerInterface $container */
    public ContainerInterface $container;

    /** @var RouteCollection $routes */
    protected RouteCollection $routes;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $this->routes = new RouteCollection();


        $this->container = new Container();

        define( 'BASEPATH', dirname( __FILE__ ) . '/' );
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param bool $registerRoutes
     *
     * @return void
     */
    public function run(bool $registerRoutes = true): void
    {
        $this->container->set(LoggerInterface::class, function(ContainerInterface $c) {
            return new Logger();
        });

        $this->container->set(MailerInterface::class, function(ContainerInterface $c) {
            return new MailerAdapter();
        });

        $this->container->set(CommandBusInterface::class, function(ContainerInterface $c) {
            return new CommandBus($c);
        });
        $this->container->set(EventBusInterface::class, function(ContainerInterface $c) {
            return new EventBus($c);
        });

        $this->container->set(CommandHandler::class, function(ContainerInterface $c) {
            return new CommandHandler($c);
        });

        $this->container->set(EventHandler::class, function(ContainerInterface $c) {
            return new EventHandler($c);
        });

        $this->container->set(UploadFilesHandler::class, function(ContainerInterface $c) {
            return new UploadFilesHandler($c, $c->get(LoggerInterface::class));
        });

        $this->container->set(ProcessUploadFilesHandler::class, function(ContainerInterface $c) {
            return new ProcessUploadFilesHandler(
                $c,
                $c->get(LoggerInterface::class),
                $c->get(Ftp::class)
            );
        });

        $this->container->set(UploadFilesSuccessHandler::class, function(ContainerInterface $c) {
            return new UploadFilesSuccessHandler($c);
        });

        $this->container->set(UploadFilesFailureHandler::class, function(ContainerInterface $c) {
            return new UploadFilesFailureHandler($c);
        });

        if ($registerRoutes)
        {
            $request = Request::createFromGlobals();

            $response = $this->handle($request);
            $response->send();
        }
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request): Response
    {
        $context = new RequestContext();
        $context->fromRequest($request);

        $matcher = new UrlMatcher($this->routes, $context);

        try {
            $routeParams = $matcher->match($request->getPathInfo());
            $controller = new $routeParams['controller']($this->container);
            $response = $controller->{$routeParams['method']}($request);
        } catch (ResourceNotFoundException $e) {
            $response = new JsonResponse([
                'success' => false,
                'errors' => [
                    $e->getMessage()
                ]
            ], Response::HTTP_NOT_FOUND);
        } catch (InvalidArgumentException $e) {
            $response = new JsonResponse([
                'success' => false,
                'errors' => [
                    $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }

    /**
     * @param array $params
     *
     * @return $this
     */
    public function route(array $params): App
    {
        $this->routes->add($params['name'], new Route(
            $params['path'],
            array('controller' => $params['controller'], 'method' => $params['method'])
        ));

        return $this;
    }
}