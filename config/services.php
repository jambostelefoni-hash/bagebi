<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'mailtrap' => [
        'api_token' => env('MAILTRAP_API_TOKEN'),
        'from_address' => env('MAILTRAP_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')),
        'from_name' => env('MAILTRAP_FROM_NAME', env('MAIL_FROM_NAME', 'Mailtrap Test')),
        'test_recipient' => env('MAILTRAP_TEST_RECIPIENT'),
    ],

    'smsoffice' => [
        'enabled' => env('SMSOFFICE_ENABLED', false),
        'api_key' => env('SMSOFFICE_API_KEY'),
        'sender' => env('SMSOFFICE_SENDER', 'Bagebi'),
        'urgent' => env('SMSOFFICE_URGENT', false),
        'test_recipient' => env('SMSOFFICE_TEST_RECIPIENT'),
    ],

];
