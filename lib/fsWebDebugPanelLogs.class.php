<?php

class fsWebDebugPanelLogs extends sfWebDebugPanelLogs
{
  public function getTitle()
  {
    return 'logs';
  }

  public function getPanelTitle()
  {
    return 'Logs & Messages';
  }

  public function getPanelContent()
  {
    $event = $this->webDebug->getEventDispatcher()->filter(new sfEvent($this, 'debug.web.filter_logs'), $this->webDebug->getLogger()->getLogs());
    $logs = $event->getReturnValue();
    
    $logData = array();
    
    $line_nb = 0;
    foreach ($logs as $log)
    {
      $priority = $this->webDebug->getPriority($log['priority']);

      // xdebug information
      $debug_info = array();
      if (count($log['debug_stack']))
      {
        foreach ($log['debug_stack'] as $i => $logLine)
        {
          array_push($debug_info, array('line_nb' => $i, 
                                        'message' => $this->formatLogLine($logLine),
                                       ));
        }
      }

      ++$line_nb;

       array_push($logData, array(
             'line_nb'     => $line_nb, 
             'priority'   => $priority, 
             'type'       => $log['type'], 
             'message'    => $this->formatLogLine($log['message']),
             'debug_info' => $debug_info
            ));
    }

    return array('logData' => $logData, 'types' => $this->webDebug->getLogger()->getTypes());
  }

  /**
   * Formats a log line.
   *
   * @param string $logLine The log line to format
   *
   * @return string The formatted log lin
   */
  protected function formatLogLine($logLine)
  {
    static $constants;

    if (!$constants)
    {
      foreach (array('sf_app_dir', 'sf_root_dir', 'sf_symfony_lib_dir') as $constant)
      {
        $constants[realpath(sfConfig::get($constant)).DIRECTORY_SEPARATOR] = $constant.DIRECTORY_SEPARATOR;
      }
    }

    // escape HTML
    $logLine = htmlspecialchars($logLine, ENT_NOQUOTES, sfConfig::get('sf_charset'));

    // replace constants value with constant name
    $logLine = str_replace(array_keys($constants), array_values($constants), $logLine);

    $logLine = sfToolkit::pregtr($logLine, array('/&quot;(.+?)&quot;/s' => '"\\1"',
                                                   '/^(.+?)\(\)\:/S'      => '\\1():',
                                                   '/line (\d+)$/'        => 'line \\1'));

    // special formatting for SQL lines
    $logLine = preg_replace('/\b(SELECT|FROM|AS|LIMIT|ASC|COUNT|DESC|WHERE|LEFT JOIN|INNER JOIN|RIGHT JOIN|ORDER BY|GROUP BY|IN|LIKE|DISTINCT|DELETE|INSERT|INTO|VALUES)\b/', '<span class="sfWebDebugLogInfo">\\1</span>', $logLine);

    // remove username/password from DSN
    if (strpos($logLine, 'DSN') !== false)
    {
      $logLine = preg_replace("/=&gt;\s+'?[^'\s,]+'?/", "=&gt; '****'", $logLine);
    }

    return $logLine;
  }
}
