<?php 

/**
 * class gproc
 * 
 */

class gproc
{
  public function remove($p){
    if(!isset($p[1]) || !is_numeric($p[1])){
      $out = ["success" => false, "message" => "Item to delete not specified"];      
    }else{
      $out = ["success" => true, "message" => "Delete item with ID: " . (int) $p[1]];      
    }
    echo json_encode($out);
    exit();
  }
} 