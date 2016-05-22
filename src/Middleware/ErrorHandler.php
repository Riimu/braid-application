<?php

namespace Riimu\Braid\Application\Middleware;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Riimu\Braid\Application\Template\DefaultTemplate;
use Riimu\Braid\Application\Template\TemplateInterface;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\TextResponse;

/**
 * ErrorHandler.
 *
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2016, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ErrorHandler
{
    private $debug;
    private $template;
    private $errorTemplate;
    private $jsonType;
    private $jsonRequest;

    public function __construct(TemplateInterface $template = null)
    {
        $this->debug = false;
        $this->template = $template ?: new DefaultTemplate();
        $this->errorTemplate = 'error/internal_server_error';
        $this->jsonType = 'application/json';
    }

    public function setErrorTemplate($name)
    {
        $this->errorTemplate = (string) $name;
    }

    public function enableDebugMode($enabled = true)
    {
        $this->debug = (bool) $enabled;
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        $handler = new Run();
        $handler->allowQuit(false);
        $handler->writeToOutput(false);

        set_error_handler([$handler, 'handleError']);

        try {
            return $next($request, $response);
        } catch (\Throwable $exception) {
            if (!isset($this->jsonRequest)) {
                $this->jsonRequest = $this->isJsonRequest($request);
            }

            return $this->getErrorResponse($handler, $exception);
        } finally {
            restore_error_handler();
        }
    }

    private function getErrorResponse(Run $handler, \Throwable $exception)
    {
        if ($this->debug) {
            if ($this->jsonRequest) {
                $contentType = 'application/json';
                $pageHandler = new JsonResponseHandler();
                $pageHandler->addTraceToOutput(true);
            } else {
                $contentType = 'text/html; charset=utf-8';
                $pageHandler = new PrettyPageHandler();
            }

            $handler->pushHandler($pageHandler);

            return new TextResponse(
                $handler->handleException($exception),
                500,
                ['Content-Type' => $contentType]
            );
        }

        if ($this->jsonRequest) {
            return new JsonResponse(['error' => true], 500);
        }

        return $this->template->renderResponse($this->errorTemplate)->withStatus(500);
    }

    private function isJsonRequest(Request $request)
    {
        if ($this->isJsonMimeType($request->getHeaderLine('Content-Type'))) {
            return true;
        } elseif ($this->isJsonMimeType($request->getHeaderLine('Accept'))) {
            return true;
        }

        return false;
    }

    private function isJsonMimeType($string)
    {
        return strncasecmp($string, $this->jsonType, strlen($this->jsonType)) === 0;
    }
}
