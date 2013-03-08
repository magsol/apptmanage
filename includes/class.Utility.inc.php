<?php

defined('IC') or die('Restricted access');

import('dbconn');
import('request');
import('config');
import('factory');

/**
 * This class takes care of some of the useful utilities via static methods.
 * 
 * Created on Jul 2, 2008, 2008
 * 
 * @package Introverted.Champions
 * @author Shannon Quinn
 * @version 2.0
 */

// ---------------------------------------------
// Revisions
// 0.1  - 7/2/08
//      - Begun
//      - Wrote cleanDatabase()
//      - Wrote cleanDisplay()
//      - Wrote encrypt()
//      - Wrote decrypt()
//      - Added documentation
// 0.2  - Tested
//
// 1.0  - Everything seems to be working
// 1.1  - 7/6/08
//      - Improved documentation
//      - Added dateMySQL(), dateUnix(), and now() functions
// 1.2  - 7/8/08
//      - Added redirect() function
// 1.3  - 7/8/08
//      - Added simplifyString() function
// 1.4  - 7/9/08
//      - Added email() function
// 1.5  - 7/16/08
//      - Added a check for a null parameter into cleanDatabase() and cleanDisplay()
// 1.6  - 7/17/08
//      - Checks removed
// 1.7  - 7/17/08
//      - Made redirect() modify the argument to create an absolute address if
//        not already provided
// 1.8  - 7/19/08
//      - Added checks in dateMySQL() and dateUnix() for the data type of the
//        date parameter
// 1.9  - 7/28/08
//      - Fixed "From" field in email()
// 2.0  - 8/2/08
//      - Added admin's email address to "From" field in email()

class Utility {
  
  /***********************\
  |       Functions       |
  \***********************/
  
  /**
   * Cleans input to make it safe for database query input.  Uses the 
   * mysql_real_escape_string function to escape control characters.
   * 
   * @static
   * @access public
   * @param string $text The text to be inserted into a MySQL query.
   * @param bool $trim Optional parameter, if set the text is trimmed for whitespace.
   * @return string The sanitized text, all control characters escaped.
   */
  public static function cleanDatabase($text, $trim = false) {
    /*
    if (!$text) { // just in case...
      return null;
    }
    */
    $db = Factory::getDB(); // kind of a hack...
    
    // clean the text
    $retval = mysql_real_escape_string($text);
    if ($trim) {
      $retval = trim($retval);  
    }
    return $retval;
  }
  
  /**
   * Cleans input to make it safe to render as HTML.  Uses the htmlentities
   * function to turn HTML control characters into entities.
   * 
   * @static
   * @access public
   * @param string $text The text containing HTML control characters.
   * @param bool $trim Optional parameter, if set the text is trimmed for whitespace.
   * @return string The HTML, containing entities instead of control characters.
   */
  public static function cleanDisplay($text, $trim = false) {
    /*
    if (!$text) {
      return null;
    }
    */
    // clean the text
    $retval = htmlspecialchars($text, ENT_COMPAT, 'UTF-8', false);
    if ($trim) {
      $retval = trim($retval);
    }
    return $retval;
  }

  /**
   * Encrypts plaintext using symmetric encryption from mcrypt.
   * 
   * @static
   * @access public
   * @param string $plaintext The plaintext to be encrypted.
   * @param resource $handle The encryption resource.
   * @return string The encrypted text.
   */
  public static function encrypt($plaintext, &$handle) {
    $cfg = Factory::getConfig();
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($handle), MCRYPT_RAND);
    mcrypt_generic_init($handle, $cfg->getValue('secret_key'), $iv);
    $crypttext = mcrypt_generic($handle, $plaintext);
    mcrypt_generic_deinit($handle);
    return $iv . $crypttext;
  }
  
  /**
   * Decrypts crypttext using symmetric encryption from mcrypt.
   * 
   * @static
   * @access public
   * @param string $crypttext The crypttext to be decrypted.
   * @param resource $handle The encryption handle.
   * @return string The decrypted plaintext.
   */
  public static function decrypt($crypttext, &$handle) {
    $cfg = Factory::getConfig();
    $ivsize = mcrypt_enc_get_iv_size($handle);
    $iv = substr($crypttext, 0, $ivsize);
    $crypttext = substr($crypttext, $ivsize);
    mcrypt_generic_init($handle, $cfg->getValue('secret_key'), $iv);
    $plaintext = mdecrypt_generic($handle, $crypttext);
    mcrypt_generic_deinit($handle);
    return $plaintext;
  }
  
  /**
   * A simple redirect to a different url.
   * 
   * @static
   * @access public
   * @param string $url The URL (can be relative or absolute) to redirect
   *        the server to.
   */
  public static function redirect($url) {
    if (!strstr($url, 'http://') && !strstr($url, 'https://')) { // if it's not absolute, make it so
      $cfg = Factory::getConfig();
      $url = 'http://' . Request::getString('SERVER_NAME', 'server') . '/' . 
             $cfg->getValue('rel_path') . $url;
    }
    header('Location: ' . $url);
  }
  
  /**
   * This function strips out all non-alphanumeric characters from the
   * string, leaving only those characters in the classes a-z, A-Z, and 0-9.
   * This can be used to help compare field names while making such comparisons
   * as flexible and forgiving as possible.
   *
   * @static
   * @access public
   * @param string $str The string to be simplified.
   * @param bool $lowercase Should the string also be made lowercase?
   * @return string A new string stripped of extraneous characters.
   */
  public static function simplifyString($str, $lowercase = false) {
    $retval = preg_replace('/([^a-z0-9])*/', '', $str);
    if ($lowercase) {
      $retval = strtolower($retval);
    }
    return $retval;
  }
  
  /**
   * This is just a wrapper for the built-in PHP mail() function.
   * 
   * The $from field is taken directly from the server configuration file.
   * If you want the "From" header in the email to read something different,
   * change the email address under "email" in class.Config.inc.php.
   * Furthermore, if you wish to add additional headers to the email (such
   * as a Reply-To, or a Bcc, etc), append the following PHP code to the
   * end of the mail() function:
   * 
   * // we want to add a Reply-To of "abc@123.com"
   * return mail($to, $subject, $message, "From: " . $cfg->getValue('email') .
   *             "\r\nReply-To: abc@123.com");
   * 
   * @static
   * @access public
   * @param string $to
   * @param string $subject
   * @param string $message
   * @return bool True on success, false on failure.
   */
  public static function email($to, $subject, $message) {
    $cfg = Factory::getConfig();
    return mail($to, $subject, $message, "From: " . $cfg->getValue('email'));
  }
}

?>