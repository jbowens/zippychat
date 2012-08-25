<?php

namespace zc\views;

use \zc\lib\BaseView;

use \esprit\core\Config;
use \esprit\core\util\Logger;
use \esprit\core\TemplateParser;
use \esprit\core\Response;

/**
 * Forwards output responsibility to the default esprit implementation, but still benefits from 
 * all of the extra work we do in BaseView.generateOutput().
 *
 * @author jbowens
 * @since 2012-08-24
 */
class DefaultView extends BaseView
{

    protected $espritDefaultView;

    public function __construct(Config $config, Logger $logger, TemplateParser $templateParser)
    {
        $this->espritDefaultView = new \esprit\core\views\DefaultView($config, $logger, $templateParser);
        parent::__construct($config, $logger, $templateParser);
    }

    protected function output(Response $response)
    {
        $this->espritDefaultView->generateOutput($response);
    }

}
