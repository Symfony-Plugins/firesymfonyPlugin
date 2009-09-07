<?php
class fsWebDebugPanelInfo extends sfWebDebugPanel
{
  public function getTitle()
  {
    return 'info';
  }
  
  public function getPanelTitle()
  {
    return 'Information';
  }
  
  public function getPanelContent()
  {
    $content = array();
    $content['details'] = $this->getConfigDetails();
    $content['version'] = 'sf-' . SYMFONY_VERSION;
    return $content;
  }
  
  protected function getConfigDetails()
  {
    return array(
      'debug'        => sfConfig::get('sf_debug')           ? 'on' : 'off',
      'xdebug'       => extension_loaded('xdebug')          ? 'on' : 'off',
      'logging'      => sfConfig::get('sf_logging_enabled') ? 'on' : 'off',
      'cache'        => sfConfig::get('sf_cache')           ? 'on' : 'off',
      'compression'  => sfConfig::get('sf_compressed')      ? 'on' : 'off',
      'tokenizer'    => function_exists('token_get_all')    ? 'on' : 'off',
      'eaccelerator' => extension_loaded('eaccelerator') && ini_get('eaccelerator.enable') ? 'on' : 'off',
      'apc'          => extension_loaded('apc') && ini_get('apc.enabled')                  ? 'on' : 'off',
      'xcache'       => extension_loaded('xcache') && ini_get('xcache.cacher')             ? 'on' : 'off',
    );
  }
}