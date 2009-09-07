<?php

class fsWebDebugPanelCache extends sfWebDebugPanelCache
{
  public static $cache_info = array();
  
  public function getTitle()
  {
    return 'cache';
  }

  public function getPanelTitle()
  {
    return 'reload and ignore cache';
  }

  public function getPanelContent()
  {
    $content = array();
        
    if(sfConfig::get('sf_debug') && sfConfig::get('sf_cache'))
    {
      $content['reload_url'] = $_SERVER['PHP_SELF'].((strpos($_SERVER['PHP_SELF'], '_sf_ignore_cache') === false) ? '?_sf_ignore_cache=1' : '');
      $content['cache_info'] = self::$cache_info;
    }
    else
    {
      $content['reload_url'] = '';
      $content['cache_info'] = array();
    }

    return $content;
  }
  
  public static function decorateContentWithDebug(sfEvent $event, $content)
  {
    // don't decorate if not html or if content is null
    if(!sfConfig::get('sf_web_debug') || !$content || false === strpos($event['response']->getContentType(), 'html'))
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
    
    self::$cache_info[] = $cache_data;

    return $content;
  }
}