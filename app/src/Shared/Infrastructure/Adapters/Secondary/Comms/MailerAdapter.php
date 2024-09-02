<?php
declare(strict_types=1);

namespace App\Shared\Infrastructure\Adapters\Secondary\Comms;

use App\Shared\Application\Ports\Secondary\Comms\MailerInterface;
use App\Shared\Domain\Exception\NotFoundException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

class MailerAdapter implements MailerInterface {
    /**
     * @param array $data
     *
     * @return void
     * @throws TransportExceptionInterface|NotFoundException
     */
    public function send(array $data): void
    {
        $transport = Transport::fromDsn($_ENV['MAILER_DSN'] ?? 'smtp://localhost');
        $mailer = new Mailer($transport);

        $email = (new Email())
            ->from('tech@samirify.com')
            ->to($data['to'] ?? 'tech@samirify.com')
            //->cc('cc@samirify.com')
            //->bcc('bcc@samirify.com')
            //->replyTo('tech@samirify.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($data['subject'])
            ->addPart((new DataPart(fopen(BASEPATH . $_ENV['EMAIL_TEMPLATES_DIRECTORY'] . 'images/logo.svg', 'r'), 'logo', 'image/svg+xml'))->asInline())
            ->text($data['body_text'] ?? '')
            ->html($this->inTemplate(
                '<p style="font-family: Helvetica, sans-serif">' . ($data['body'] ?? '') . '</p>'
            ));

        $mailer->send($email);

//        No external libraries needed
//        **********************************
//        $to      = 'tech@samirify.com';
//        $subject = $data['subject'];
//        $body = $data['body'] ?? '';
//
//        $headers  = 'MIME-Version: 1.0' . "\r\n";
//        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
//        $headers .= 'To: Samir <tech@samirify.com>, John <tech@samirify.com>' . "\r\n";
//        $headers .= 'From: Samirify Team <tech@samirify.com>' . "\r\n";
//
//        mail($to, $subject, $body, $headers);
    }

    /**
     * @param string $content
     *
     * @return string
     * @throws NotFoundException
     */
    private function inTemplate(string $content): string
    {
        $templatesDir = $_ENV['EMAIL_TEMPLATES_DIRECTORY'] ?? null;

        if (empty($templatesDir)) {
            throw new NotFoundException("Templates directory found!");
        }

        $templateHtml = file_get_contents(BASEPATH . $_ENV['EMAIL_TEMPLATES_DIRECTORY'] . 'email-layout.html');

        return str_replace('{{emailContent}}', $content, $templateHtml);
    }
}