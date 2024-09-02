<?php

declare(strict_types=1);

namespace App\Services\Ftp\Domain\DTO;

use App\Services\Ftp\Domain\Exception\InvalidFtpParameterException;
use App\Services\Ftp\Services\Constants;

final readonly class FtpDTO
{
    /**
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $remotePath
     * @param string|null $protocol
     * @param int|null $port
     *
     * @throws InvalidFtpParameterException
     */
    public function __construct(
        public string  $host,
        public string  $username,
        public string  $password,
        public string  $remotePath,
        public ?string  $protocol = 'ftp',
        public ?int     $port = 21,
    ) {
        $supportedProtocolsText = implode(',', Constants::SUPPORTED_PROTOCOLS);

        if (!in_array(strtolower($this->protocol), Constants::SUPPORTED_PROTOCOLS)) {
            throw new InvalidFtpParameterException("Protocol '{$this->protocol}' is not supported! We support only ($supportedProtocolsText)");
        }

        if (empty($host)) {
            throw new InvalidFtpParameterException('Ftp host cannot be blank!');
        }

        if (empty($username)) {
            throw new InvalidFtpParameterException('Ftp username cannot be blank!');
        }

        if (empty($password)) {
            throw new InvalidFtpParameterException('Ftp password cannot be blank!');
        }
    }
}
