<?php

namespace Riimu\Braid\Template;

use Latte\Engine;
use Riimu\Kit\PathJoin\Path;

/**
 * LatteTemplate.
 *
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2016, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class LatteTemplate extends AbstractTemplate
{
    private $engine;
    private $templatePath;

    public function __construct()
    {
        $this->engine = new Engine();
        $this->templatePath = '.';
    }

    public function setTemplatePath($path)
    {
        $this->templatePath = $path;
    }

    public function renderResponse($template, array $params = [])
    {
        return $this->getHtmlResponse($this->engine->renderToString(
            Path::join($this->templatePath, $template . '.latte'),
            $params
        ));
    }
}
