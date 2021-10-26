<?php 

/**
 * @package MariaFramework
 * @subpackage lib functions
 * @project helloworld
 */

function lib(){

}


function out_with_json($out){
  header("Content-Type: application/json");
  echo json_encode($out);
}

class console {
  public static function log($message){
    echo $message;
    // file_put_contents('php://stderr', $message . "\n");
  }
}
