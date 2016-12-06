# Laravel error reporting

## Install

### composer
```bash
composer require ...
```

### ENV (config)
```env
ERROR_REPORTING_EMAIL_FROM=example@example.com
ERROR_REPORTING_EMAIL_FROM_NAME=Example name
ERROR_REPORTING_EMAIL_RECIPIENTS=example.recipients@example.com
ERROR_REPORTING_EMAIL_SUBJECT=Test %APP_ENVIRONMENT%
```

### config error.reporting.php
```php
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
    'emailTemplate' => ''
);

```
If you do not want report excpetion class, then you can add class to array doNotReport.
If you want use custom template, then you have to put emailTemplate value.

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
class ExceptionUsingReport extends Exception implements \ErrorReporting\Interfaces\IReportException
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
class ExceptionNotUsingReport extends Exception implements \ErrorReporting\Interfaces\IReportException
{
    use \ErrorReporting\Exceptions\Traits\DoNotReportToEmail;

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