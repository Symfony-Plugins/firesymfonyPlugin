<?php

class fsWebDebugPanelTimer extends sfWebDebugPanelTimer {

  public function getTitle()
  {
    return 'timer';
  }

  public function getPanelTitle()
  {
    return 'Timers';
  }

  public function getPanelContent()
  {
    $panel = array();
    $timers = sfTimerManager::getTimers();
    if (sfConfig::get('sf_debug') && $timers)
    {
      $totalTime = $this->getTotalTime();
      $timer_nb = 1;
      foreach ($timers as $name => $timer)
      {
        array_push($panel, array(
                                  'number'  => $timer_nb,
                                  'name'    => $name,
                                  'calls'   => $timer->getCalls(),
                                  'time'    => $timer->getElapsedTime() * 1000,
                                  'percent' => $totalTime ? ($timer->getElapsedTime() * 1000 * 100 / $totalTime) : 'N/A'
                                ));
        $timer_nb++;
      }
      $panel['total'] = 'Total time: '.$this->getTotalTime().' ms';
    }
    else
    {
      $panel['total'] = 'No info available';
    }
      
    return $panel;
  }

  public function filterLogs(sfEvent $event, $logs)
  {
    $newLogs = array();
    foreach ($logs as $log)
    {
      if ('sfWebDebugLogger' != $log['type'])
      {
        $newLogs[] = $log;
      }
    }

    return $newLogs;
  }

  protected function getTotalTime()
  {
    return isset($_SERVER['REQUEST_TIME']) ? sprintf('%.0f', (microtime(true) - $_SERVER['REQUEST_TIME']) * 1000) : 0;
  }
}