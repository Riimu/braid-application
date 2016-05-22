<?php

namespace Riimu\Braid\Application\Template;

/**
 * DefaultTemplate.
 *
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2016, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class DefaultTemplate extends LatteTemplate
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplatePath(__DIR__);
    }
}
