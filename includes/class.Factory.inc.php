<?php

defined('IC') or die('Restricted access');

/**
 * Base class from which singleton instances of classes can be created, 
 * ensuring no more than one instance of a class exists at any given point.
 * 
 * Created on Jun 24, 2008, 2008  
 * 
 * @package Introverted.Champions
 * @author Shannon Quinn
 * @version 0.3
 */

// ---------------------------------------------
// Revisions
// 0.1  - 6/25/08
//      - Added getConfig()
//      - Added getSmarty()
//      - Added getDB()
//      - Added documentation
// 0.2  - 7/2/08
//      - Added getCookie()
// 0.3  - 7/8/08
//      - Deleted getCookie()
//      - Added getUser()
// 0.4  - 7/9/08
//      - Modified getUser() to also return a new instance if the
//        old instance was that of a guest, and the request one was
//        for a legit user
// 0.5  - 7/16/08
//      - Added getSession() method
// 0.6  - 7/16/08
//      - Removed getSession() method

class Factory {

  /***********************\
  |       Functions       |
  \***********************/

  /**
   * Factory method for the Config class.
   * 
   * @static
   * @access public
   * @staticvar $instance
   * @return object An instance of the Config class.
   */
  public static function getConfig() {
    static $instance;
    if (!is_object($instance)) {
      $instance = Config::getInstance();
    }
    return $instance;
  }
  
  /**
   * Factory method for the Smarty template engine.
   * 
   * @static
   * @access public
   * @staticvar $instance
   * @return object An instance of the Smarty template.
   */
  public static function getSmarty() {
    static $instance;
    if (!is_object($instance)) {
      // first, get the Config, so we know the paths
      $cfg = Factory::getConfig();
      $smarty_path = $cfg->getValue('abs_path') . $cfg->getValue('smrty_path');
      
      // include the Smarty library
      include_once($smarty_path . 'Smarty.class.php');
      
      // now instantiate the template object
      $instance = new Smarty();
      $instance->template_dir = $smarty_path . 'templates/' . $cfg->getValue('smrty_tmpl');
      $instance->compile_dir  = $smarty_path . 'templates_c';
      $instance->cache_dir    = $smarty_path . 'cache';
      $instance->config_dir   = $smarty_path . 'configs';
    }
    
    // return the smarty object
    return $instance;
  }
  
  /**
   * Factory method for the database.
   * 
   * @static
   * @access public
   * @staticvar $instance
   * @return object An instance of the DBConn database connection.
   */
  public static function getDB() {
    static $instance;
    if (!is_object($instance)) {
      $instance = DBConn::getInstance();
    }
    return $instance;
  }

  /**
   * Factory method for the User.
   * 
   * @static
   * @access public
   * @staticvar $instance
   * @param string $email The user's email address
   * @return object An instance of the User.
   */
  public static function getUser($email = null) {
    static $instance;
    if (!is_object($instance) || ($instance->isGuest() && $email)) {
      $instance = User::getInstance($email);
    }
    return $instance;
  }
}

?>