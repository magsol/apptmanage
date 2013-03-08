<?php

defined('IC') or die('Restricted access');

/**
 * This class helps manage the various user classes of the appointment
 * management system.  This is one of the few classes that is *not* 
 * restricted by the singleton system; generally speaking, there will only
 * be one Permissions object per User object, though there will only be
 * one User object.  Hooray for no redundancy!
 *
 * Created on Jul 8, 2008
 *
 * @package Introverted.Champions
 * @author Shannon Quinn
 * @version 0.1
 */

// ---------------------------------------------
// Revisions
// 0.1  - 7/8/08
//      - Begun
//      - Added constructor
//      - Added private fields
//      - Added canPerform()
//      - Added documentation
// 0.2  - 7/21/08
//      - Added getClassName()

class Permissions {
  
  /***********************\
  |   Private Attributes  |
  \***********************/

  /**
   * This is the name of the user class.
   *
   * @access private
   * @var string $className
   */
  private $className;
  
  /**
   * Array of possible actions, and corresponding values to indicate
   * whether this particular instance is allowed to perform the specified
   * actions.
   * 
   * The keys are the actions (string), and the values are 1s or 0s (booleans),
   * indicating whether or not that action can be performed.
   *
   * @access private
   * @var array $permissions
   */
  private $permissions;
 
  /***********************\
  |    Public Functions   |
  \***********************/
  
  /**
   * Constructor. This hits the database in order to determine the possible
   * actions for this object.
   *
   * @access public
   * @param string $class
   */
  public function __construct($class) {
    // grab the database
    $db = Factory::getDB();
    
    // query it
    $db->setQuery('SELECT * FROM user_class WHERE class = "' . 
                  Utility::cleanDatabase($class) . '"');
    $tmp = $db->loadRow();
    $this->className = $class;
    $this->permissions = array();
    // loop through the returned actions and whether or not this class
    // level allows them, and stick those in the permissions array as
    // key/value pairs
    foreach ($tmp as $key => $value) {
      $this->permissions[strtoupper($key)] = ($value == 0 ? false : true);
    }
  }
  
  /**
   * Determines whether or not this Permissions object is configured to allow
   * the action specified in the parameter.  Note: the specified action must
   * correspond exactly with the column name in the database.  Case sensitivity
   * does not matter, but spelling and general syntax is crucial.
   * 
   * canlogin == CANLOGIN
   * can_login != canLogin
   *
   * @access public
   * @param string $action The action to be performed.
   * @return True if the action is allowed, false otherwise.
   */
  public function canPerform($action) {
    $action = strtoupper($action);
    return isset($this->permissions[$action]) && $this->permissions[$action];
  }
  
  /**
   * Accessor method for the className field.
   *
   * @access public
   * @return string The name of the user class for this user.
   */
  public function getClassName() {
    return $this->className;
  }
}

?>