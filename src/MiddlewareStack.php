<?php

namespace Riimu\Braid\Application;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * MiddlewareStack.
 *
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2016, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class MiddlewareStack
{
    private $stack;

    public function __construct(array $middlewares = [])
    {
        $this->stack = [];

        foreach ($middlewares as $callable) {
            $this->push($callable);
        }
    }

    public function push(callable $callable)
    {
        $this->stack[] = $callable;
    }

    public function processStack(Request $request, Response $response)
    {
        $next = function (Request $request, Response $response) use (& $next) {
            $response = call_user_func(array_shift($this->stack), $request, $response, $next);

            if (!$response instanceof Response) {
                throw new \RuntimeException("Middlewares must return a ResponseInterface");
            }

            return $response;
        };

        return $next($request, $response);
    }
}
