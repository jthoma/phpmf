<?php

/**
 * @package MariaFramework
 * @description mf_portal
 * @author Jiju Thomas Mathew
 */

 function themehelper($part, $vars = array()){
   MF::display('header', $vars);
   MF::display($part, $vars);
   MF::display('footer', $vars);
 }