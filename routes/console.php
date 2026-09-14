<?php

use App\Services\MailtrapEmailService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Mailtrap\Helper\ResponseHelper;
use Symfony\Component\Console\Command\Command;

Artisan::command('send-mail {--to=}', function (MailtrapEmailService $mailtrap) {
    $recipient = $this->option('to') ?: config('services.mailtrap.test_recipient');
    if (!$recipient) {
        $this->error('Set MAILTRAP_TEST_RECIPIENT or pass --to=email@example.com.');
        return 1;
    }

    $response = $mailtrap->send(
        $recipient,
        'Mailtrap ინტეგრაციის ტესტი',
        'ბაგა-ბაღების პლატფორმიდან სატესტო წერილი წარმატებით გაიგზავნა.',
        null,
        'Integration Test'
    );

    $this->line(json_encode(ResponseHelper::toArray($response), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    return 0;
})->purpose('Send a Mailtrap integration test email');

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('mailtrap:test', function (MailtrapEmailService $mailtrap) {
    $recipient = config('services.mailtrap.test_recipient');

    if (! $recipient) {
        $this->error('MAILTRAP_TEST_RECIPIENT must be configured.');

        return Command::FAILURE;
    }

    $response = $mailtrap->send($recipient, 'Mailtrap ინტეგრაციის ტესტი', 'Mailtrap ინტეგრაცია წარმატებით მუშაობს.', null, 'Integration Test');
    $result = ResponseHelper::toArray($response);

    $this->info('სატესტო წერილი გაიგზავნა.');
    $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return Command::SUCCESS;
})->purpose('Send a transactional test email through Mailtrap');
