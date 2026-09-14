<?php

namespace App\Services;

use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Mime\Address;

class MailtrapEmailService
{
    public function send(string $to, string $subject, string $message, ?string $actionUrl = null, string $category = 'Platform notification'): ResponseInterface
    {
        $token = (string) config('services.mailtrap.api_token');
        $fromAddress = (string) config('services.mailtrap.from_address');
        $fromName = (string) config('services.mailtrap.from_name');

        if ($token === '' || $fromAddress === '') {
            throw new \RuntimeException('MAILTRAP_API_TOKEN and MAILTRAP_FROM_ADDRESS must be configured.');
        }

        $text = $message.($actionUrl ? "\n\nგაგრძელება: {$actionUrl}" : '');
        $email = (new MailtrapEmail())
            ->from(new Address($fromAddress, $fromName))
            ->to(new Address($to))
            ->subject($subject)
            ->category($category)
            ->text($text);

        return MailtrapClient::initSendingEmails(apiKey: $token)->send($email);
    }
}
