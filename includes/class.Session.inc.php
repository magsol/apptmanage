<?php

defined('IC') or die('Restricted access');

import('authexception');
import('config');
import('utility');
import('error');

/**
 * This class encapsulates the session management for a user logged into
 * the appointment management system.  It takes care of setting cookies
 * and ensuring that authentication has proceeded as expected.  This class
 * acts as a replacement and integration for both the previous Session class 
 * and the Cookie class.
 *
 * Created on Jul 14, 2008
 *
 * @package Introverted.Champions
 * @author Shannon Quinn
 * @version 1.2
 */

// ---------------------------------------------
// Revisions
// 0.1  - 7/14/08
//      - Begun
// 0.2  - Added attributes, private and static
// 0.3  - Added public functions
// 0.4  - Added private functions
// 0.5  - Documentation for attributes completed
// 0.6  - 7/15/08
//      - Added encryption handle
//      - Added destructor for closing encryption handle
//      - Began constructor
// 0.7  - Added _encrypt(), _decrypt() wrappers
// 0.9  - Completed constructor
//      - Added _setCookie()
//      - Completed _validate()
//      - Completed _start()
//      - Completed _reissue()
//      - Completed restart()
//      - Completed destroy()
//      - Completed setVar()
//      - Completed getVar()
//      - Finished documentation
// 1.0  - Tested lightly; seems to be functioning properly
// 1.1  - 7/16/08
//      - Made constructor private
//      - Added public static getInstance() method to enforce singleton status
// 1.2  - 7/16/08
//      - Removed factory method and made constructor public again

class Session {
  
  /***********************\
  |   Private Attributes  |
  \***********************/
  
  /**
   * This tracks the current state of the session.
   * 
   * Possible values: 'active' | 'expired' | 'destroyed'
   *
   * @access private
   * @var string $state
   */
  private $state;
  
  /**
   * The unique user ID of the user who established this session.
   *
   * @access private
   * @var string $userid
   */
  private $userid;
  
  /**
   * The timestamp of the time this session began (call to time()).
   *
   * @access private
   * @var int $created
   */
  private $created;
  
  /**
   * The encryption handle for encrypting and decrypting the contents of the
   * cookie.  Returned by mcrypt_module_open()
   *
   * @access private
   * @var resource $encryption
   */
  private $encryption;
  
  /***********************\
  |   Static Attributes   |
  \***********************/
  
  /**
   * This is the name that will appear for the cookie within the web browser,
   * and how it will be accessed through the $_COOKIE array.
   * 
   * @static
   * @access public
   * @var string $cookiename
   */
  static $cookiename = 'introvertedchampions';
  
  /**
   * The "glue" which splices together strings to be stored in the cookie.
   *
   * @static
   * @access public
   * @var string $glue
   */
  static $glue = "|";
  
  /**
   * This is the amount of time (in seconds) that will elapse before the
   * cookie will be replaced, as it is approaching its expiration time.
   *
   * @static
   * @access public
   * @var string $warning
   */
  static $warning = '300';
  
  /**
   * After this amount of time (in seconds), the cookie will have expired and 
   * the session will no longer exist and must be recreated manually by the user.
   * 
   * @static
   * @access public
   * @var string $expire
   */
  static $expire = '600';
  
  /***********************\
  |    Public Functions   |
  \***********************/

  /**
   * Constructor, should only be called by the User class.
   *
   * @access public
   * @param string $userid The user ID, or email address, of the user owning this session.
   */
  public function __construct($userid = null) {
    // open up the encryption handle
    $this->encryption = mcrypt_module_open('blowfish', '', 'cfb', '');
    $this->_start();
    
    // was a userid passed in to create this session?
    if ($userid) {
      $this->userid = $userid;
      $this->_setCookie();
    } else {
      // two possibilities - did a session exist prior to now?
      // or is this an unauthenticated guest?
      if (isset($_COOKIE[self::$cookiename])) {
        // a prior session exists
        $this->_unpackage($_COOKIE[self::$cookiename]);
      } else {
        // unauthenticated...start as a guest
        $this->userid = 'guest';
        $this->_setCookie();
      }
    }
  }
  
  /**
   * An explicit destructor, so that the resources of the encryption
   * handle are definitively freed.
   *
   * @access public
   */
  public function __destruct() {
    mcrypt_module_close($this->encryption);
  }
  
  /**
   * Returns a variable set within the $_SESSION superglobal, or null if
   * the session is inactive or the requested variable is not set.
   *
   * @access public
   * @param string $name The name of the variable.
   * @return string The value of the requested variable.
   */
  public function getVar($name) {
    // first, make sure this is an active session
    if ($this->state != 'active') {
      return null;
    }
    
    // now, return the variable requested, or null if it's unset
    if (isset($_SESSION[$name])) {
      return $_SESSION[$name];
    }
    return null;
  }
  
