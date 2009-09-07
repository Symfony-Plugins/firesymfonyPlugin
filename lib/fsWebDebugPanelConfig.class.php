<?php 

class fsWebDebugPanelConfig extends sfWebDebugPanelConfig
{
  public function getTitle()
  {
    return 'config';
  }

  public function getPanelTitle()
  {
    return 'Configuration and request variables';
  }

  public function getPanelContent()
  {
    $content = array();
    
    if (sfConfig::get('sf_logging_enabled'))
    {
      $context = sfContext::getInstance();    
    
      $content['request']  = sfDebug::requestAsArray($context->getRequest());
      $content['response'] = sfDebug::responseAsArray($context->getResponse());
      $content['user']     = sfDebug::userAsArray($context->getUser());
      $content['settings'] = sfDebug::settingsAsArray();
      $content['globals']  = sfDebug::globalsAsArray();
      $content['php']      = sfDebug::phpInfoAsArray();
      $content['symfony']  = sfDebug::symfonyInfoAsArray();
    }
    else
    {
      $content['Logging disabled'] = array();
    }
    
    return $content;
  }
}