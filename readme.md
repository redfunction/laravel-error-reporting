# Laravel error reporting

## Install

### composer
#### Direct download
```bash
composer require redfunction/laravel-error-reporting
```

#### composer.json
```json
{
    "require": {
      "redfunction/laravel-error-reporting" : "*"
    }
}
```

```bash
composer update
```

### ENV (config)
```env
ERROR_REPORTING_EMAIL_FROM=example@example.com
ERROR_REPORTING_EMAIL_FROM_NAME="Example name"
ERROR_REPORTING_EMAIL_RECIPIENTS="example.recipients@example.com second.recipients@example.com"
ERROR_REPORTING_EMAIL_SUBJECT="Test %APP_ENVIRONMENT%"
ERROR_REPORTING_LOG_STACK_TRACE=true
ERROR_REPORTING_JSON_RESPONSE_LONG_MESSAGE=true
```

### config/error.reporting.php
```php
<?php
return array(

    'doNotReportClasses' => [
        Illuminate\Auth\Access\AuthorizationException::class,
        Illuminate\Foundation\Testing\HttpException::class,
        Illuminate\Database\Eloquent\ModelNotFoundException::class,
        Illuminate\Validation\ValidationException::class
    ],
    'doNotReportIpv4Addresses' => [
            '127.0.0.1',
            '192.168.0.0/24'
    ],
    'emailFrom' => env("ERROR_REPORTING_EMAIL_FROM"),
    'emailFromName' => env("ERROR_REPORTING_EMAIL_FROM_NAME"),
    'emailRecipients' => preg_split("/\\s+/", env("ERROR_REPORTING_EMAIL_RECIPIENTS", "")),
    'emailSubject' => env("ERROR_REPORTING_EMAIL_SUBJECT"),
    'emailTemplate' => '',
    'customExceptionRender' => null,
    'logStackTrace' => env("ERROR_REPORTING_LOG_STACK_TRACE", false),
    'jsonResponseLongMessage' => env("ERROR_REPORTING_JSON_RESPONSE_LONG_MESSAGE", false),
    'encryptionAlgorithm' => 'md5',
    'encryptionFields' => [
        'HTTP_AUTHORIZATION',
        [
            'regexPattern' => 'PASSWORD$',
            'useUpperCase'
        ]
    ]
);

```
If you do not want report excpetion class, then you can add class to array doNotReport.
If you want use custom template, then you have to put emailTemplate value.

### Add to plugin to Laravel
You can choice
1) bootstrap/app.php
2) config/app.php

#### bootstrap/app.php
You have to add code.
```php
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    RedFunction\ErrorReporting\ExceptionReportHandler::class
);
```

#### config/app.php
You have to add `\RedFunction\ErrorReporting\Providers\ExceptionReportProvider::class` to providers.
```php 
    'providers' => [
    ...
    \RedFunction\ErrorReporting\Providers\ExceptionReportProvider::class,
    ...
    ]
```

## Example code

### Report is working

You have to add implements of class `\ErrorReporting\Interfaces\IReportException`. 
Report can call methods (getLogMessage, getLogType, getRedirectPage)

```php
<?php
/**
 * Class ExceptionUsingReport
 *
 */
class ExceptionUsingReport extends Exception implements RedFunction\ErrorReporting\Interfaces\IReportException
{

    /**
     * @return string
     */
    public function getLogMessage()
    {
        return "Error 500: reason...";
    }

    /**
     * 1 - INFO
     * 2 - WARNING
     * 3 - NOTICE
     * 4 - ERROR
     * @return integer
     */
    public function getLogType()
    {
        return 4;
    }

    /**
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|null
     */
    public function getRedirectPage()
    {
        return null;
    }
}
```

### Report do not send via E-mail.
You have to add trait `\ErrorReporting\Exceptions\Traits\DoNotReportToEmail`

```php
<?php
/**
 * Class ExceptionNotUsingReport
 *
 */
class ExceptionNotUsingReport extends Exception implements RedFunction\ErrorReporting\Interfaces\IReportException
{
    use RedFunction\ErrorReporting\Traits\DoNotReportToEmail;

    /**
     * @return string
     */
    public function getLogMessage()
    {
        return "Error 500: reason...";
    }

    /**
     * 1 - INFO
     * 2 - WARNING
     * 3 - NOTICE
     * 4 - ERROR
     * @return integer
     */
    public function getLogType()
    {
        return 4;
    }

    /**
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|null
     */
    public function getRedirectPage()
    {
        return null;
    }
}
```
### Using custom render

#### config error.reporting
Example code.
```php
'customExceptionRender' => [
        'className' => RedFunction\ErrorReporting\Examples\CustomExceptionRender::class,
        'usingException' => [
            RedFunction\ErrorReporting\Examples\ExceptionNotUsingReport::class
        ]
    ]
```

#### Create custom render class

```php
<?php
namespace RedFunction\ErrorReporting\Examples;
use Exception;
use Illuminate\Http\Response;
use RedFunction\ErrorReporting\AbstractCustomExceptionRender;


/**
 * Class CustomExceptionRender
 *
 */
class CustomExceptionRender extends AbstractCustomExceptionRender
{
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Exception $e
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function render($request, $e)
    {
        //TODO: You can add something, what you want.
        if ($e instanceof Exception) {
            $this->log(self::LOG_NOTICE, $e->getMessage());
            return new Response($e->getMessage(), $e->getCode());
        }
        return null;
    }

    /**
     * AbstractCustomExceptionRender constructor.
     */
    public function __construct()
    {
    }
}
```