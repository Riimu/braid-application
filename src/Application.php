<?php

namespace Riimu\Braid\Application;

use Riimu\Braid\Container\Container;
use Riimu\Braid\Router\Router;
use Riimu\Braid\Template\DefaultTemplate;
use Zend\Diactoros\Request;
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

        $this->container->set([
            Application::class => $this,
        ]);
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

        if (!in_array(Middleware\ErrorHandler::class, $middlewares)) {
            $this->stack->push(new Middleware\ErrorHandler());
        }

        if (!in_array(Middleware\Router::class, $middlewares)) {
            $this->stack->push(new Middleware\Router($this, new DefaultTemplate()));
        }

        foreach ($middlewares as $name) {
            $this->stack->push($this->container->get($name));
        }
    }
}
