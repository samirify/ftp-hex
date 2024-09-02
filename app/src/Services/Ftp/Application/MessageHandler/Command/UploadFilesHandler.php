<?php

declare(strict_types=1);

namespace App\Services\Ftp\Application\MessageHandler\Command;

use App\Services\Ftp\Domain\DTO\FtpDTO;
use App\Services\Ftp\Domain\Message\Command\ProcessUploadFilesCommand;
use App\Services\Ftp\Services\Protocols\Ftp;
use App\Shared\Application\Ports\Primary\Message\Command\CommandBusInterface;
use App\Shared\Domain\Message\MessageHandlerInterface;
use App\Shared\Domain\Message\MessageInterface;
use App\Shared\Domain\Services\LoggerInterface;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final readonly class UploadFilesHandler implements MessageHandlerInterface
{
    private readonly Ftp $ftpService;
    /**
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     */
    public function __construct(
        private ContainerInterface $container,
        private LoggerInterface    $logger,
    ) {
        $this->ftpService = new Ftp();
    }

    /**
     * @param MessageInterface $message
     *
     * @return array
     * @throws ContainerExceptionInterface
     */
    public function handle(MessageInterface $message): array
    {
        try {
            /** @var FtpDTO $ftp */
            $ftp = $message->getFtp();
            
            $source = $this->ftpService->determineFilePathOrFilesSource($message->getFiles() ?? []);

            $files = $source['files'];
            $filesPath = $message->getFilesPath() ?? $source['filesPath'];

            $hash = md5(time() . json_encode($ftp) . json_encode($files));

            $location = '/var/www/html/upload/files/' . $hash . '/';

            $this->ftpService->createFilesLocation($location);

            if (!$filesPath) {
                $this->ftpService->moveFilesToUploadLocation($files, $location);
            } else {
                $filesPath = '/var/www/html' . $filesPath;
                if (!is_dir($filesPath)) {
                    $this->ftpService->cleanUploadFolder($location);
                    throw new Exception("Folder {$filesPath} does not exist!");
                }

                if ($this->dirIsEmpty($filesPath)) {
                    $this->ftpService->cleanUploadFolder($location);
                    throw new Exception('No files in folder!');
                } else {
                    $this->ftpService->moveFolderToUploadLocation($filesPath, $location);
                }
            }

            /** @var CommandBusInterface $commandBus */
            $commandBus = $this->container->get(CommandBusInterface::class);
            $processUploadFilesCommand = new ProcessUploadFilesCommand([
                'ftp' => $ftp,
                'local_path' => $location
            ]);
            $commandBus->dispatch($processUploadFilesCommand);

            return [
                'success' => true,
                'message' => 'Files are being uploaded!',
            ];
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return [
                'success' => false,
                'code' => $e->getCode(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Taken from here: https://stackoverflow.com/a/7497848/771174
     *
     * @param string $dir
     *
     * @return bool
     */
    private function dirIsEmpty(string $dir): bool
    {
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                closedir($handle);
                return false;
            }
        }
        closedir($handle);
        return true;
    }
}
