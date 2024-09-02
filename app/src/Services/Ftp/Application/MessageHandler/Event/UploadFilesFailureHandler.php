<?php

declare(strict_types=1);

namespace App\Services\Ftp\Application\MessageHandler\Event;

use App\Shared\Application\Ports\Secondary\Comms\MailerInterface;
use App\Shared\Domain\Message\MessageHandlerInterface;
use App\Shared\Domain\Message\MessageInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class UploadFilesFailureHandler implements MessageHandlerInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(
        protected ContainerInterface $container,
    ) {}

    /**
     * @param MessageInterface $message
     *
     * @return string|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(MessageInterface $message): ?string
    {
        /** @var MailerInterface $mailer */
        $mailer = $this->container->get(MailerInterface::class);
        $mailer->send([
            'to' => 'info@samirify.com',
            'subject' => 'Failed uploading files!',
            'body' => $this->getEmailBody([
                'error' => $message->getErrorMessage()
            ]),
            'body_text' => "Hi , " . PHP_EOL . "Failed uploading files"
        ]);

        return null;
    }

    /**
     * @param array $data
     *
     * @return string
     */
    private function getEmailBody(array $data = []): string
    {
        return <<<HTML
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="padding: 0 2.5em; text-align: center; padding-bottom: 3em;">
                        <div class="text">
                            <h3 class="name">Hello,</h3>
                            <h2 style="color: red">Your files have NOT been uploaded successfully!</h2>
                            <p style="color: red">Error: {$data['error']}</p>
                        </div>
                    </td>
                </tr>
            </table>
HTML;
    }
}
