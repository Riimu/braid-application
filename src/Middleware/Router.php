<?php

namespace Riimu\Braid\Application\Middleware;

use Riimu\Braid\Application\Application;
use Riimu\Braid\Template\DefaultTemplate;
use Riimu\Braid\Template\TemplateInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

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
        $uri = $request->getUri();
        $router = $this->application->getRouter();
        $route = $router->route($request->getMethod(), $uri->getPath());

        if ($route === null) {
            return $this->template->renderResponse($this->templateName, [
                'path' => $uri->getPath()
            ])->withStatus(404);
        }

        if (strcasecmp($uri->getPath(), $route->getCanonicalPath()) !== 0) {
            return (new \Zend\Diactoros\Response())
                ->withHeader('Location', $uri->withPath($route->getCanonicalPath()))
                ->withStatus(302);
        }

        $container = $this->application->getContainer();
        $stack = $this->application->getMiddlewareStack();

        $stack->push($container->load($route->getHandler()));

        foreach ($route->getParams() as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        return $next($request, $response);
    }
}
