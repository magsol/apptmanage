<?php
include_once('config.inc.php');

import('user');
import('factory');
import('request');

/******************************************\
* Created on Jul 18, 2008                  *
********************************************
* This file is responsible for modifying   *
* the User's account details.              *
\******************************************/

// active user
$user = Factory::getUser();
$smarty = Factory::getSmarty();

// is this user a guest?
if ($user->isGuest()) {
  Utility::redirect('index.php');
}
$db = Factory::getDB();

// alright, the user is valid
// have they committed any changes to their information?
$updated = Request::getString('first', 'post');
if ($updated) {
  $first     = Utility::cleanDatabase(Request::getString('first', 'post'));
  $last      = Utility::cleanDatabase(Request::getString('last', 'post'));
  $street    = Utility::cleanDatabase(Request::getString('street', 'post'));
  $city      = Utility::cleanDatabase(Request::getString('city', 'post'));
  $state     = Utility::cleanDatabase(Request::getString('state', 'post'));
  $cellphone = Utility::cleanDatabase(Request::getString('cellphone', 'post'));
  $homephone = Utility::cleanDatabase(Request::getString('homephone', 'post'));
  
  // run a check to make sure everything is valid
  if ($first && $last && $street && $city && $state && $state != 'NULL' && 
      $cellphone && $homephone) {
    // everything checks out!
    $query = 'UPDATE user SET first_name = "' . $first . '", last_name = "' . 
             $last . '", street = "' . $street . '", city = "' . $city . '", ' .
             'state = "' . $state . '", cell_phone = "' . $cellphone . '", ' .
             'home_phone = "' . $homephone . '" WHERE email = "' . 
             $user->getUserID() . '"';
    $db->setQuery($query);
    if ($db->query()) {
      // update successful
      $smarty->assign('title', 'Account Details Updated');
      $smarty->assign('message', 'Your account has been updated.');
      $smarty->assign('url', 'home.php');
      $smarty->assign('urltext', 'Go to Home Page');
      $smarty->display('redirectmsg.tpl');
      exit;
    } else {
      Error::errorMsg('An error occurred while processing your account change.', 
                      'Query: ' . $db->getActiveQuery() . '<br />MySQL response: ' .
                      $db->getError());
    }
  }
}

// get the information on this user
$query = 'SELECT * FROM user WHERE email = "' . $user->getUserID() . '"';
$db->setQuery($query);
$data = $db->loadRow();

// fill out the template with their data
$smarty->assign('first', $data['first_name']);
$smarty->assign('last', $data['last_name']);
$smarty->assign('street', $data['street']);
$smarty->assign('city', $data['city']);
$smarty->assign('cellphone', $data['cell_phone']);
$smarty->assign('homephone', $data['home_phone']);
$smarty->assign('states', $STATES);
$smarty->assign('stateSelected', $data['state']);
$smarty->assign('state', $data['state']);
$smarty->display('account.tpl');


?>