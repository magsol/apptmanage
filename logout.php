<?php
include_once('config.inc.php');

import('factory');
import('user');

/******************************************\
* Created on Jul 18, 2008                  *
********************************************
* This script simply logs the user out of  *
* their active session, terminating login  *
* credentials, deleting cookies, and       *
* redirecting the user to the login page.  *
\******************************************/

// get the active user
$user = Factory::getUser();

// is this a trick?  kind of like Chris saying he's straight?
if ($user->isGuest()) {
  Utility::redirect('index.php');
}

// if we get to this point, the user is logged in...let's fix that
$session = $user->getSession();
$session->destroy();

// message the user and provide an automatic redirect
$smarty = Factory::getSmarty();
$smarty->assign('title', 'Logged Out');
$smarty->assign('message','You have been logged out.');
$smarty->assign('url', 'index.php');
$smarty->assign('urltext', 'Return to Login');
$smarty->display('redirectmsg.tpl');

?>