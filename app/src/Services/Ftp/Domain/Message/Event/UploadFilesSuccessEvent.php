<?php

declare(strict_types=1);

namespace App\Services\Ftp\Domain\Message\Event;

use App\Shared\Domain\Message\Event\BaseEvent;

final class UploadFilesSuccessEvent extends BaseEvent
{
    /**
     * @return bool
     */
    protected function isValid(): bool
    {
        return true;
    }
}
