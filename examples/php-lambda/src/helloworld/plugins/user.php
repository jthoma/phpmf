<?php

/**
 * @package MariaFramework
 * @subpackage user plugin
 * @description helloworld
 */

class user
{

  public function signup(){
    $payload = MF::object("bodyparser")->getjson();
    $payload['otp'] = rand(111111,999999);
    $payload['pk'] = $payload['mobile'];
    unset($payload['mobile']);
    $payload['type'] = 'user';
    $payload['role'] = 'user';
    $payload['Active'] = 'N';
    $payload['Approved'] = 'N';
    $success = MF::object("ddbwrapper")->create($payload);
    out_with_json(['success' => $success, 'otp' => $payload['otp']]);
  }

  public function validate(){

  }

  public function resendotp(){

  }

  public function checkdup(){

  }

}
