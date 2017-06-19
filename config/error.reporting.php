<?php

return array(

    'doNotReportClasses' => [
        Illuminate\Auth\Access\AuthorizationException::class,
        Illuminate\Foundation\Testing\HttpException::class,
        Illuminate\Database\Eloquent\ModelNotFoundException::class,
        Illuminate\Validation\ValidationException::class
    ],
    'doNotReportIpv4Addresses' => [
        '127.0.0.0'
    ],
    'emailFrom' => env("ERROR_REPORTING_EMAIL_FROM"),
    'emailFromName' => env("ERROR_REPORTING_EMAIL_FROM_NAME"),
    'emailRecipients' => preg_split("/\\s+/", env("ERROR_REPORTING_EMAIL_RECIPIENTS", "")),
    'emailSubject' => env("ERROR_REPORTING_EMAIL_SUBJECT"),
    'emailTemplate' => '',
    'customExceptionRender' => [
        'className' => RedFunction\ErrorReporting\Examples\CustomExceptionRender::class,
        'usingException' => [
            RedFunction\ErrorReporting\Examples\ExceptionNotUsingReport::class
        ]
    ],
    'logStackTrace' => env("ERROR_REPORTING_LOG_STACK_TRACE", false),
    'encryptionAlgorithm' => 'md5',
    'encryptionFields' => [
        'HTTP_AUTHORIZATION',
        [
            'regexPattern' => 'PASSWORD$',
            'useUpperCase'
        ]
    ]
);