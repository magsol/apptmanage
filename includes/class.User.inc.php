<?php

defined('IC') or die('Restricted access');

import('permissions');
import('utility');
import('session');
import('error');

/**
 * User class.  Manages users as they navigate and utilize the appointment
 * management system.
 * 
 * @package Introverted.Champions
 * @author Chris Gray
 * @version 1.7
 */

// ---------------------------------------------
// Revisions
// 0.5
// 0.6  - 7/8/08 by Shannon
//      - Removed permissions[] array; replaced with user class identifier
//      - User class identifier can be linked to set of permissions through
//        the Permissions class...
//      - Added getInstance()
// 0.7  - 7/8/08 by Shannon
//      - Added $session for session management
// 0.8  - 7/8/08 by Shannon
//      - Fixed bug in the constructor that nullified userid field
//      - Added modify() function
//      - Added changePassword() function
// 0.9  - 7/9/08 by Shannon
//      - Added $guest field
// 1.0  - 7/9/08 by Shannon
//      - Added isGuest() function
//      - Added getSession() function
// 1.1  - 7/16/08 by Shannon
//      - Changed constructor to coincide with new Session class
//      - Made modify() function static
// 1.2  - 7/19/08 by Shannon
//      - Made changePassword() function static
// 1.3  - Altered changePassword() function's reference to modify()
// 1.4  - 7/21/08 by Shannon
//      - Added canPerform()
//      - Added getUserClass()
//      - Deleted unused functions
// 1.5  - 7/21/08 by Robert
// 	    - added private variable $offset
//	    - added public accessor for $offset
// 1.6  - 7/22/08 by Shannon
//      - Improved documentation
//      - Fixed minor array bug in constructor
//      - Changed getOffset() to include check for DST
// 1.7  - 7/24/08 by Shannon
//      - Removed $offset attribute 
//      - Removed getOffset() method
//      - Modified constructor to bypass tz_offset field in database
//      - *Offset considerations have been effectively removed from the design*

class User {

  /***********************\
  |   Private Attributes  |
  \***********************/
  
  /**
   * Uniquely identifies the account of the user.
   * 
   * @access private
   * @var string $email handle for this user
   */
  private $userid;  
  
  /**
	 * This object coordinates the permissions for this User.
	 * 
	 * @access private
	 * @var object $permissions A Permissions object
	 */
  private $permissions;
  
  /**
   * This handles the user's session.
   *
   * @access private
   * @var object $session A Session object
   */
  private $session;
  
  /**
   * Indicates if the active user is a guest, unauthenticated with the system.
   *
   * @access private
   * @var bool True if the user is a guest, false otherwise
   */
  private $guest = false;
  
  /**
   * This is the default permissions level of an authenticated user in 
   * the system, corresponding to the values in the database.
   *
   * @static
   * @access public
   * @var string $defaultClass A string indicating the default permissions level.
   */
  static $defaultClass = 'client';
  
  /***********************\
  |      Constructor      |
  \***********************/
  
  /**
   * Constructor, private so only one user is instantiated
   * 
   * @access private
   * @param string $email The email address corresponding to the user in the database.
   *        If this is set to null, the User is assumed to be a guest.
   */
  private function __construct($email) {
    if ($email) { // this is a registered member
      $this->userid = Utility::cleanDatabase($email);
      $this->session = new Session($this->userid);
    } else { // there may be a session cookie we can retrieve
      $this->session = new Session();
      $this->userid = $this->session->getUserID();
    }
    
    // now we test and see if the user had a valid session, or is in fact
    // just an unregistered guest on the website
    if ($this->userid != 'guest') { // valid session
      $db = Factory::getDB();
      $db->setQuery('SELECT userclass FROM user WHERE email = "' . 
                    $this->userid . '"');
      $class = $db->loadRow();
      $this->permissions = new Permissions($class['userclass']);
      $this->guest = false;
    } else { // unregistered guest
      $this->guest = true;
      $this->permissions = new Permissions('guest');
    }
  }
  
  /***********************\
  |    Static Functions   |
  \***********************/
  
  /**
   * Static factory method to obtain an instance of the User.
   * 
   * @static
   * @access public
   * @param string $email The email address of the user, corresponding to the database
   * @return object A new instance of a User.
   */
  public static function getInstance($email = null) {
    return new User($email);
  }
  
