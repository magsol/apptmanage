<?php
include_once('config.inc.php');

import('user');
import('factory');
import('dbconn');
import('request');
import('utility');
import('error');
import('calendar');

/******************************************\
* Created on Jul 16, 2008                  *
********************************************
* This script is responsible for helping   *
* registered users reset lost or forgotten *
* passwords.  It has multiple states, so   *
* be careful to inspect what state is      *
* currently active at any given time.      *
* Password reset requesting and resetting  *
* are all handled by this script.          *
\******************************************/

// number of hours until a password request is deemed expired
$HOURS = 24;

/* POSSIBILITIES FOR REACHING THIS PAGE
 * 
 * 1) User navigated here simply by clicking the link
 * 2) User submitted an email address to start the process
 * 3) User followed a link in an email
 * 4) User submitted a new password
 */

// Enable SSL if it's available
$cfg = Factory::getConfig();
if (!Request::getString('HTTPS', 'server') && $cfg->getValue('ssl_enabled')) {
  Utility::redirect('https://' . Request::getString('SERVER_NAME', 'server') .
                                Request::getString('SCRIPT_NAME', 'server') . 
                                (Request::getSet('hash', 'get') ? '?hash=' . 
                                Request::getString('hash', 'get') : ''));
}

// do all the other shtuff
$smarty = Factory::getSmarty();
$db = Factory::getDB();
if (Request::getString('email', 'post')) { // CASE 2
  // first, check to make sure the user exists
  $email = Utility::cleanDatabase(Request::getString('email', 'post'));
  $db->setQuery('SELECT userid FROM user WHERE email = "' . $email . '"');
  $result = $db->loadRow();
  if (!isset($result['userid'])) {
    Error::errorMsg('The email address you supplied was not found in our database. ' .
                    'If you have not registered, please do so to log in.');
  }
  
  // generate the password request, store it in the DB, and sent the link
  // to the user by email
  $timestamp = Calendar::dateMySQL(time());
  $hash = md5($email . $timestamp);
  $db->setQuery('INSERT INTO request_pass (email, timestamp, hash) VALUES ' . 
                '("' . $email . '", "' . $timestamp . '", "' . $hash . '")');
  if ($db->query()) {
    // successfully inserted the password request to the database
    $smarty->assign('title', 'Password Request Processed');
    $smarty->assign('message', 'Thank you!  You will receive an email ' . 
                    'with further instructions.');
    $smarty->assign('url', 'index.php');
    $smarty->assign('urltext', 'Return to Login');
    Utility::email(Request::getString('email', 'post'), 'Password Reset',
                   "Hello $email,\n\nYou have requested to reset your password. " .
                   "In order to do so, please click on the following link:\n\n" .
                   "http://" . Request::getString('SERVER_NAME', 'server') . 
                   Request::getString('SCRIPT_NAME', 'server') . '?hash=' . 
                   $hash . "\n\nIf you cannot click the link, copy and paste " .
                   "it into your browser window.  This link will remain active " .
                   "for up to $HOURS hours from the time you submitted the request, " .
                   "after which you will be required to submit another request.\n\n" .
                   "Regards,\nThe Introverted Champions");
    // also, wipe out any old-timey reset requests over the allotted time
    $db->setQuery('DELETE FROM request_pass WHERE timestamp < "' . 
                  Calendar::dateMySQL(time() - (60 * 60 * $HOURS)) . '"');
    $db->query();
    $smarty->display('redirectmsg.tpl');
  } else {
    // an error occurred
    Error::errorMsg('An error occurred while processing your request.', 
                    'Query: ' . $db->getActiveQuery() . '<br />MySQL: ' .
                    $db->getError());
  }
} else if (Request::getString('hash', 'get')) { // CASE 3
  // first, verify that the value in "key" is a valid password request
  $hash = Utility::cleanDatabase(Request::getString('hash', 'get'));
  $db->setQuery('SELECT * FROM request_pass WHERE hash = "' . $hash . '"');
  $data = $db->loadRow();
  if (!isset($data['request_id']) || 
      strtotime($data['timestamp']) < time() - (60 * 60 * $HOURS)) {
    // nice try, d00d
    Error::errorMsg('You have supplied an invalid reset link.  Please make ' .
                    'sure you copied the link exactly as provided in the email ' .
                    "you received.  If it has been over $HOURS hours since you " .
                    'submitted your request, you will need to submit another one.');
  }
  
  // second, display the dialogue to allow the user to input a new password
  $smarty->assign('hash', $hash);
  $smarty->display('passreset.tpl');
  
} else if (Request::getString('password', 'post')) { // CASE 4
  // first, check one last time that the hash is valid
  // no need to check the date, though
  $hash = Utility::cleanDatabase(Request::getString('hash', 'post'));
  $db->setQuery('SELECT * FROM request_pass WHERE hash = "' . $hash . '"');
  $data = $db->loadRow();
  if (!isset($data['request_id'])) {
    Error::errorMsg('A form error has occurred.  Please try again later.');
  }
  
  // commit the password straight to the database
  $password = Utility::cleanDatabase(Request::getString('password', 'post'));
  if (User::changePassword($password, $data['email'])) {
    // delete this request from the database...it has been serviced
    $db->setQuery('DELETE FROM request_pass WHERE hash = "' . $hash .'"');
    if (!$db->query()) {
      Error::errorMsg('A problem was encountered with the database.  We ' .
                      'apologize for the inconvenience.', 'Query: ' .
                      $db->getActiveQuery() . '<br />MySQL: ' . $db->getError());
    }
    
    // redirect the user to the login page
    $smarty->assign('title', 'Password Changed!');
    $smarty->assign('message', 'Your password has been successfully changed!');
    $smarty->assign('url', 'index.php');
    $smarty->assign('urltext', 'Return to Login');
    $smarty->display('redirectmsg.tpl');
  } else {
    Error::errorMsg('An error occurred while attempting to submit your ' . 
                    'new password.  No changes have been made.', 'MySQL: ' .
                    $db->getError());
  }
} else { // CASE 1
  
  // nope, got here by navigating, so show the email dialogue and
  // generate a password request
  $smarty->display('password.tpl');
}

?>