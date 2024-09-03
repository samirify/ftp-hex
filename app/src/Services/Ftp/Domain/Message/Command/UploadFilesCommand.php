<?php

declare(strict_types=1);

namespace App\Services\Ftp\Domain\Message\Command;

use App\Services\Ftp\Domain\DTO\FtpDTO;
use App\Services\Ftp\Domain\Exception\InvalidFtpParameterException;
use App\Services\Ftp\Domain\Exception\MissingFtpConfigException;
use App\Services\Ftp\Services\Protocols\Ftp;
use App\Shared\Domain\Exception\InvalidCommandException;
use App\Shared\Domain\Message\Command\BaseCommand;

final class UploadFilesCommand extends BaseCommand
{
    /**
     * UploadFilesCommand constructor.
     *
     * @param array $data
     *
     * @throws InvalidCommandException
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    /**
     * @return bool
     *
     * @throws InvalidCommandException
     */
    protected function isValid(): bool
    {
        if (empty($this->data['files_path']) && empty($this->data['files'])) {
            throw new InvalidCommandException("No file to upload!");
        }

        if (!empty($this->data['files_path']) && !empty($this->data['files'])) {
            throw new InvalidCommandException("No file to upload!");
        }

        return true;
    }

    /**
     * @return FtpDTO
     * @throws InvalidFtpParameterException
     * @throws MissingFtpConfigException
     */
    public function getFtp(): FtpDTO
    {
        if (empty($this->data['ftp'])) {
            Ftp::checkFtpDefaultConfig();

            return new FtpDTO(
                host: $_ENV['FTP_HOST'],
                username: $_ENV['FTP_USERNAME'],
                password: $_ENV['FTP_PASSWORD'],
                remotePath: $_ENV['FTP_PATH'],
                protocol: $_ENV['FTP_PROTOCOL'],
                port: (int)$_ENV['FTP_PORT'],
            );
        }

        $this->data['ftp']['remotePath'] = $this->data['ftp']['remotePath'] ?? $_ENV['FTP_PATH'] ?? '/';

        return new FtpDTO(...$this->data['ftp']);
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return $this->data['files'];
    }

    /**
     * @return string|null
     */
    public function getFilesPath(): ?string
    {
        return $this->data['files_path'];
    }
}