  /**
   * Performs the checks to determine if the user who provided the email
   * and password credentials is a valid and registered user with the system.
   * If the username and password (MD5 hashed within the function) match
   * a given email/passhash combination in the database, a valid user is 
   * noted.
   *
   * @static
   * @access public
   * @param string $email The email address provided
   * @param string $password The password provided (plaintext)
   * @return bool True if the user is valid, false otherwise
   */
  public static function checkLogin($email, $password) {
    // filtering
    $password = md5($password);
    $db = Factory::getDB();
    $email = Utility::cleanDatabase($email);
    
    // perform the checks
    $db->setQuery('SELECT userid FROM user WHERE email = "' . $email . 
                  '" AND password = "' . $password . '"');
    $result = $db->loadRow();
    
    // based on whether or not we have a return value...
    return isset($result['userid']);
  }
  
  /**
   * Updates one of the user attributes in the database.  String munging is
   * performed on the attribute name in order to reduce the possibility of
   * small syntactic differences preventing a match between the parameter
   * and the actual database column name.
   * 
   * Regular expressions are used to strip all characters other than [a-zA-Z0-9]
   * from both the column names and the attribute name.  Any match is then used
   * to update.  If no match is found, no update is performed.
   *
   * @static
   * @access public
   * @param string $attrName The name of the field in the database to change.
   * @param mixed $attrValue The new value of that field.
   * @param string $email Email address of the user to modify.
   * @return bool True if the update was successful, false otherwise.
   */
  public static function modify($attrName, $attrValue, $email) {
    $attrName = Utility::simplifyString($attrName, true);
    $db = Factory::getDB();
    $db->setQuery('DESCRIBE user');
    $results = $db->loadRows();
    $numResults = count($results);
    $found = -1;
    for ($i = 0; $i < $numResults; $i++) {
      if ($attrName == Utility::simplifyString($results[$i]['Field'], true)) {
        $found = $i;
        break;
      }
    }
    
    // have we found what we're looking for?
    if ($found < 0) { // no dice
      $cfg = Factory::getConfig();
      if ($cfg->getValue('debug')) { // debug mode is ON...make some output 
        Error::errorMsg('Warning: Improper use of \'modify()\': attribute ' . 
                        "'" . $attrName . "', value '" . $attrValue . "', and " .
                        "user '" . $email . "'.");
      }
      return false;
    }
    // we got it...run the update statement
    $db->setQuery('UPDATE user SET ' . $results[$found]['Field'] . ' = "' . 
                  Utility::cleanDatabase($attrValue) . '" WHERE email = "' .
                  $email . '"');
    // woot
    return $db->query();
  }
  
  /**
   * Since the passwords must be one-way encrypted before being stored in the
   * database, the modify() function is insufficient.  This function takes
   * a new password and stores it in the database.
   *
   * @access public
   * @param string $newPass The new password.
   * @return bool True on success, false on failure.
   */
  public static function changePassword($newPass, $email) {
    return self::modify('password', md5($newPass), $email);
  }
  
  /***********************\
  |    Public Functions   |
  \***********************/
  
  /**
   * This tests whether or not this user can perform a particular action.
   * 
   * @param string $action The action to be tested.
   * @return bool True if the user has this permission, false otherwise.
   */
  public function canPerform($action) {
    return $this->permissions->canPerform($action);
  }
  
  /**
   * Retrieves and returns the user permissions class, as specified by the database.
   * 
   * @access public
   * @return string The name of the user's permissions class.
   */
  public function getUserClass() {
    return $this->permissions->getClassName();
  }
  
  /**
   * Accessor for the $userid field.
   *
   * @access public
   * @return string The unique user id of the registered user, or 'guest'
   */
  public function getUserID() {
    return $this->userid;
  }
  
  /**
   * Determines whether or not this user is a guest, aka unauthenticated.
   *
   * @access public
   * @return bool True if the user is a guest, false if they are a registered user
   */
  public function isGuest() {
    return $this->guest;
  }
  
  /**
   * Accessor for the user's active session.
   *
   * @access public
   * @return object The session object correlated with this user.
   */
  public function getSession() {
    return $this->session;
  }
  
}

?>
