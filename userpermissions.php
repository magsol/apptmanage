<?php

include_once('config.inc.php');

import('factory');
import('user');
import('utility');
import('request');

/******************************************\
* Created on Jul 30, 2008                  *
********************************************
* This script is purely for the system     *
* administrator, used to handle the        *
* permissions level of all the other users *
* in the system, promoting and demoting    *
* permissions levels as necessary.         *
\******************************************/

// first, is the user logged in?
$user = Factory::getUser();
if ($user->isGuest()) {
  Utility::redirect('index.php');
}

// second, are they an administrator?
if ($user->getUserClass() != 'administrator') {
  Utility::redirect('home.php');
}

$db = Factory::getDB();
$smarty = Factory::getSmarty();

// sorry dudes. no h4x0ring allowed
// has this form been submitted with updates?
if (Request::getSet('submitted', 'post')) {
  // in order to obtain all the form information, let's first
  // query the database for all the users
  $db->setQuery('SELECT * FROM user WHERE userclass != "administrator"');
  $users = $db->loadRows();

  // now, iterate through the users, using their email addresses
  // to snag POST data from the form
  $numUsers = count($users);
  for ($i = 0; $i < $numUsers; $i++) {
    $userclass = Request::getString($users[$i]['userid'], 'post');
    $cfg->fireDebugOutput('Userclass', $userclass);
    // run an update query
    $query = 'UPDATE user SET userclass = "' . 
             Utility::cleanDatabase($userclass) . '" WHERE email = "' . 
             $users[$i]['email'] . '"';
    $db->setQuery($query);
    $cfg->fireDebugOutput('Query ' . $i, $query);
    if (!$db->query()) {
      Error::errorMsg('A fatal error has occurred.', 'Query: ' . $db->getActiveQuery() . 
                      '<br />MySQL: ' . $db->getError());
    }
  }
  
  // show the success message
  $smarty->assign('title', 'Accounts updated!');
  $smarty->assign('message', 'You have successfully updated ' . $numUsers . ' accounts.');
  $smarty->assign('url', 'home.php');
  $smarty->assign('urltext', 'Go to your home page');
  $smarty->display('redirectmsg.tpl');
  exit;
}

// grab the necessary information from the database
$db->setQuery('SELECT * FROM user WHERE userclass != "administrator"');
$users = $db->loadRows();
$db->setQuery('SELECT class FROM user_class WHERE class != "administrator" ' .
              'AND class != "guest"');
$results = $db->loadRows();
$userclasses = array();
for ($i = 0; $i < count($results); $i++) {
  $userclasses[$results[$i]['class']] = $results[$i]['class'];
}

// do some debugging
$cfg->fireDebugOutput('Users', $users);
$cfg->fireDebugOutput('User classes', $userclasses);

// set the template variables
$smarty->assign('users', $users);
$smarty->assign('userclasses', $userclasses);
$smarty->display('userPermissions.tpl');

?>