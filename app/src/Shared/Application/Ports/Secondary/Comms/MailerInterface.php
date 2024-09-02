<?php
declare(strict_types=1);

namespace App\Shared\Application\Ports\Secondary\Comms;

interface MailerInterface {
    /**
     * @param array $data
     *
     * @return void
     */
    public function send(array $data): void;
}