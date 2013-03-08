<?php

defined('IC') or die('Restricted access');

import('dbconn');
import('factory');

/**
 * This file's purpose is simply to store any information pertinent to the 
 * global configuration settings of the application.  No object instantiations 
 * are made here; only setting up the parameters for those instantiations.
 * 
 * @package Introverted.Champions
 * @author Shannon Quinn
 * @version 0.5
 */

// ---------------------------------------------
// Revisions
// 0.1  - 6/25/08
//      - Basic config.inc.php file completed.
// 0.2  - Changed to Config class structure.
// 0.3  - Added documentation to methods
//      - added getValue()
//      - added getInstance()
//      - TODO: Document attributes
// 0.4  - 7/28/08
//      - Added fireDebugOutput() function
// 0.5  - Edited fireDebugOutput() to utilize FirePHP::TABLE output

class Config {

  /***********************\
  |   Private Attributes  |
  \***********************/

  // For the sake of clarity...I am forgoing official PHPDoc style guidelines
  // and providing a single-line description of each field.  This will
  // make the file at large more readable and, most importantly, more editable.
  
  // GLOBAL WEBSITE CONFIGURATION
  private $sitename = 'Introverted Champions';
  private $offline = false;
  private $offline_msg = 'This site is down for maintenance.';
  private $ssl_enabled = false;
  private $debug = true;
  private $email = 'do-not-reply@dms489.com';
  
  // DATABASE PARAMETERS
  private $dbuser = 'cs4911user';
  private $dbpass = 'P2Vjdc.8v,f7SpX9';
  private $dbname = 'dms489co_cs4911';
  //private $dbhost = 'dms489co.startlogicmysql.com';
  private $dbhost = '127.0.0.1';
  //private $dbprefix = 'ic_';
  
  // FILESYSTEM PATHS
  //private $abs_path = '/hermes/bosweb/web183/b1831/sl.dms489co/public_html/cs4911/';
  private $abs_path = '/Users/V2/Sites/cs4911/trunk/';
  //private $rel_path = 'cs4911/';
  private $rel_path = 'cs4911/trunk/';
  //private $abs_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $rel_path;
  private $incl_path = 'includes/';
  private $smrty_path = 'includes/smarty/';
  private $smrty_tmpl = 'default';
  
  // OTHER
  private $secret_key = "this is CS4911's encryption key, k+h><B41";

  /***********************\
  |       Functions       |
  \***********************/
  
  /**
   * Constructor.
   * 
   * @access private
   */
  private function __construct() {
    // yeah nothing much happens here
  }
  
  /**
   * Accessor method by which the Factory class generates instances.
   * 
   * @static
   * @access public
   * @return object An instance of a Config object.
   */
  public static function getInstance() {
    return new Config();
  }
  
  /**
   * General accessor for configuration settings.
   * 
   * @access public
   * @param string $var The name of the configuration variable.
   * @param mixed $default The default return value.
   * @return mixed The value of $var, or $default if not set.
   */
  public function getValue($var, $default = null) {
    if (isset($this->$var)) {
      // if this variable exists, return its value
      return $this->$var;
    }
    
    // return the default if the specified variable does not exist
    return $default;
  }
  
  /**
   * This is a wrapper for the FirePHP debugger function, fb().
   * 
   * It prints everything to the console, performing all checks necessary
   * to ensure that the FirePHP extension is installed, that the function
   * is callable, and that debug mode is on.
   *
   * @access public
   * @param string $title A description of the variable to be printed in the console.
   * @param mixed $variable The value of the variable to be printed.
   */
  public function fireDebugOutput($title, $variable) {
    // make sure debug mode is on, and make sure the FirePHP extension
    // is installed and ready
    if ($this->getValue('debug') && is_callable('fb')) {
      ob_start(); // buffer output
      if (is_array($variable) && isset($variable[0]) && is_array($variable[0])) {
        fb(array($title, $variable), FirePHP::TABLE);
      } else {
        fb($variable, $title, FirePHP::LOG);
      }
    }
  }

}

?>
