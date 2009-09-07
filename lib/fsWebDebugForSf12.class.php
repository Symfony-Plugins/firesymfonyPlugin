<?php

class fsWebDebugForSf12 extends sfWebDebug {
  /**
   * Configures the web debug toolbar.
   */
  public function configure()
  { 
    $this->setPanel('cache', new fsWebDebugPanelCache($this));
    
    $this->setPanel('config', new fsWebDebugPanelConfig($this));
    $this->setPanel('logs', new fsWebDebugPanelLogs($this));

    $this->setPanel('memory', new fsWebDebugPanelMemory($this));
    
    $this->setPanel('database', new fsWebDebugPanelDatabase($this));
    
    $this->setPanel('timer', new fsWebDebugPanelTimer($this));    
    $this->setPanel('info', new fsWebDebugPanelInfo($this));
  }
  
  /**
   * Injects the web debug toolbar into a given HTML string.
   *
   * @param string  $content The HTML content
   *
   * @return string The content with the web debug toolbar injected
   */
  public function injectToolbar($content)
  {
    $debug = $this->asJSON();

    $content = str_ireplace('</body>', "<script type=\"text/javascript\"> //<![CDATA[ \n FireSymfonyDebugData = ".$debug." \n //]]></script></body>", $content);
    $content = str_ireplace('</script></body>', '</script><script type="text/javascript">'.$this->getJavascript().'</script></body>', $content);

    return $content;
  }
  
  public function asJSON()
  {
    $panels = array();
    foreach ($this->panels as $name => $panel)
    {
      if ($title = $panel->getTitle())
      {
        $panels[$title] = array(
                            'title'=>$panel->getPanelTitle(), 
                            'content'=>$panel->getPanelContent()
                           );
      }
    }
    if(function_exists('json_encode'))
    {
      return json_encode($panels);
    }
    else
    {
      throw new fsNoJsonException("To use FireSymfony you need to enable the json php extension.");
    }
  }
}

?>