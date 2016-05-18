<?php

namespace Riimu\Braid\Template;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface for template rendering classes.
 *
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2016, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
interface TemplateInterface
{
    /**
     * Sets the path to template directory.
     * @param string $path Path to the template file directory
     * @return void
     */
    public function setTemplatePath($path);

    /**
     * Renders the given template and returns it as a HTTP response.
     * @param string $template The template to render
     * @param array $params Parameters for the template
     * @return ResponseInterface The rendered response
     */
    public function renderResponse($template, array $params = []);
}
