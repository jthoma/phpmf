<?php

/**
 * @package MariaFramework
 * @subpackage BenchMarkAddon
 *
 * @author: github.com/jthoma
 */

/**
 * Tries to capture a benchmark by detecting the number of queries and time taken
 * and the peak memory usage. This is automatically injected with the addaction 
 * triggers to before and after handler hooks.
 * @param String $arg
 */
 
function benchmark($arg = 'action=start'){
   $res = array();
   parse_str($arg, $res); 
   extract($res);
   $rv = "";
   if($action == 'start'){
     return MF::set('benchmark_START', microtime(true));
   }elseif($action == 'stop'){
     $start = MF::get('benchmark_START');
     if(!empty($start)){
        $rv = microtime(true) - $start;
        $cc = MF::get('countqueries');
        $cc = ($cc !== '')?', running '.$cc.' queries':'';
        if(!empty($gethtml)){
            $rv = ' generated in : ' . sprintf("%0.4f",$rv) . ' seconds'.$cc;
        }
        if(!empty($getTxt)){
            $rv = '<span class="bc">generated in : ' . sprintf("%0.4f",$rv) . ' seconds'.$cc.'</span>';
        }
     }
   }
   return $rv;
}

/**
 * @param int $bytes Number of bytes (eg. 25907)
 * @param int $precision [optional] Number of digits after the decimal point (eg. 1)
 * @return string Value converted with unit (eg. 25.3KB)
 */
function formatMem($bytes, $precision = 2) {
  $unit = ["B", "KB", "MB", "GB"];
  $exp = floor(log($bytes, 1024)) | 0;
  return round($bytes / (pow(1024, $exp)), $precision).$unit[$exp];
}

function memory_used(){
  $rv = "";
  $mp = memory_get_peak_usage();
  $rv = 'peak memory usage : ' . formatMem($mp);
  return $rv;
}

function benchmarkStart(){
  benchmark('action=start');
}

function benchmarkStop(){
  echo "\n\n\n".'<!-- ' . "\n";
  echo memory_used() . "\n";
  echo benchmark('action=stop&gethtml=1') . "\n";
  echo '-->' . "\n";
}

function apm(){
  MF::addaction('before-handler','benchmarkStart');
  MF::addaction('after-handler','benchmarkStop');
}