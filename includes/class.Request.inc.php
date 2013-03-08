<?php

defined('IC') or die('Restricted access');

/**
 * This class encapsulates the access of form variables; namely, $_POST, 
 * $_GET, and $_SERVER
 * 
 * Created on Jun 27, 2008, 2008 
 * 
 * @package Introverted.Champions
 * @author Shannon Quinn
 * @version 1.0
 */

// ---------------------------------------------
// Revisions
// 0.1  - 6/27/08
//      - Begun
// 0.2  - 7/2/08
//      - Completed documentation
//      - Wrote getVar()
//      - Wrote getInt()
//      - Wrote getBool()
//      - Wrote getString()
//      - Wrote setVar()
// 0.3  - Fixed reference errors in _array(), getVar(), and setVar()
//
// 1.0  - Everything seems to be working
// 1.1  - 7/9/08
//      - getVar() returns null if the request value isn't set
// 1.2  - 7/20/08
//      - Added getSet() method, and 'raw' type to getVar()

class Request {
  
  /**
   * Retrieves and returns a variable from the specified superglobal array,
   * and sets the return value to the specified type.
   *
   * @static
   * @access public
   * @param string $name The name of the variable, acts as the key.
   * @param string $method The name of the superglobal array holding the variable.
   * @param string $type The type of the variable (string, int, bool, etc)
   * @return mixed The value of the desired variable.
   */
  public static function getVar($name, $method, $type) {
    $arr =& Request::_array($method);

    // is the value we're looking for even set?
    if (!isset($arr[$name])) {
      return null;
    }
    
    // is this simply to determine whether or not it is set?
    if ($type == 'raw') {
      return isset($arr[$name]);
    }
    
    // determine the return type
    $retval = $arr[$name];
    settype($retval, $type);
    return $retval;
  }
  
  /**
   * A proxy to getVar(), always returns a value of type int.
   *
   * @static
   * @access public
   * @param string $name The name of the variable, acts as the key.
   * @param string $method The name of the superglobal array holding the variable.
   * @return int The int value of the desired variable.
   */
  public static function getInt($name, $method) {
    return Request::getVar($name, $method, 'int');
  }
  
  /**
   * A proxy to getVar(), always returns a value of type bool.
   *
   * @static
   * @access public
   * @param string $name The name of the variable, acts as the key.
   * @param string $method The name of the superglobal array holding the variable.
   * @return bool The boolean value of the desired variable.
   */
  public static function getBool($name, $method) {
    return Request::getVar($name, $method, 'bool');
  }
  
  /**
   * A proxy to getVar(), always returns a value of type string.
   *
   * @static
   * @access public
   * @param string $name The name of the variable, acts as the key.
   * @param string $method The name of the superglobal array holding the variable.
   * @return string The string value of the desired variable.
   */
  public static function getString($name, $method) {
    return Request::getVar($name, $method, 'string');
  }
  
  /**
   * A proxy to getVar(), this determines if a particular value is set.
   * 
   * If the specified value exists, this function returns true, regardless
   * of what that value is.  If the variable does not exist in the specified
   * superglobal array, this function returns false.
   *
   * @param string $name The name of the variable, acts as the key.
   * @param string $method The name of the superglobal array holding the variable.
   * @return bool True if the variable exists, false otherwise.
   */
  public static function getSet($name, $method) {
    return Request::getVar($name, $method, 'raw');
  }
  
  /**
   * Sets and saves a variable within one of the superglobal arrays.
   * This will overwrite any previously saved variables with the
   * given name in the given superglobal array.
   *
   * @static
   * @access public
   * @param string $name The name of the variable, will be used as the key.
   * @param mixed $value The value of the variable.
   * @param string $method The name of the superglobal array.
   */
  public static function setVar($name, $value, $method) {
    $arr =& Request::_array($method);
    $arr[$name] = $value;
  }
  
  /**
   * Carries out the switch statement to determine superglobal array.
   * 
   * @access private
   * @param string $method The name of the desired superglobal array.
   * @return array A reference to the corresponding superglobal array.
   */
  private function &_array($method) {
    $method = strtoupper($method);
    $retVal = "";
    switch($method) {
      case 'POST':
        return $_POST;
      case 'GET':
        return $_GET;
      case 'SERVER':
        return $_SERVER;
      default:
        return $_REQUEST;
    }
  }
}

?>