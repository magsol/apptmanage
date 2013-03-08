<?php

/**
 * @package Introverted.Champions
 * @author Shannon Quinn
 * @version 1.1
 */

/******************************************\
* Created on Jul 8, 2008                   *
********************************************
* Here is our configuration script for     *
* getting our application rolling.  It     *
* does some menial but very important      *
* stuff, like assigning include paths,     *
* instantiating any necessary objects, and *
* generally making a wonderous mess of     *
* things :)                                *
\******************************************/

// uber important...comment out or remove this line and you singlehandedly
// break the entire application.  is the power overwhelming you yet?
define('IC', 1);

// include the rest of the site's configuration...if you want to alter
// how the site operates, you'll want to edit the file below
// OLOL YOU THOUGHT THE SITE'S CONFIGURATION OPTIONS WERE IN THIS FILE
require_once('includes/class.Config.inc.php');

// is this site even up and running?
$cfg = Factory::getConfig();
if ($cfg->getValue('offline')) {
  die($cfg->getValue('offline_msg'));
}

// are we in debug mode?
if ($cfg->getValue('debug')) {
  // this is the FirePHP Firefox3 extension.  This output can only be
  // viewed if this extension is installed.  Further instructions can 
  // be found here: http://www.firephp.org/Wiki/Main/QuickStart
  @include_once('FirePHPCore/fb.php');
}

// just a constant that's helpful
include_once('constants.php');

/**
 * This is a nifty include function that'll come in uber handy.  Use this
 * instead of the standard 'require' or 'include' stuffs if you're looking
 * to grab a particular class.  Now all you have to do is specify the
 * class by its name, case-insensitive, and this will include it.
 *
 * @param string $classname The name of the class.
 * @return bool True on success, false on failure.
 */
function import($classname) {
  $cfg = Config::getInstance();
  $files = scandir($cfg->getValue('abs_path') . $cfg->getValue('incl_path'));
  foreach ($files as $file) {
    $parts = explode('.', $file);
    if (strtolower($parts[1]) == strtolower($classname)) {
      include_once($cfg->getValue('abs_path') . $cfg->getValue('incl_path') . $file);
      return true;
    }
  }
  return false;
}
?>
