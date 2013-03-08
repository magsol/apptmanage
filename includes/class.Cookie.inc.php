<?php

defined('IC') or die('Restricted access');

import('authexception');
import('utility'); // symmetric encryption

/**
 * This class manages cookies that are issued to users when they authenticate 
 * with the system. REQUIRES PHP's mcrypt module, as of the latest version.
 * 
 * Created on Jun 22, 2008, 2008
 *
 * @deprecated Deprecated since version 0.9; USE SESSION CLASS INSTEAD.
 * @package Introverted.Champions
 * @author Shannon Quinn
 * @version 1.0
 */

// ---------------------------------------------
// Revisions
// 0.1  - 6/22/08
//      - Skeleton created
// 0.2  - 6/24/08
//      - Documentation added
// 0.3  - 6/25/08
//      - Documentation completed
//      - All private methods completed
//      - All public methods completed
//      - TODO: Need a constructor
//              Error handling?
// 0.4  - 6/25/08
//      - Added AuthException for error handling
// 0.5  - 6/25/08
//      - Removed $key
//      - Added Factory functionality to retrieve secret key
//      - Filled out private constructor
// 0.6  - Fixed a few syntax errors
// 0.7  - Removed encryption/decryption functionality; calls Utility instead
//      - Changed $td to type resource
//      - Changed $userid to type int
//      - Added getInstance()
// 0.8  - 7/8/08
//      - Deleted getInstance()
//      - Made constructor public
// 0.9  - 7/9/08
//      - Changed $userid to string
//      - Added getUserID() function
//      - TODO - Fix encryption/decryption mangling of cookie contents
// ***  - 7/14/08
//      - THIS CLASS IS DEPRECATED
// 1.0  - 7/16/08
//      - Compiled detailed deprecated tag.

class Cookie {

  /***********************\
  |   Private Attributes  |
  \***********************/

  /**
   * Stores the timestamp of issuance for this cookie.
   * 
   * @access private
   * @var int $created result of call time 'time()'
   */
  private $created;
  
  /**
   * Uniquely identifies the owner of this cookie.
   * 
   * @access private
   * @var string $userid handle for this user
   */
  private $userid;
  
  /**
   * Cookie version.
   * 
   * @access private
   * @var string $version
   */ 
  private $version;
  
  /**
   * Mcrypt handle.
   * 
   * @access private
   * @var resource $td
   */
  private $td;
  
  /**
   * Debug mode
   *
   * @access private
   * @var bool $debug True if in debug mode, false otherwise
   */
  private $debug;

  /***********************\
  |   Static Attributes   |
  \***********************/

  /**
   * Algorithm mcrypt will use for this cookie.
   * 
   * @static
   * @access public
   * @var string $cypher
   */
  static $cypher = 'blowfish';
  
  /**
   * Encryption mode.
   * 
   * @static
   * @access public
   * @var string $mode
   */
  static $mode = 'cfb';
  
  /**
   * Generic name of a cookie issued.
   * 
   * @static
   * @access public
   * @var string $cookiename
   */
  static $cookiename = 'introvertedchampions';
  
  /**
   * Version generator.
   * 
   * @static
   * @access public
   * @var string $myversion
   */
  static $myversion = '0.9';
  
  /**
   * Lifetime of an issued cookie, in seconds.
   * 
   * @static
   * @access public
   * @var string $expiration
   */
  static $expiration = '600';
  
  /**
   * Grace period after issuance to reissue cookie.
   * 
   * @static
   * @access public
   * @var string $warning
   */
  static $warning = '300';
  
  /**
   * Delimeter in encrypted cookie by which to split up fields.
   * 
   * @static
   * @access public
   * @var string $glue
   */
  static $glue = '|';

  /***********************\
  |    Public Functions   |
  \***********************/
  
  /**
   * Constructor. Creates a new Cookie.
   * 
   * @access public
   * @param string $userid Uniquely identifies the user of this cookie.  If
   *        null, will attempt to use an active cookie to initialize this 
   *        field.  If no active cookie is found, an exception will be thrown.
   */
  public function __construct($userid) {
    $cfg = Factory::getConfig();
    $this->debug = $cfg->getValue('debug');
    $this->td = mcrypt_module_open(self::$cypher, '', self::$mode, '');
    if ($userid) {
      $this->userid = $userid;
      $this->set();
    } else {
      if (isset($_COOKIE[self::$cookiename])) {
        $this->_unpackage($_COOKIE[self::$cookiename]);
      } else {
        throw new AuthException("No cookie");
      }
    }
  }
  
  /**
   * Accessor for the $userid field; returns the email address of the user
   * who owns this cookie.
   *
   * @return string The email address of the user.
   */
  public function getUserID() {
    return $this->userid;
  }
  
  /**
   * Resets a cookie.
   * 
   * @access public
   */
  public function set() {
    $cookie = $this->_package();
    setcookie(self::$cookiename, $cookie);
  }
  
  /**
   * Validates a cookie, reissues if necessary.
   * 
   * @access public
   */
  public function validate() {
    // check that all fields exist
    if (!$this->version || !$this->created || !$this->userid) {
      throw new AuthException("Malformed cookie in validate. version=$this->version | created=$this->created | userid=$this->userid");
    }
    
    // check that the version is correct
    if ($this->version != self::$myversion) {
      throw new AuthException("Version mismatch");
    }
    
    // check cookie expiration
    if ((time() - $this->created) > self::$expiration) {
      throw new AuthException("Cookie expired");
    
    } else if ((time() - $this->created) > self::$warning) {
      // cookie is in a warning period...reset it
      $this->set();
    }
  }
  
  /**
   * Destroys a cookie session.
   * 
   * @access public
   */
  public function logout() {
    // clear the cookie
    setcookie(self::$cookiename, "", 0);
  }
  
  /***********************\
  |   Private Functions   |
  \***********************/
  
  /**
   * Packages the cookie together.
   * 
   * @access private
   * @return object A Cookie object, packaged and ready.
   */
  private function _package() {
    // create an array of the data to store
    $this->created = time();
    $this->version = self::$myversion;
    $arr = array($this->version, $this->created, $this->userid);
    
    // glue everything together
    $cookie = implode(self::$glue, $arr);
    
    // return the encrypted cookie
    return $this->_encrypt($cookie);
  }
  
  /**
   * Takes apart a cookie and verifies its contents.
   *
   * @access private
   * @param string $cookie The packaged string representation of the cookie.
   */
  private function _unpackage($cookie) {
    // decrypt the cookie
    $buffer = $this->_decrypt($cookie);

    // decompose the stored parameters of the cookie
    list($this->version, $this->created, $this->userid) = explode(self::$glue, $buffer);
    
    // check data consistency
    if ($this->version != self::$myversion || !$this->created || !$this->userid) {
      throw new AuthException("Malformed cookie in _unpackage");
    }
  }
  
  /**
   * Encrypts the contents of the cookie.
   *
   * @access private
   * @param string $plaintext The plaintext to be encrypted.
   * @return string The crypttext of the cookie.
   */
  private function _encrypt($plaintext) {
    return ($this->debug ? $plaintext : Utility::encrypt($plaintext, &$this->td));
  }
  
  /**
   * Decrypts the contents of the cookie.
   * 
   * @access private
   * @param string $crypttext The crypttext to be decrypted.
   * @return string The plaintext version of the cookie.
   */
  private function _decrypt($crypttext) {
    return ($this->debug ? $crypttext : Utility::decrypt($crypttext, &$this->td));
  }
  
  /**
   * Resets the issue time of the cookie.
   * 
   * @access private
   */
  private function _reissue() {
    $this->created = time();
  }

}

?>