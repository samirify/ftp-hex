<?php

declare(strict_types=1);

namespace App\Services\Ftp\Api\Controller;

use App\Services\Ftp\Domain\Message\Command\UploadFilesCommand;
use App\Shared\Application\Ports\Primary\Message\Command\CommandBusInterface;
use App\Shared\Infrastructure\Api\Controller\BaseController;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

class FtpController extends BaseController
{
    public function init(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => [
                'ftp' => [
                    'protocol' => $_ENV['FTP_PROTOCOL'] ?? 'ftp',
                    'host' => $_ENV['FTP_HOST'] ?? '',
                    'port' => (int)$_ENV['FTP_PORT'] ?? 21,
                    'username' => $_ENV['FTP_USERNAME'] ?? '',
                    'password' => $_ENV['FTP_PASSWORD'] ?? '',
                    'remotePath' => $_ENV['FTP_PATH'] ?? '',
                ]
            ]
        ]);
    }

    /**
     * Upload files endpoint
     * 
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function uploadFiles(Request $request): JsonResponse
    {
        $params = $this->getRequestParams($request);

        try {
            $ftp = $params['ftp'] ?? null;

            if (!is_null($ftp)) {
                // Casting port to int if set as Javascript FormData sends only string type!
                $ftp['port'] = (int)$ftp['port'];
            }

            $commandBus = $this->container->get(CommandBusInterface::class);
            $command = new UploadFilesCommand([
                'ftp' => $ftp,
                'files_path' => $params['files_path'] ?? null,
                'files' => $_FILES
            ]);

            $result = $commandBus->dispatch($command);

            if (!$result['success']) {
                throw new Exception($result['error'], $result['code']);
            }

            return new JsonResponse($result);
        } catch (Throwable $th) {
            return new JsonResponse([
                'success' => false,
                'errors' => [$th->getMessage()]
            ], $th->getCode() ?: 500);
        }
    }
}
