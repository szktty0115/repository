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

    'x' => [
        'api_key' => env('X_API_KEY'),
        'api_secret' => env('X_API_SECRET'),
        'access_token' => env('X_ACCESS_TOKEN'),
        'access_token_secret' => env('X_ACCESS_TOKEN_SECRET'),
        'bearer_token' => env('X_BEARER_TOKEN'),
        'username' => env('X_USERNAME'),
        'genre' => env('X_CONTENT_GENRE', 'プログラミング・技術'),
    ],

    'threads' => [
        'access_token' => env('THREADS_ACCESS_TOKEN'),
        'user_id' => env('THREADS_USER_ID'),
        'username' => env('THREADS_USERNAME'),
        'genre' => env('THREADS_CONTENT_GENRE', 'プログラミング・技術'),
    ],

    'note' => [
        'genre' => env('NOTE_CONTENT_GENRE', 'プログラミング・技術'),
        'target_chars' => env('NOTE_TARGET_CHARS', 2500),
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-opus-4-7'),
    ],

];
