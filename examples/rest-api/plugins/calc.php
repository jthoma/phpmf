<?php 

/**
 * class gproc
 * 
 */

class calc
{
  public function sumOf(){
    $json = MF::object("bodyparser")->getjson();
    $out = ["success" => true, "res" => (float) $json['a'] + (float) $json['b']];
    echo json_encode($out);
    exit();
  }

  public function diffOf(){
    $out = ["success" => true, "res" => (float) $_GET['a'] - (float) $_GET['b']];
    echo json_encode($out);
    exit();
  }
} 