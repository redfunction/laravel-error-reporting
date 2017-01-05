<?php

return array(

    'doNotReport' => [
        Illuminate\Auth\Access\AuthorizationException::class,
        Illuminate\Foundation\Testing\HttpException::class,
        Illuminate\Database\Eloquent\ModelNotFoundException::class,
        Illuminate\Validation\ValidationException::class
    ],
    'emailFrom' => env("ERROR_REPORTING_EMAIL_FROM"),
    'emailFromName' => env("ERROR_REPORTING_EMAIL_FROM_NAME"),
    'emailRecipients' => explode(';', env("ERROR_REPORTING_EMAIL_RECIPIENTS", "")),
    'emailSubject' => env("ERROR_REPORTING_EMAIL_SUBJECT"),
    'emailTemplate' => '',
    'customExceptionRender' => [
        'className' => RedFunction\ErrorReporting\Examples\CustomExceptionRender::class,
        'usingException' => [
            RedFunction\ErrorReporting\Examples\ExceptionNotUsingReport::class
        ]
    ]

);