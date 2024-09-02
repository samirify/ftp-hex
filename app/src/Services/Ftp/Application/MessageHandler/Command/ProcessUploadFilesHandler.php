<?php
declare(strict_types=1);

namespace App\Services\Ftp\Application\MessageHandler\Command;

use App\Services\Ftp\Domain\DTO\FtpDTO;
use App\Services\Ftp\Domain\Message\Event\UploadFilesFailureEvent;
use App\Services\Ftp\Domain\Message\Event\UploadFilesSuccessEvent;
use App\Services\Ftp\Services\Protocols\Ftp;
use App\Shared\Application\Ports\Secondary\Message\Event\EventBusInterface;
use App\Shared\Domain\Message\MessageHandlerInterface;
use App\Shared\Domain\Message\MessageInterface;
use App\Shared\Domain\Services\LoggerInterface;
use Psr\Container\ContainerInterface;

final readonly class ProcessUploadFilesHandler implements MessageHandlerInterface {
    /**
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     * @param Ftp $ftpService
     */
    public function __construct(
        private ContainerInterface $container,
        private LoggerInterface    $logger,
        private Ftp            $ftpService
    )
    {
    }

    /**
     * @param MessageInterface $message
     *
     * @return array
     */
    public function handle(MessageInterface $message): array
    {
        try {
            /** @var FtpDTO $ftp */
            $ftp = $message->getFtp();
            $localPath = $message->getLocalPath();

            $remotePath = $ftp->remotePath;

            $result = $this->ftpService->uploadFiles($ftp, $localPath, $remotePath);

            /** @var EventBusInterface $eventBus */
            $eventBus = $this->container->get(EventBusInterface::class);
            $event = $result['success'] ? new UploadFilesSuccessEvent() : new UploadFilesFailureEvent($result['error'] ?? 'Unknown error!');
            $eventBus->dispatch($event);

            $msg = $result['success'] ? "Files uploaded successfully!" : ($result['error'] ?? 'Unknown error!');

            $this->logger->info($msg);

            return [
                'success' => true,
                'message' => $msg,
            ];
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            return [
                'success' => false,
                'code' => $e->getCode(),
                'error' => $e->getMessage(),
            ];
        }
    }
}