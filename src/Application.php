<?php

namespace Riimu\Braid\Application;

use Riimu\Braid\Container\Container;
use Riimu\Braid\Router\Router;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Application.
 *
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2016, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class Application
{
    private $container;
    private $router;
    private $stack;

    public function __construct(Router $router, Container $container)
    {
        $this->router = $router;
        $this->container = $container;
        $this->stack = new MiddlewareStack();

        $this->container->setValues([Application::class => $this]);
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getMiddlewareStack()
    {
        return $this->stack;
    }

    public function run()
    {
        $this->initializeMiddlewareStack();

        $request = ServerRequestFactory::fromGlobals();
        $response = $this->stack->processStack($request, new Response());

        $http = new HttpResponse($response);
        $http->setResponseChunkSize($this->container->load('config.braid.response_chunk_size', 8192));

        if (strcasecmp($request->getMethod(), 'HEAD') === 0) {
            $http->omitBody();
        }

        $http->send();
    }

    private function initializeMiddlewareStack()
    {
        $middlewares = $this->container->load('config.braid.middlewares', []);

        $router = new Middleware\Router($this);
        $errorHandler = new Middleware\ErrorHandler();

        if ($this->container->load('config.braid.debug', false)) {
            $errorHandler->enableDebugMode();
        }

        foreach ([$router, $errorHandler] as $default) {
            if (!in_array(get_class($default), $middlewares)) {
                array_unshift($middlewares, get_class($default));
                $this->container->setValues([get_class($default) => $default]);
            }
        }

        foreach ($middlewares as $name) {
            $this->stack->push($this->container->get($name));
        }
    }
}
