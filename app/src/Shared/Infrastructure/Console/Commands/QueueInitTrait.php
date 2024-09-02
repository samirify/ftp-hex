<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Console\Commands;


use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

trait QueueInitTrait
{
    /**
     * @return AMQPStreamConnection
     * @throws Exception
     */
    public function connection(): AMQPStreamConnection
    {
        return new AMQPStreamConnection($_ENV['RABBITMQ_HOST'], $_ENV['RABBITMQ_PORT'], $_ENV['RABBITMQ_USERNAME'], $_ENV['RABBITMQ_PASSWORD']);
    }

    /**
     * @param AMQPStreamConnection $connection
     *
     * @return AMQPChannel
     */
    public function channel(AMQPStreamConnection $connection): AMQPChannel
    {
        return $connection->channel();
    }

    /**
     * @return Serializer
     */
    public function serializer(): Serializer
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        return new Serializer($normalizers, $encoders);
    }
}