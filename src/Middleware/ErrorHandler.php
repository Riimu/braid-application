<?php

namespace Riimu\Braid\Application\Middleware;

use Zend\Diactoros\Request;
use Zend\Diactoros\Response;

/**
 * ErrorHandler.
 *
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2016, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ErrorHandler
{
    public function __invoke(Request $request, Response $response, callable $next)
    {
        
        return $next;
    }
}
