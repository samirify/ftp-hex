<?php
declare(strict_types=1);

namespace App\Shared\Domain\Services;

class Logger implements LoggerInterface
{
    private string $logPath;
    private string $errorFileName = 'error.log';
    private string $infoFileName = 'info.log';

    public function __construct()
    {
        $this->logPath = BASEPATH . '../logs';

        if (!file_exists($this->logPath)) {
            mkdir($this->logPath, 0777, true);
        }
    }

    public function emergency(\Stringable|string $message, array $context = []): void
    {
        // TODO: Implement emergency() method.
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        // TODO: Implement alert() method.
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        // TODO: Implement critical() method.
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        file_put_contents($this->logPath . '/' . $this->errorFileName, 'Error: ' . date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        // TODO: Implement warning() method.
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        // TODO: Implement notice() method.
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        file_put_contents($this->logPath . '/' . $this->infoFileName, 'Error: ' . date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        // TODO: Implement debug() method.
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        // TODO: Implement log() method.
    }
}