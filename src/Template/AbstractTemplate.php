<?php

namespace Riimu\Braid\Template;

use Zend\Diactoros\Response\HtmlResponse;

/**
 * AbstractTemplate.
 *
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2016, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
abstract class AbstractTemplate implements TemplateInterface
{
    protected function getHtmlResponse($output)
    {
        return new HtmlResponse($output);
    }
}
