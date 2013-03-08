<?php
include_once('config.inc.php');

import('factory');
import('request');
import('utility');
import('user');

/******************************************\
* Created on Jul 16, 2008                  *
********************************************
* This script handles user registration.   *
* The user creates a new profile by        *
* entering their information and submitting*
* it to the database, allowing them to     *
* log into the system and use its          *
* resources.                               *
\******************************************/

$registered = Request::getString('email', 'post');
$smarty = Factory::getSmarty();
if ($registered) { // somebody wants to join the club!
  // first, run a check to make sure all the fields were filled in
  // with valid values 
  $email     = Utility::cleanDatabase(Request::getString('email', 'post'));
  $first     = Utility::cleanDatabase(Request::getString('first', 'post'));
  $last      = Utility::cleanDatabase(Request::getString('last', 'post'));
  $sex       = Utility::cleanDatabase(Request::getString('sex', 'post'));
  $street    = Utility::cleanDatabase(Request::getString('street', 'post'));
  $city      = Utility::cleanDatabase(Request::getString('city', 'post'));
  $state     = Utility::cleanDatabase(Request::getString('state', 'post'));
  $zipcode   = Utility::cleanDatabase(Request::getString('zipcode', 'post'));
  $cellphone = Utility::cleanDatabase(Request::getString('cellphone', 'post'));
  $homephone = Utility::cleanDatabase(Request::getString('homephone', 'post'));
  $pass1     = Request::getString('pass1', 'post');
  $pass2     = Request::getString('pass2', 'post');
  
  if ($email   && $first     && $last      && $sex   && $street && $city && $state && 
      $state != 'NULL' && $zipcode && $cellphone && $homephone && $pass1 && $pass2  && 
      ($pass1 == $pass2)) {
    // everything checks out, so commit the information to the database and
    // redirect the user to the login page
    
    // check email for originality
    $db = Factory::getDB();
    $db->setQuery('SELECT email FROM user WHERE email = "' . $email . '"');
    $original = $db->loadRow();
    if (isset($original['email'])) { // EMAIL ALREADY IN USE
      Error::errorMsg('Your email, ' . Utility::cleanDisplay($email) .
                      ' has already been registered!');
    }

    // build the query
    $query = 'INSERT INTO user (email, password, first_name, last_name, gender, ' .
             'home_phone, cell_phone, street, city, state, userclass) VALUES ("' . 
             $email . '", "' . md5($pass1) . '", "' . $first . '", "' . $last . 
             '", "' . $sex . '", "' . $homephone . '", "' . $cellphone . '", "' . 
             $street . '", "' . $city . '", "' . $state . '", "' . User::$defaultClass .
             '")';
    $db->setQuery($query);
    if ($db->query()) {
      // let the user know they have successfully registered with the system
      Utility::email($email, "Registration Successful!", "Hello " . $first . "!\n\n" .
                     "Thank you for registering.  You will be able to log in using " .
                     "this email address (" . $email . ") and the password you " .
                     "provided.\n\nIf you ever forget your password, you can always " .
                     "reset it by clicking the \"Forgot Password?\" link on the " . 
                     "login page and providing this email address.\n\nRegards,\n " .
                     "The Introverted Champions");
      
      $smarty->assign('firstName', $first);
      $smarty->display('registerSuccessful.tpl');
      exit; // kill the script
    } else { // an error occurred
      Error::errorMsg('An error occurred while processing your registration.', 
                      'Query: ' . $db->getActiveQuery() . '<br />' . 
                      'MySQL response: ' . $db->getError());
    }
  } else { // somebody didn't fill something out...or got it wrong...or whatever
    $smarty->assign('email', Request::getString('email', 'post'));
    $smarty->assign('first', Request::getString('first', 'post'));
    $smarty->assign('last', Request::getString('last', 'post'));
    $smarty->assign('street', Request::getString('street', 'post'));
    $smarty->assign('city', Request::getString('city', 'post'));
    $smarty->assign('zipcode', Request::getString('zipcode', 'post'));
    $smarty->assign('cellphone', Request::getString('cellphone', 'post'));
    $smarty->assign('homephone', Request::getString('homephone', 'post'));
    $smarty->assign('states', $STATES);
    $smarty->assign('stateSelected', Request::getString('state', 'post'));
  }
}

// no registration attempt, just show the page
$smarty->assign('states', $STATES);
$smarty->display('register.tpl');

// echo (mt_rand(0, 1) ? '<img src="http://www.prism.gatech.edu/~gtg910r/highscore.jpg" title="BOOYA! IT\'S OVER 40 BILLION!" />' : '');


?>