<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SmsOfficeService
{
    public function send(string $mobile, string $message, string $reference): array
    {
        $key = (string) config('services.smsoffice.api_key');
        $sender = (string) config('services.smsoffice.sender');

        if ($key === '' || $sender === '') {
            throw new RuntimeException('SMSOFFICE_API_KEY and SMSOFFICE_SENDER must be configured.');
        }

        $response = Http::asForm()->timeout(10)->post('https://smsoffice.ge/api/v2/send/', [
            'key' => $key,
            'destination' => $this->normalizeMobile($mobile),
            'sender' => $sender,
            'content' => mb_substr($message, 0, 1000),
            'reference' => mb_substr($reference, 0, 20),
            'urgent' => config('services.smsoffice.urgent') ? 'true' : 'false',
        ]);

        $response->throw();
        $payload = $response->json();
        if (!is_array($payload) || empty($payload['Success'])) {
            throw new RuntimeException((string) data_get($payload, 'Message', 'SMS Office rejected the request.'));
        }

        return $payload;
    }

    public function status(string $mobile, string $reference): array
    {
        $key = (string) config('services.smsoffice.api_key');
        $reference = mb_substr(trim($reference), 0, 20);

        if ($key === '' || $reference === '') {
            throw new RuntimeException('SMS Office API გასაღები და reference სავალდებულოა.');
        }

        $response = Http::timeout(10)->get('https://smsoffice.ge/api/v2/getMessageStatus/', [
            'key' => $key,
            'destination' => $this->normalizeMobile($mobile),
            'reference' => $reference,
        ]);

        $response->throw();
        $payload = $response->json();
        if (!is_array($payload) || empty($payload['Success'])) {
            throw new RuntimeException((string) data_get($payload, 'Message', 'SMS Office სტატუსის მიღება ვერ მოხერხდა.'));
        }

        return $payload;
    }

    public function normalizeMobile(string $mobile): string
    {
        $digits = preg_replace('/\D+/', '', $mobile);
        if (str_starts_with($digits, '00')) $digits = substr($digits, 2);
        if (str_starts_with($digits, '0')) $digits = substr($digits, 1);
        if (str_starts_with($digits, '995') && strlen($digits) === 12 && substr($digits, 3, 1) === '5') return $digits;
        if (strlen($digits) === 9 && str_starts_with($digits, '5')) return '995'.$digits;

        throw new RuntimeException('მობილური ნომერი არ არის სწორ ქართულ ფორმატში. მიუთითეთ 9-ნიშნა ნომერი, რომელიც იწყება 5-ით.');
    }
}
