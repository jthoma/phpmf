<?php

/**
 * @package MariaFramework
 * @subpackage settings addon
 * @project helloworld
 */

require '/opt/php-aws-sdk/aws-autoloader.php';

function settings(){
  define('ddb_table', 'etokensas');
  date_default_timezone_set('UTC');
  !defined('IDNA_DEFAULT') && define('IDNA_DEFAULT', null); // workaround for php_intl not available

}
