<?php

namespace zc\lib;

use \esprit\core\AbstractView;
use \esprit\core\Response;
use \esprit\core\Config;
use \esprit\core\util\Logger;
use \esprit\core\TemplateParser;


/**
 * The base view that all ZippyChat views should *probably* inherit from.
 *
 * @author jbowens
 * @since 2012-08-24
 */
abstract class BaseView extends AbstractView
{

    protected $urlUtil;

    /**
     * Adds a javascript file to be included into the page.
     *
     * @param  $script the js file to be included
     */
    protected function addScript( $script )
    {
        $templateParser = $this->templateParser;
        $currentScripts = $templateParser->getVariable('jsScripts');
        if( $currentScripts == null )
            $currentScripts = array();
        array_push($currentScripts, $script);
        $templateParser->setVariable('jsScripts', $currentScripts);
    }

    public function generateOutput(Response $response)
    {
        // Store some zc-specific but global values
        $site = $response->getRequest()->getSite();
        $this->urlUtil = new util\UrlUtil( $site );
        $this->set( 'urlutil', $this->urlUtil );
        $this->set( 'fb_integration', $this->config->get('fb_integration') );
        $this->set( 'twitter_integration', $this->config->get('twitter_integration') );
        $this->set( 'flashEnabled', $response->getRequest()->hasFlag('flashEnabled'));
        $this->set( 'flashKnown', $response->getRequest()->isFlagDefined('flashEnabled'));

        // Determine whether or not we should try to determine if flash is enabled
        if( $response->getRequest()->hasFlag('flashIndeterminate') && $this->config->get('flash_flagger') )
        {
            // We don't know if this user supports flash. Let's find out.
            $this->set( 'run_flash_check', true );
        }

        // Set some global values that we need on every page
        $this->setGlobalValues();

        // Run output logic
        $this->output( $response ); 
    }

    protected function setGlobalValues()
    {
        $this->set('year', date("Y"));
    }

    /**
     * Output logic in subclasses should go here!
     */
    protected abstract function output(Response $response);

}
