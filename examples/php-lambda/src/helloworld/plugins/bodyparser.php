<?php

/**
 * @package MariaFramework
 * @subpackage body parser plugin
 *
 * @author: github.com/jthoma
 */

class bodyparser
{
  /**
   * 
   */
  public function getjson($array = true){
    if($_SERVER['CONTENT_TYPE'] === 'application/json'){
      $body = file_get_contents("php://input");
      return json_decode($body, $array);
    }
  }
}