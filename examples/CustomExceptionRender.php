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