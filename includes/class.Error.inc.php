<?php

defined('IC') or die('Restricted access');

import('config');
import('factory');

/**
 * This works as a layer of error-handling within the UI of the appointment
 * management framework.  By assuming the presence of a generic error-handling
 * script, this can display critical or non-critical error messages to the user
 * for the purposes of feedback or even debugging.
 *
 * Created on Jul 14, 2008
 *
 * @package Introverted.Champions
 * @author Shannon Quinn
 * @version 1.0
 */

// ---------------------------------------------
// Revisions
// 0.1  - 7/14/08
//      - Begun
// 1.0  - Completed errorMsg() function

class Error {
  
  // not a whole lot to do here...other than Chris' mom

  /**
   * This function prints an error message to the UI, defaulting to 
   * error.tpl for the template.
   * 
   * The optional parameters allow for some customization in how the error
   * message is handled.  If additional debug output is required, this
   * can be specified in the second parameter.  Furthermore, if some other 
   * custom template is required for displaying the error, this template 
   * can be specified with the third parameter.
   * 
   * NOTE: The debug output will simply be appended to the main message, to
   * guarantee that it will show up in the rendered template.  Also, the 
   * Config debug value must be set to true.
   *
   * @static
   * @access public
   * @param string $message The error message that will be displayed to the user.
   * @param string $debugMessage Any additional debugging information.
   * @param string $errorScript The Smarty template which will display the error.
   */
  public static function errorMsg($message,
                                  $debugMessage = null,
                                  $errorScript = 'error.tpl') {
    // first, grab the two objects we need
    $cfg = Factory::getConfig();
    $smarty = Factory::getSmarty();
    
    // next, assign the smarty template variables
    $msg = '<b>' . $message . '</b>' . ($cfg->getValue('debug') ? "<br />\n" . $debugMessage : '');
    $smarty->assign('message', $msg);
    
    // now display the message
    $smarty->display($errorScript);
    exit;
  }
}

?>