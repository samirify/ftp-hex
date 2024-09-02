<?php

declare(strict_types=1);

use App\Services\Ftp\Application\MessageHandler\Command\ProcessUploadFilesHandler;
use App\Services\Ftp\Application\MessageHandler\Command\UploadFilesHandler;
use App\Services\Ftp\Application\MessageHandler\Event\UploadFilesFailureHandler;
use App\Services\Ftp\Application\MessageHandler\Event\UploadFilesSuccessHandler;
use App\Services\Ftp\Domain\Message\Command\ProcessUploadFilesCommand;
use App\Services\Ftp\Domain\Message\Command\UploadFilesCommand;
use App\Services\Ftp\Domain\Message\Event\UploadFilesFailureEvent;
use App\Services\Ftp\Domain\Message\Event\UploadFilesSuccessEvent;

return [
    UploadFilesCommand::class => [
        'async' => false,
        'handlerClass' => UploadFilesHandler::class
    ],
    ProcessUploadFilesCommand::class => [
        'async' => true,
        'handlerClass' => ProcessUploadFilesHandler::class
    ],
    UploadFilesSuccessEvent::class => [
        'async' => true,
        'handlerClass' => UploadFilesSuccessHandler::class
    ],
    UploadFilesFailureEvent::class => [
        'async' => true,
        'handlerClass' => UploadFilesFailureHandler::class
    ],
];
