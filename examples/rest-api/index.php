<?php 

/**
 * Rest API Example
 */

require "./MF.php";

// urilmask is expected to have the sub directory from 
// application server docroot 
MF::set('urimask' ,'/api');

// just source the file. 
// one could use require or include but this is just a fancy
MF::addon('libFunctions');  

// hook this function defined in the addons/libFunctions.php 
// to the event before-handler ie, the function will be called
// when ever a route is identified and will execute only for 
// defined routes
MF::addaction('before-handler','jsonContentHeader');

// HTTP Method PUT and path /add will be handled by plugins/calc.php -> sumOf()
MF::MFR('PUT /add', 'calc', 'sumOf');

// HTTP Method POST and path /add will be handled by plugins/calc.php -> sumOf()
MF::MFR('POST /add', 'calc', 'sumOf');

// HTTP Method GET and path /sub will be handled by plugins/calc.php -> diffOf()
MF::MFR('GET /sub', 'calc', 'diffOf');

// HTTP Method DELETE and path /item/* will be handled by plugins/gproc.php -> remove()
MF::MFR('DELETE /item/(.+)', 'gproc', 'remove');

// if request is undefined, just output unauthorized
// since we are hanlding an api
MF::run('', 403);
