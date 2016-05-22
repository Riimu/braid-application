<?php

namespace Riimu\Braid\Application\Middleware;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Riimu\Braid\Application\Application;
use Riimu\Braid\Application\Template\DefaultTemplate;
use Riimu\Braid\Application\Template\TemplateInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;

/**
 * Router.
 *
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2016, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class Router
{
    private $application;
    private $template;
    private $templateName;

    public function __construct(Application $application, TemplateInterface $template = null)
    {
        $this->application = $application;
        $this->template = $template ?: new DefaultTemplate();
        $this->templateName = 'error/page_not_found';
    }

    public function setTemplateName($name)
    {
        $this->templateName = (string) $name;
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        if (!$request instanceof ServerRequest) {
            throw new \InvalidArgumentException("Routing supported only for server requests");
        }

        $uri = $request->getUri();
        $router = $this->application->getRouter();
        $route = $router->route($request->getMethod(), $uri->getPath());

        if ($route === null) {
            return $this->template->renderResponse($this->templateName, [
                'path' => $uri->getPath()
            ])->withStatus(404);
        }

        if (strcasecmp($uri->getPath(), $route->getCanonicalPath()) !== 0) {
            return new EmptyResponse(302, [
                'Location' => (string) $uri->withPath($route->getCanonicalPath())
            ]);
        }

        $container = $this->application->getContainer();
        $handler = $route->getHandler();
        $stack = $this->application->getMiddlewareStack();

        if ($container->has($handler)) {
            $stack->push($container->load($handler));
        } elseif (class_exists($handler)) {
            $stack->push(new $handler);
        } elseif (is_callable($handler)) {
            $stack->push($handler);
        } else {
            throw new \UnexpectedValueException("Invalid route handler for " . $route->getCanonicalPath());
        }

        foreach ($route->getParams() as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        return $next($request, $response);
    }
}
