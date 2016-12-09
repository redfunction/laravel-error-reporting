<?php

namespace RedFunction\ErrorReporting;

use Exception;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\View\Engines\PhpEngine;
use RedFunction\ErrorReporting\Interfaces\IReportException;
use RedFunction\ErrorReporting\Traits\DoNotReportToEmail;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Class ExceptionReportHandler
 *
 * @package RedFunction\ErrorReporting
 */
class ExceptionReportHandler extends Handler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * E-mail from address
     *
     * @var string
     */
    protected $emailFrom;

    /**
     * E-mail from name
     *
     * @var string
     */
    protected $emailFromName;

    /**
     * E-mail Recipients
     *
     * @var array(string)
     */
    protected $emailRecipients;

    /**
     * E-mail subject
     *
     * @var string
     */
    protected $emailSubject;

    /**
     * E-mail template
     *
     * @var string
     */
    protected $emailTemplate;


    public function __construct(Container $container)
    {
        parent::__construct($container);
        $config = config('error.reporting');
        if ($config != null) {
            $this->dontReport = $config['doNotReport'];
            $this->emailFrom = $config['emailFrom'];
            $this->emailFromName = $config['emailFromName'];
            $this->emailRecipients = $config['emailRecipients'];
            $this->emailSubject = $config['emailSubject'];
            $this->emailTemplate = $config['emailTemplate'];
        }
    }

    /**
     * @param Exception $e
     * @return bool
     */
    private function canReport(Exception $e)
    {
        foreach ($this->dontReport as $each) {
            if ($e instanceof $each)
                return false;
        }
        return !$this->objectHasTrait($e, DoNotReportToEmail::class);
    }


    /**
     * @param object $obj
     * @param mixed $trait
     * @return bool
     */
    private function objectHasTrait($obj, $trait)
    {
        //check arguments
        $used = class_uses($obj);
        if (!isset($used[$trait])) {
            $parents = class_parents($obj);
            while (!isset($used[$trait]) && $parents) {
                //get trait used by parents
                $used = class_uses(array_pop($parents));
            }
        }
        return isset($used[$trait]);//return bool
    }

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     *
     * @return void
     */
    public function report(Exception $e)
    {
        if ($this->canReport($e)) {
            if ($this->emailFrom) {
                if (App::offsetExists('mailer')) {
                    $emailSubject = $this->emailSubject;
                    $emailSubject = str_replace("%APP_ENVIRONMENT%", App::environment(), $emailSubject);
                    // in case we have xdebug, we don't want it to override var_dump any longer
                    ini_set('xdebug.overload_var_dump', 0);
                    Mail::raw('', function ($message) use ($e, $emailSubject) {
                        $message->from($this->emailFrom, $this->emailFromName);
                        $message->setBody($this->reportRenderHtml($e, $_REQUEST, $_SERVER), 'text/html');
                        $message->to($this->emailRecipients)->subject($emailSubject);
                    }
                    );
                }
            }
            parent::report($e);
        }
    }

    /**
     * @param Exception $e
     * @param array $request
     * @param array $server
     * @return string
     */
    private function reportRenderHtml(Exception $e, $request, $server)
    {
        $data = ['error' => $e, 'request' => $request, 'server' => $server];
        if ($this->emailTemplate == '') {
            $phpEngine = new PhpEngine();
            return $phpEngine->get(__DIR__ . '/../../../resources/views/emails/exception.blade.php', $data);
        }
        return View::make($this->emailTemplate, $data)->render();
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception|IReportException $e
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function render($request, Exception $e)
    {
        $isAjaxException = $request->ajax() || $request->wantsJson();
        if (in_array(IReportException::class, class_implements($e))) {
            $logMessage = $e->getLogMessage();
            switch ($e->getLogType()) {
                case 1:
                    Log::info($logMessage);
                    break;
                case 2:
                    Log::warning($logMessage);
                    break;
                case 3:
                    Log::notice($logMessage);
                    break;
                case 4:
                    Log::error($logMessage);
                    break;
            }
            $redirectPage = $e->getRedirectPage();
            if ($redirectPage != null && !$isAjaxException) {
                return $redirectPage;
            }
        } else {
            Log::error($e->getMessage());
        }

        if ($isAjaxException) {
            $statusCode = 500;
            if ($e instanceof HttpExceptionInterface) {
                $statusCode = $e->getStatusCode();
            }

            $error = [
                'status' => false,
                'http_status_code' => $statusCode,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'error_class_name' => get_class($e),
                'long_message' => $e->__toString(),
            ];

            if ($e instanceof HttpResponseException) {
                $response = $e->getResponse();
                if ($response instanceof JsonResponse) {
                    $data = $response->getData(true);
                    $error['validation_errors'] = $data;
                    $statusCode = $response->getStatusCode();
                }
            }
            $error['http_status_code'] = $statusCode;
            return response()->json($error, $statusCode);
        }
        return parent::render($request, $e);
    }
}
