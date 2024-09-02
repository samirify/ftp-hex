<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Console\Commands;

use PhpAmqpLib\Message\AMQPMessage;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;

class RabbitMQBrokerCommand extends Command
{
    use QueueInitTrait;

    /** @var array  */
    private array $appConfig = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(private readonly ContainerInterface $container)
    {
        $this->appConfig = include(BASEPATH . '../config/Config.php');
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('messenger:consume')
            ->setDescription('Consumes queue messages.')
            ->setHelp('This command consumes queue messages...')
            ->addOption('num-of-messages', 'm', InputOption::VALUE_OPTIONAL, )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $asyncQueue = $_ENV['ASYNC_QUEUE'] ?? 'enabled';

            if ($asyncQueue === 'disabled') {
                throw new \Exception("Asynchronous queue is currently disabled!");
            }

            $queueName = $_ENV['RABBITMQ_QUEUE_NAME'];

            // We could do something with this :)
            $numOfMessages = $input->getOption('num-of-messages');

            $connection = $this->connection();
            $channel = $this->channel($connection);

            $channel->queue_declare($queueName, false, true, false, false);

            $output->writeln(sprintf("<%s>%s</>", 'fg=black;bg=yellow','Waiting for messages. To exit press CTRL+C'));

            $appConfig = $this->appConfig;

            $callback = function (AMQPMessage $msg) use ($output, $appConfig) {
                $serializer = new PhpSerializer();

                $envelope = $serializer->decode([
                    'body' => $msg->getBody()
                ]);

                $classConfig = $appConfig[get_class($envelope->getMessage())] ?? [];

                if (!isset($classConfig['handlerClass'])) {
                    throw new \Exception("Handler must be set");
                }

                $handlerClass = $this->container->get($classConfig['handlerClass']);
                $handlerClass->handle($envelope->getMessage());

                $output->writeln(' <comment> Processing ' . get_class($handlerClass) . '</comment>');
                $output->writeln(' <info> - Done </info>');
                $msg->ack();
            };

            $channel->basic_qos(0, 1, false);
            $channel->basic_consume($queueName, '', false, false, false, false, $callback);

            try {
                $channel->consume();
            } catch (\Throwable $exception) {
                echo $exception->getMessage();
            }

            $channel->close();
            $connection->close();
        } catch (\Exception $e) {
            $output->writeln(' <error> ' . $e->getMessage() . ' </error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}