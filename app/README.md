# Ftp Uploader - Hexagonal PHP

Ftp Uploader Hexagonal PHP application is an attempt to apply my understanding of Hexagonal architecture (Ports and Adapters) style in PHP. Lots of improvements can be done of course!

# Installation
First off, you need to verify if you have Docker installed by running this command in your console.

`> docker -v`

If not, refer to the Docker installation [here](https://docs.docker.com/get-docker/).

## Installation
Start by cloning the repo

```git clone https://samirify-tech@bitbucket.org/samirify/samirify-dt-lvm.git```


> **Note:** All `make` commands must be run from the root directory **not** from the docker directory!

#### On MAC OS
Run this command at the root `make install`

#### On Windows OS
Unfortunately you can not run the MakeFile on Windows so you have one of two options:

1. Install third party packages to enable the make command [See this article](https://earthly.dev/blog/makefiles-on-windows/), then run `make install`
2. Run the commands manually as described below (remember you need to be at the root)

Run the following commands on this order
```
> copy ./app/.env.example ./app/.env
> docker compose -p ftp-hex -f docker/docker-compose.yml up -d --build
> docker exec -it php-app composer install
```

## Docker commands
> Run the containers

`> docker compose -p ftp-hex -f docker/docker-compose.yml up -d`

For developers on MAC you could use:

`> make up`

> Stop and remove the containers

`> docker compose -p ftp-hex -f docker/docker-compose.yml down`

For developers on MAC you could use:

`> make down`

## Running the application (locally)

If you've reached here without any issues, and all your Docker containers are running fine, then you can now start the development and your local application will be available here: [http://localhost:8091](http://localhost:8091) (or the port you setfor `NGINX_PORT` in `.env` file if changed!)

This will instantiate the domain Container, but feel free to use any Container library you prefer as long as it complies with PSR-4 (i.e. [PHP-DI](https://php-di.org/)).

```
use App\Shared\Infrastructure\Container;
// use DI\Container;

$container = new Container();
```

You could create a new App instance, set the routes and run it as follows:

```
$app = (new App())
    ->route([
        'name' => 'some_name',
        'path' => '/route-uri',
        'controller' => ControllerClass::class,
        'method' => 'methodInController'
    ])
    ...
;

$app->run();
```

Here's an example which uploads a file:
```
// file: UploadFilesController.php
...
```
An event will then be triggered upon success to send an email to the user
```
/** @var EventBusInterface $eventBus */
$eventBus = $this->container->get(EventBusInterface::class);
$event = new UploadFilesSuccessEvent($user->getEmail());
$eventBus->dispatch($event);
```

You can check the application's mailbox here: [Mailhog port 8092](http://localhost:8092/) (or the port you setfor `MAILHOG_CLIENT_PORT` in `.env` file if changed!)

### Running RabbitMQ
To consume messages saved in the queue, you can run this command at the root

`> make worker`

### Run the tests

#### Unit tests - PHPUnit (TDD)
MAC OS

`> make unit-test`

Or navigate to the `app` folder,  or `> cd app`, and run

`app> vendor/bin/phpunit`

#### Behavior-Driven tests - Behat (BDD)
MAC OS

`> make behat-test`

Or navigate to the `app` folder,  or `> cd app`, and run

`app> vendor/bin/behat`

#### Useful commands

##### RabbitMQ
To reset your local RabbitMQ queue

`> make rabbitmq`

Then run

`rabbitmqadmin delete queue name='samirify_message_queue'`

Where `samirify_message_queue` is the name of your queue in `.env`

##### RabbitMQ
To start PHP shell

`> make shell`

