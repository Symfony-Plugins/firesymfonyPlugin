<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelDoctrine adds a panel to the web debug toolbar with Doctrine information.
 *
 * @package    symfony
 * @subpackage debug
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfWebDebugPanelDoctrine.class.php 11205 2008-08-27 16:24:17Z fabien $
 */
class fsWebDebugPanelDatabase extends sfWebDebugPanel
{
  /**
   * Constructor.
   *
   * @param sfWebDebug $webDebug The web debut toolbar instance
   */
  public function __construct(sfWebDebug $webDebug)
  {
    parent::__construct($webDebug);

    $this->webDebug->getEventDispatcher()->connect('debug.web.filter_logs', array($this, 'filterLogs'));
  }

  /**
   * Get the title/icon for the panel
   *
   * @return string $html
   */
  public function getTitle()
  {
    return 'database';
  }

  /**
   * Get the verbal title of the panel
   *
   * @return string $title
   */
  public function getPanelTitle()
  {
    return 'Database information';
  }

  /**
   * Get the html content of the panel
   *
   * @return string $html
   */
  public function getPanelContent()
  {
    return $this->getSqlLogs();
  }
  
  /**
   * Filter the logs to only include the entries from sfDoctrineLogger
   *
   * @param sfEvent $event
   * @param array $Logs
   * @return array $newLogs
   */
  public function filterLogs(sfEvent $event, $logs)
  {
   $newLogs = array();
   foreach ($logs as $log)
   {
     if (!$this->isDbLog($log))
     {
       $newLogs[] = $log;
     }
   }

   return $newLogs;
  }
  
  protected function isDbLog($log)
  {
    return 'sfDoctrineLogger' == $log['type'] || 'sfPropelLogger' == $log['type'];
  }

  /**
   * Build the sql logs and return them as an array
   *
   * @return array $newSqlogs
   */
  protected function getSqlLogs()
  {
    
    $logs = array();
    $bindings = array();
    $i = 0;
    foreach ($this->webDebug->getLogger()->getLogs() as $log)
    {
      if (!$this->isDbLog($log))
      {
        continue;
      }

      if (preg_match('/^.*?(\b(?:SELECT|INSERT|UPDATE|DELETE)\b.*)$/', $log['message'], $match))
      {
        $logs[$i++] = array('time' => '-',
                           'query' => $match[1]
                           );
        $bindings[$i - 1] = array();
      }
      else if (preg_match('/Binding (.*) at position (.+?) w\//', $log['message'], $match))
      {
        $bindings[$i - 1][] = $match[2].' = '.$match[1];
      }
    }

    foreach ($logs as $i => $log)
    {
      if (count($bindings[$i]))
      {
        $logs[$i]['query'] .= sprintf(' (%s)', implode(', ', $bindings[$i]));
      }
    }
    
    $sqlLogs[] = array('time' => isset($queryMatches[1]) ? $queryMatches[1] : '-',
                       'query' => isset($queryMatches[2]) ? $queryMatches[2] : '-'
                       );

    return $logs;
  }
}