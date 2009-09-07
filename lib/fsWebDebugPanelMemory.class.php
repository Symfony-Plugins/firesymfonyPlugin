<?php

class fsWebDebugPanelMemory extends sfWebDebugPanelMemory {
  
  public function getTitle()
  {
    return 'memory';
  }
  
  public function getPanelTitle()
  {
    return 'Memory usage';
  }
  
  public function getPanelContent()
  {
    if (function_exists('memory_get_usage'))
    {
      $totalMemory = sprintf('%.1f', (memory_get_usage() / 1024));
      return $totalMemory .' KB';
    }
  }
}