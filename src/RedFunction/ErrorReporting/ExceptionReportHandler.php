<?php

namespace RedFunction\ErrorReporting;

use Exception;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Illuminate\View\Engines\PhpEngine;
use Psr\Log\LoggerInterface;
use RedFunction\ErrorReporting\Interfaces\IOptionReport;
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
     * A list of ipv4 address that should not be reported.
     *
     * @var array
     */
    protected $doNotReportIpv4Addresses = [];

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

    /**
     * Custom exception render class
     *
     * @var string|null
     */
    protected $customExceptionRenderClass = null;

    /**
     * Custom exception render class
     *
     * @var array
     */
    protected $customExceptionRenderUsing = [];

    /**
     * Write log strace
     *
     * @var bool
     */
    protected $logStackTrace = true;

    /**
     * JSON response long message
     *
     * @var bool
     */
    protected $jsonResponseLongMessage = true;

    /**
     * Encryption algorithm
     *
     * @var string|null
     *
     */
    protected $encryptionAlgorithm = null;

    /**
     * Encryption field list
     *
     * @var array
     *
     */
    protected $encryptionFields = array();

    /**
     * ExceptionReportHandler constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $config = config('error.reporting');
        if ($config != null) {
            if(!empty($config['doNotReportClasses']))
                $this->dontReport = $config['doNotReportClasses'];
            if(!empty($config['doNotReportIpv4Addresses']))
                $this->doNotReportIpv4Addresses = $config['doNotReportIpv4Addresses'];
            $this->emailFrom = $config['emailFrom'];
            $this->emailFromName = $config['emailFromName'];
            $this->emailRecipients = $config['emailRecipients'];
            $this->emailSubject = $config['emailSubject'];
            $this->emailTemplate = $config['emailTemplate'];
            if (!empty($config['logStackTrace']))
                $this->logStackTrace = $config['logStackTrace'];
            if (!empty($config['encryptionAlgorithm']))
                $this->encryptionAlgorithm = $config['encryptionAlgorithm'];
            if (!empty($config['encryptionFields']))
                $this->encryptionFields = $config['encryptionFields'];
            if(!empty($config['customExceptionRender'])){
                $customExceptionRender = $config['customExceptionRender'];
                if(!empty($customExceptionRender['className']) && !empty($customExceptionRender['usingException'])){
                    $className = $customExceptionRender['className'];
                    if(trim($className) != ''){
                        $this->customExceptionRenderClass = $customExceptionRender['className'];
                        $this->customExceptionRenderUsing = $customExceptionRender['usingException'];
                    }
                }
            }
        }
    }

    /**
     * @param \Exception|IOptionReport|Exception $e
     * @return bool
     */
    private function canReport(Exception $e)
    {
        foreach ($this->dontReport as $each) {
            if ($e instanceof $each)
                return false;
        }
        $ipv4Client = $this->getIpv4Address();
        if($ipv4Client != null)
        {
            $ipv4ClientLong = ip2long($ipv4Client);
            foreach ($this->doNotReportIpv4Addresses as $doNotReportIpv4Address)
            {
                $ipPart = explode("/", $doNotReportIpv4Address);
                if(count($ipPart) == 2)
                {
                    $ipLong = ip2long($ipPart[0]);
                    $ipMask = ~((1 << (32 - $ipPart[1])) - 1);
                    if($ipLong == ($ipv4ClientLong & $ipMask))
                        return false;
                }
                elseif ($ipv4Client == $doNotReportIpv4Address)
                    return false;
            }
        }
        if (in_array(IOptionReport::class, class_implements($e)))
            return $e->canReportToEmail();
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
     * @param  \Exception|IReportException $e
     *
     * @throws Exception
     */
    public function report(Exception $e)
    {
        $canReport = $this->canReport($e);
        if ($canReport) {
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
        }
        if (in_array(IReportException::class, class_implements($e)))
            $this->writeLog($e->getLogType(), $e->getLogMessage());
        else if($canReport)
        {
            try
            {
                /** @var LoggerInterface $logger */
                $logger = $this->container->make(LoggerInterface::class);

                $exceptionClass = get_class($e);
                $logContent = "{$exceptionClass}" . (empty(trim($e->getMessage())) ? '' : ": {$e->getMessage()}") . " in {$e->getFile()}:{$e->getLine()}";
                if ($this->logStackTrace) {
                    $logContent .= "\nStack trace:\n{$e->getTraceAsString()}";
                }
                $logger->error($logContent);
            }
            catch (Exception $ex) {
                throw $e;
            }
        }
    }

    private function encryptArray($array)
    {
        if (empty($this->encryptionAlgorithm))
            return [];
        $encryptedArray = [];
        foreach ($array as $field => $value) {
            if ($this->canEncrypt($field)) {
                $encryptedArray[$field] = hash($this->encryptionAlgorithm, $value);
            }
        }
        return $encryptedArray;
    }

    private function canEncrypt($fieldName)
    {
        foreach ($this->encryptionFields as $encryptionField) {
            if (is_array($encryptionField)) {
                $fieldTemp = $fieldName;
                if (array_key_exists('useUpperCase', $encryptionField)) {
                    $fieldTemp = mb_strtoupper($fieldTemp);
                }
                if (array_key_exists('regexPattern', $encryptionField)) {
                    if (preg_match("/{$encryptionField['regexPattern']}/", $fieldTemp)) {
                        return true;
                    }
                }
                if (array_key_exists('name', $encryptionField)) {
                    if ($fieldTemp == $encryptionField['name']) {
                        return true;
                    }
                }
            } elseif ($fieldName == $encryptionField) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return null|string
     */
    private function getIpv4Address()
    {
        if (function_exists('apache_request_headers'))
            $headers = apache_request_headers();
        else
            $headers = $_SERVER;
        //Get the forwarded IP if it exists
        if (array_key_exists('X-Forwarded-For', $headers) && filter_var(
                $headers['X-Forwarded-For'],
                FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV4
            )
        ) {
            return $headers['X-Forwarded-For'];
        }
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $headers) && filter_var(
                $headers['HTTP_X_FORWARDED_FOR'],
                FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV4
            )
        ) {
            return $headers['X-Forwarded-For'];
        }
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            return $_SERVER['HTTP_CLIENT_IP'];
        if(!isset($_SERVER['REMOTE_ADDR']))
            return null;
        return filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * @param Exception $e
     * @param array $request
     * @param array $server
     * @return string
     */
    private function reportRenderHtml(Exception $e, $request, $server)
    {
        $data = [
            'error' => $e,
            'request' => $request,
            'server' => $server,
            'encryptedData' => [
                'request' => $this->encryptArray($request),
                'server' => $this->encryptArray($server),
            ]
        ];
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
     * @param  \Exception|IReportException|Exception $e
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $e)
    {
        if($this->customExceptionRenderClass != null){
            foreach ($this->customExceptionRenderUsing as $exceptionClass){
                if($e instanceof $exceptionClass){
                    /** @var AbstractCustomExceptionRender $customExceptionRender */
                    $customExceptionRender = new $this->customExceptionRenderClass;
                    $redirect = $customExceptionRender->render($request, $e);
                    $this->writeLog($customExceptionRender->getLogType(), $customExceptionRender->getLogMessage());
                    if($redirect != null) return $redirect;
                }
            }
        }
        $isAjaxException = $request->ajax() || $request->wantsJson();
        if (in_array(IReportException::class, class_implements($e))) {
            $redirectPage = $e->getRedirectPage();
            if ($redirectPage != null && !$isAjaxException) {
                return $redirectPage;
            }
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
            ];
            if ($this->jsonResponseLongMessage) {
                $error['long_message'] = $e->__toString();
            }

            if ($e instanceof ValidationException) {
                $response = $e->getResponse();
                if ($response instanceof JsonResponse) {
                    $data = $response->getData(true);
                    $error['validation_errors'] = $data;
                    if ($this->jsonResponseLongMessage) {
                        unset($error['long_message']);
                    }
                    $statusCode = $response->getStatusCode();
                }
            }
            $error['http_status_code'] = $statusCode;
            return response()->json($error, $statusCode);
        }
        return parent::render($request, $e);
    }

    /**
     * @param integer $type
     * @param string $message
     * @return void
     */
    private function writeLog($type, $message){
        switch ($type) {
            case 1:
                Log::info($message);
                break;
            case 2:
                Log::warning($message);
                break;
            case 3:
                Log::notice($message);
                break;
            case 4:
                Log::error($message);
                break;
        }
    }
}
