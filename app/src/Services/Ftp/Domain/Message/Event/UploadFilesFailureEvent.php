<?php

declare(strict_types=1);

namespace App\Services\Ftp\Domain\Message\Event;

use App\Shared\Domain\Exception\InvalidEventException;
use App\Shared\Domain\Message\Event\BaseEvent;

final class UploadFilesFailureEvent extends BaseEvent
{
    /**
     * @param string $data
     *
     * @throws InvalidEventException
     */
    public function __construct(string $data)
    {
        parent::__construct($data);
    }

    /**
     * @return bool
     *
     * @throws InvalidEventException
     */
    protected function isValid(): bool
    {
        if (empty($this->data)) {
            throw new InvalidEventException("Upload error message must be set!");
        }

        return true;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage(): ?string
    {
        return $this->data;
    }
}
