<?php

class fsNoJsonException extends sfException
{
  public function __construct($message = null, $code = 0)
  {
    $this->setName('fsNoJsonException');
    parent::__construct($message, $code);
  }
}