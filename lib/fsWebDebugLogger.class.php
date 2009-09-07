<?php

class fsWebDebugLogger extends sfWebDebugLogger
{
  protected
    $webDebug = null;

  /**
   * Initializes the web debug logger.
   *
   * @param array Logger options
   */
  public function initialize($options = array())
  {
   if (!sfConfig::get('sf_web_debug'))
   {
     return;
   }

   $this->webDebug = fsWebDebugForSf10::getInstance();
  }
}
