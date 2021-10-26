<?php 

/**
 * helloworld
 */

require "./MF.php";

// urilmask is expected to have the sub directory from 
// application server docroot 
MF::set('urimask' ,'');

// further configuration 
MF::addon('settings');  
MF::addon('lib');

// All routing to be written here 
MF::MFR('POST /signup', 'user','signup');
MF::MFR('POST /validate', 'user','validate');
MF::MFR('POST /resendotp', 'user','resendotp');
MF::MFR('POST /checkdup', 'user','checkdup');

MF::MFR('GET /info', 'info', 'index');
// Rest is standard

// Assuming a web page application
// But if you are developing API first
// Better change this to ('', 403); 
MF::run('404', 404);

