#!/usr/bin/php -q
<?php

/** 
 * Command line helper to initialize things
 */

if (count($argv) < 3 || $argv[1] === 'new' && count($argv) < 4) {
  usage();
}

define('CWD', getcwd());

switch ($argv[1]) {
  case 'init':
    doInit($argv[2]);
    break;

  case 'new':
    switch ($argv[2]) {
      case 'addon':
        doNew('addons', ':fn_name:', $argv[3]);
        break;

      case 'plugin':
        doNew('plugins', ':pl_name:', $argv[3]);
        break;

      case 'view':
        doNew('views', ':v_name:', $argv[3]);
        break;

      default:
        usage();
        break;
    }
    break;

  default:
    usage();
    break;
}


# functions

function usage()
{
?>
  usage: mf [action] [options]
  init description - Initialize new project, will attempt to create basic configurations and the sort
  new [addon, plugin] [name] - will create corresponding item after finding correct path
  action new will work only in an initialized folder
<?php
  exit();
}

function doInit($description)
{
  if (file_exists(CWD . '/MF.php')) {
    echo "Already initialized";
    exit();
  }
  $o = [];
  $r = 0;
  exec('wget "https://raw.githubusercontent.com/jthoma/phpmf/master/src/MF.php" -O "' . CWD . '/MF.php" ', $o, $r);
  if (!file_exists(CWD . '/.htaccess')) {
    exec('wget "https://raw.githubusercontent.com/jthoma/phpmf/master/examples/rewrites/apache2.txt" -O "' . CWD . '/.htaccess" ', $o, $r);
  }

  if (!is_dir(CWD . '/addons')) {
    mkdir(CWD . '/addons');
  }
  if (!is_dir(CWD . '/plugins')) {
    mkdir(CWD . '/plugins');
  }
  if (!is_dir(CWD . '/views')) {
    mkdir(CWD . '/views');
  }

  // save params for future
  $p = serialize([':description:' => $description, 'ts' => time()]);
  file_put_contents(CWD . '/.mf-env', $p);

  $index = parse_tpl('index', [':description:' => $description]);
  file_put_contents(CWD . '/index.php', $index);

  // create the 404.php
  $view = parse_tpl('views', [':v_name:' => '404 Template', ':description:' => $description]);
  file_put_contents(CWD . '/views/404.php', $view);

  // create the setting.php
  doNew('addons',':fn_name:','settings');
}

function doNew($item, $nametag, $name)
{
  is_php_MF();
  $p = get_config([$nametag => $name]);
  $content = parse_tpl($item, $p);
  $path = CWD . '/' . MF::get($item) . $name . '.php';
  file_put_contents($path, $content);
}

function is_php_MF()
{
  if (!file_exists(CWD . '/MF.php')) {
    usage();
  }
  require_once CWD . "/MF.php";
}

function parse_tpl($id, $vars)
{
  $t = [
    'index' => 'PD9waHAgCgovKioKICogOmRlc2NyaXB0aW9uOgogKi8KCnJlcXVpcmUgIi4vTUYucGhwIjsKCi8vIHVyaWxtYXNrIGlzIGV4cGVjdGVkIHRvIGhhdmUgdGhlIHN1YiBkaXJlY3RvcnkgZnJvbSAKLy8gYXBwbGljYXRpb24gc2VydmVyIGRvY3Jvb3QgCk1GOjpzZXQoJ3VyaW1hc2snICwnL2NpLWFwcCcpOwoKLy8gZnVydGhlciBjb25maWd1cmF0aW9uIApNRjo6YWRkb24oJ3NldHRpbmdzJyk7ICAKCi8vIEFsbCByb3V0aW5nIHRvIGJlIHdyaXR0ZW4gaGVyZSAKCi8vIFJlc3QgaXMgc3RhbmRhcmQKCi8vIEFzc3VtaW5nIGEgd2ViIHBhZ2UgYXBwbGljYXRpb24KLy8gQnV0IGlmIHlvdSBhcmUgZGV2ZWxvcGluZyBBUEkgZmlyc3QKLy8gQmV0dGVyIGNoYW5nZSB0aGlzIHRvICgnJywgNDAzKTsgCk1GOjpydW4oJzQwNCcsIDQwNCk7Cgo=',
    'addons' => 'PD9waHAKCi8qKgogKiBAcGFja2FnZSBNYXJpYUZyYW1ld29yawogKiBAc3VicGFja2FnZSA6Zm5fbmFtZTogYWRkb24KICogQHByb2plY3QgOmRlc2NyaXB0aW9uOgogKi8KCmZ1bmN0aW9uIDpmbl9uYW1lOigpewoKCn0K',
    'plugins' => 'PD9waHAKCi8qKgogKiBAcGFja2FnZSBNYXJpYUZyYW1ld29yawogKiBAc3VicGFja2FnZSA6cGxfbmFtZTogcGx1Z2luCiAqIEBkZXNjcmlwdGlvbiA6ZGVzY3JpcHRpb246CiAqLwoKY2xhc3MgOnBsX25hbWU6CnsKCn0K',
    'views' => 'PD9waHAKCi8qKgogKiBAcGFja2FnZSBNYXJpYUZyYW1ld29yawogKiBAc3VicGFja2FnZSA6dl9uYW1lOiB2aWV3CiAqIEBwcm9qZWN0IDpkZXNjcmlwdGlvbjoKICovCgovLyBodHRwczovL2dpdGh1Yi5jb20vanRob21hL3BocG1mI21mZGlzcGxheQoKPz4K'
  ];
  $txt = base64_decode($t[$id]);
  return str_replace(array_keys($vars), array_values($vars), $txt);
}

function get_config($v)
{
  $cfg = unserialize(file_get_contents(CWD . '/.mf-env'));
  return array_merge($cfg, $v);
}