  /**
   * This function sets a variable in the $_SESSION superglobal to the 
   * specified value, returning any old value which may have occupied that
   * location, or null if the spot was unset prior to this function being
   * called.  Furthermore, if the value to be set is null, then that place
   * in the $_SESSION array is simply unset.
   *
   * @access public
   * @param string $name The name of the variable in the array.
   * @param string $value The value of the variable.
   * @return string The value of the previous variable.
   */
  public function setVar($name, $value) {
    // first, make sure this session is active
    if ($this->state != 'active') {
      return null;
    }
    
    // pull out the old value, if it existed
    $old = (isset($_SESSION[$name]) ? $_SESSION[$name] : null);
    if ($value === null) { // simply delete the value
      unset($_SESSION[$name]);
    } else { // stick the new value in the array
      $_SESSION[$name] = $value;
    }
    return $old;
  }
  
  /**
   * Accessor for the userid field.
   *
   * @access public
   * @return string The ID of the user who started this session.
   */
  public function getUserID() {
    return $this->userid;
  }
  
  /**
   * Restarts a session.  All session variables are cleared and the session
   * restarted.
   *
   * @access public
   */
  public function restart() {
    $this->destroy();
    $this->state = 'restart';
    $this->_start();
  }
  
  /**
   * Terminates a session.  The cookie is set with a lifetime of 0, and all
   * session variables are deleted.
   *
   * @access public
   */
  public function destroy() {
    if ($this->state != 'destroyed') {
      // delete the cookie
      setcookie(self::$cookiename, "", 0);
    
      // destroy the session
      session_unset();
      session_destroy();
    
      // set the state
      $this->state = 'destroyed';
    }
  }
  
  /***********************\
  |   Private Functions   |
  \***********************/

  /**
   * Sets up and establishes the cookie associated with this session.
   * 
   * @access private
   */
  private function _setCookie() {
    setcookie(self::$cookiename, $this->_package());
  }
  
  /**
   * Starts a session.
   *
   * @access private
   */
  private function _start() {
    if ($this->state != 'active') {
      session_start();
      $this->state = 'active';
    }
  }
  
  /**
   * This performs a few checks against the fields of the cookie, ensuring
   * validity with the format and that the session has not expired.  If any
   * errors are found, an AuthException is thrown.
   *
   * @throws AuthException
   * @access private
   */
  private function _validate() {
    // first, check to make sure the necessary fields are set
    if (!$this->userid || !$this->created) {
      throw new AuthException('Malformed cookie: missing required fields!');
    }
    
    // now check on the expiration of the cookie
    if ($this->userid != 'guest' && (time() - $this->created > self::$expire)) {
      $this->state = 'expired';
      throw new AuthException('Session has expired!');
    }
    
    // if the session is still valid, should we reset the cookie?
    if (time() - $this->created > self::$warning) {
      setcookie(self::$cookiename, $this->_package());
    }
  }
  
  /**
   * This essentially resets the cookie, overwriting the created time in order
   * to refresh the cookie and essentially restart the session.
   *
   * @access private
   */
  private function _reissue() {
    $this->created = time();
  }
  
  /**
   * This assembles the contents of a cookie, and encrypts everything
   * in a two-way hash to protect the contents from being mangled.
   *
   * @access private
   * @return string An encrypted string of all the necessary cookie contents.
   */
  private function _package() {
    // set the created field
    $this->_reissue();
    
    // create the string of cookie contents
    $arr = array($this->created, $this->userid);
    $contents = implode(self::$glue, $arr);
    
    // encrypt the contents
    return $this->_encrypt($contents);
  }
  
  /**
   * This decomposes an existing cookie, decrypting the contents and 
   * splitting apart the respective strings to form the pieces of 
   * information needed to manage a session.  If the data is unreadable,
   * an AuthException is thrown.
   *
   * @access private
   * @param string $cookie The crypttext of the cookie.
   */
  private function _unpackage($cookie) {
    // first, decrypt the cookie
    $contents = $this->_decrypt($cookie);
    
    // decompose the strings
    list($this->created, $this->userid) = explode(self::$glue, $contents);
    
    // make sure everything is valid
    try {
      $this->_validate();
    } catch (AuthException $ae) {
      $this->userid = 'guest';
      $this->_setCookie();
      Error::errorMsg('Your session has expired.  Please log in again.', $ae->getMessage());
    }
  }
  
  /**
   * An encryption wrapper to take advantage of debug mode.  If debug mode is
   * on, then no encryption is performed.  Otherwise, the Utility class' 
   * encryption function is utilized.
   *
   * @access private
   * @param string $plaintext The text to be encrypted.
   * @return string The encrypted text.
   */
  private function _encrypt($plaintext) {
    $cfg = Factory::getConfig();
    return ($cfg->getValue('debug') ? 
            $plaintext : Utility::encrypt($plaintext, &$this->encryption));
  }
  
  /**
   * A decryption wrapper to take advantage of debug mode.  If debug mode is
   * on, then no decryption is performed (assuming the data was not 
   * encrypted to begin with; this assumption is up to the developer to
   * enforce).  Otherwise, the Utility class' decryption method is utilized.
   * 
   * @access private 
   * @param string $crypttext The text to be decrypted.
   * @return string The decrypted (plain) text.
   */
  private function _decrypt($crypttext) {
    $cfg = Factory::getConfig();
    return ($cfg->getValue('debug') ? 
            $crypttext : Utility::decrypt($crypttext, &$this->encryption));
  }
}

?>