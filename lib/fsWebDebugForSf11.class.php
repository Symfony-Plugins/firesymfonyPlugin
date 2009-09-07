<?php

class fsWebDebugForSf11 extends sfWebDebug
{
  protected 
    $cache_info = array();

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
    $logLine = preg_replace('/\b(SELECT|FROM|AS|LIMIT|ASC|COUNT|DESC|WHERE|LEFT JOIN|INNER JOIN|RIGHT JOIN|ORDER BY|GROUP BY|IN|LIKE|DISTINCT|DELETE|INSERT|INTO|VALUES)\b/', '\\1', $logLine);

    // remove username/password from DSN
    if (strpos($logLine, 'DSN') !== false)
    {
      $logLine = preg_replace("/=&gt;\s+'?[^'\s,]+'?/", "=&gt; '****'", $logLine);
    }

    return $logLine;
  }

  /**
   * Returns the web debug toolbar as HTML.
   *
   * @return string The web debug toolbar HTML
   */
  public function getResults()
  {
    if (!sfConfig::get('sf_web_debug'))
    {
      return '';
    }
    
    $logPanel = array();
    $configPanel = $this->getCurrentConfigAsArray();
    $databasePanel = array();
    $memoryPanel = array();
    $timersPanel = array();

    $this->loadHelpers();

    $result = '';

    // max priority
    $maxPriority = '';
    if (sfConfig::get('sf_logging_enabled'))
    {
      $maxPriority = $this->getPriority($this->maxPriority);
    }

    $logData = array();
    //Database Information
    $sqlLogs = array();
    if (sfConfig::get('sf_logging_enabled'))
    {
      $line_nb = 0;
      foreach ($this->log as $logEntry)
      {
        $log = $logEntry['message'];
        $priority = $this->getPriority($logEntry['priority']);

        // xdebug information
        $debug_info = array();
        if (isset($logEntry['debug_stack']))
        {
          foreach ($logEntry['debug_stack'] as $i => $logLine)
          {
            array_push($debug_info, array('line_nb' => $i, 
                                          'message' => $this->formatLogLine($logLine),
                                         ));
          }
        }
        
        // format log
        $log = $this->formatLogLine($log);

        // sql queries log
        if (preg_match('/execute(?:Query|Update).+?\:\s+(.+)$/', $log, $match))
        {
          $queryLog =  trim($match[1]);
          preg_match('/(\[.*\])\s?(.*)/', $queryLog, $queryMatches);
          
          if(count($queryMatches) > 1)
          {
            $sqlLogs[] = array('time' => isset($queryMatches[1]) ? $queryMatches[1] : '-',
                               'query' => isset($queryMatches[2]) ? $queryMatches[2] : '-'
                               );
          }
          else
          {
            $sqlLogs[] = array('time' => '-',
                               'query' => $queryLog
                               );
          }
        }
        
        ++$line_nb; 

        $logData[] = array(
                           'line_nb'     => $line_nb, 
                           'priority'   => $priority, 
                           'type'       => $logEntry['type'], 
                           'message'    => $log,
                           'debug_info' => $debug_info
                          );
      }//end foreach

      ksort($this->types);
      $types = array();
      foreach($this->types as $t=>$n)
      {
        $types[$n] = $t;
      }
      $logPanel = array('logData' => $logData, 'types' => $types);
    }

    // ignore cache link and cache info
    $cachePanel = array();
    if (sfConfig::get('sf_debug') && sfConfig::get('sf_cache'))
    {
      $cachePanel['reload_url'] = ((strpos($_SERVER['PHP_SELF'], '_sf_ignore_cache') === false) ? '?_sf_ignore_cache=1' : '');
      $cachePanel['cache_info'] = $this->cache_info;
    }

    // memory used
    $memoryPanel = '';
    if (sfConfig::get('sf_debug') && function_exists('memory_get_usage'))
    {
      $totalMemory = sprintf('%.1f', (memory_get_usage() / 1024));
      $memoryPanel = $totalMemory .' KB';
    }

    // total time elapsed
    $totalTime = '';
    if (sfConfig::get('sf_debug'))
    {
      $totalTime = (microtime(true) - sfConfig::get('sf_timer_start')) * 1000;
      $totalTime = sprintf(($totalTime <= 1) ? '%.2f' : '%.0f', $totalTime);
    }
    
    $timersPanel = array();
    
    //my timers
    $timer_nb = 1;
    foreach (sfTimerManager::getTimers() as $name => $timer)
    {
      $timersPanel[] =  array(
                                'number'  => $timer_nb,
                                'name'    => $name,
                                'calls'   => $timer->getCalls(),
                                'time'    => $timer->getElapsedTime() * 1000,
                                'percent' => $totalTime ? ($timer->getElapsedTime() * 1000 * 100 / $totalTime) : 'N/A'
                              );
      $timer_nb++;
    }
    
    $timersPanel['total'] = 'Total time: '.$totalTime.' ms';
    
    $panels = array();
    $panels['config'] = array(
                        'title'=> 'Configuration and request variables', 
                        'content'=> $configPanel
                       );
    
    $panels['cache'] = array(
                         'title'=> 'reload and ignore cache', 
                         'content'=> $cachePanel
                        );

    $panels['logs'] = array(
                         'title'=> 'Logs & Messages', 
                         'content'=> $logPanel
                        );

    $panels['memory'] = array(
                         'title'=> 'Memory usage', 
                         'content'=> $memoryPanel
                        );

    $panels['version'] = array(
                         'title'=> 'symfony version', 
                         'content'=> "sf-".SYMFONY_VERSION
                        );

    $panels['timer'] = array(
                         'title'=> 'Timers', 
                         'content'=> $timersPanel
                        );
                        
    $panels['database'] = array(
                         'title'=> 'Database information', 
                         'content'=> $sqlLogs
                        );
                        
    $panels['info'] = array(
                            'title' => 'Information',
                            'content'=> $this->getInfoPanel()
                            );
    
    return "<script type=\"text/javascript\"> //<![CDATA[ \n FireSymfonyDebugData = ". json_encode($panels) ."\n //]]></script>";
  }
  
  /**
   * Listens to the 'view.cache.filter_content' event to decorate a chunk of HTML with cache information.
   *
   * @param sfEvent $event   A sfEvent instance
   * @param string  $content The HTML content
   *
   * @return string The decorated HTML string
   */
  public function decorateContentWithDebug(sfEvent $event, $content)
  {
    // don't decorate if not html or if content is null
    if (!sfConfig::get('sf_web_debug') || !$content || false === strpos($event['response']->getContentType(), 'html'))
    {
      return $content;
    }

    $viewCacheManager = $event->getSubject();
    
    $uri = htmlspecialchars($event['uri'], ENT_QUOTES, sfConfig::get('sf_charset'));
    $life_time = $viewCacheManager->getLifeTime($event['uri']);
    $bg_color      = $event['new'] ? '#9ff' : '#ff9';
    $last_modified = $viewCacheManager->getLastModified($event['uri']);
    $id            = md5($event['uri']);
    $content = '<div id="'.$id.'" class="fire-symfony-cache">'.$content.'</div>';
    
    $cache_data = array(
                    'id'=>$id,
                    'uri'=> $uri,
                    'last_modified' => (time() - $last_modified),
                    'is_new' => $event['new'],
                    'life_time'=> $life_time
                  );
    
    $this->cache_info[] = $cache_data;

    return $content;
  }
  
  public function getCurrentConfigAsArray()
  {
    $content = array();

    $context = sfContext::getInstance();

    $content['request']  = sfDebug::requestAsArray($context->getRequest());
    $content['response'] = sfDebug::responseAsArray($context->getResponse());
    $content['user']     = sfDebug::userAsArray($context->getUser());
    $content['settings'] = sfDebug::settingsAsArray();
    $content['globals']  = sfDebug::globalsAsArray();
    $content['php']      = sfDebug::phpInfoAsArray();
    $content['symfony']  = sfDebug::symfonyInfoAsArray();
    return $content;
  }
  
  public function getInfoPanel()
  {
    $content = array();
    $content['details'] = $this->getConfigDetails();
    $content['version'] = 'sf-'.SYMFONY_VERSION;
    return $content;
  }
  
  public function getConfigDetails()
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
