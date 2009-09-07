<?php
class firesymfonyPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    if(sfConfig::get('sf_debug') && sfConfig::get('sf_cache'))
    {
      $this->dispatcher->connect('view.cache.filter_content', array('fsWebDebugPanelCache', 'decorateContentWithDebug'));
    }
  }
}