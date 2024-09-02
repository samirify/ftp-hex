<?php
declare(strict_types=1);

namespace App\Services\Ftp\Domain\Message\Command;

use App\Services\Ftp\Domain\DTO\FtpDTO;
use App\Shared\Domain\Exception\InvalidCommandException;
use App\Shared\Domain\Message\Command\BaseCommand;

final class ProcessUploadFilesCommand extends BaseCommand
{

    /**
     * ProcessUploadFilesCommand constructor.
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
     * @throws InvalidCommandException
     */
    protected function isValid(): bool
    {
        if (!($this->data['ftp'] instanceof FtpDTO)) {
            throw new InvalidCommandException("Ftp must be of type FtpDTO");
        }

        if (empty($this->data['local_path'])) {
            throw new InvalidCommandException("Cannot find path");
        }

        return true;
    }

    /**
     * @return FtpDTO
     */
    public function getFtp(): FtpDTO
    {
        return $this->data['ftp'];
    }

    /**
     * @return string|null
     */
    public function getLocalPath(): ?string
    {
        return $this->data['local_path'];
    }
}