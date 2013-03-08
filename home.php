<?php
include_once('config.inc.php');

import('factory');
import('user');
import('request');
import('calendar');

/******************************************\
* Created on Jul 9, 2008                   *
********************************************
* This is the home for the user.  As sort  *
* of an editable heads-up display, this    *
* will mirror the user's most current      *
* information, including personal and      *
* appointment information, and allow the   *
* user to edit that info directly.         *
\******************************************/

// first, get the active user
$user = Factory::getUser();

if ($user->isGuest()) { // LOL...nice try, home boy
  Utility::redirect('index.php');
}

// Redirection from SSL
$cfg = Factory::getConfig();
if (Request::getString('HTTPS', 'server') && $cfg->getValue('ssl_enabled')) {
  Utility::redirect('http://' . Request::getString('SERVER_NAME', 'server') .
                                Request::getString('SCRIPT_NAME', 'server'));
}

// populate their profile with information
$db = Factory::getDB();
$db->setQuery('SELECT * FROM user WHERE email = "' . $user->getUserID() . '"');
$data = $db->loadRow();
$smarty = Factory::getSmarty();
$smarty->assign('first', $data['first_name']);
$smarty->assign('last', $data['last_name']);
$smarty->assign('cellphone', $data['cell_phone']);
$smarty->assign('homephone', $data['home_phone']);
$smarty->assign('street', $data['street']);
$smarty->assign('city', $data['city']);
$smarty->assign('state', $data['state']);
$smarty->assign('email', $data['email']);

/** HUD stuff doesn't work...oy

//Assign the Values to their HUD
$userEmail=$user->getUserID();
//get the scheduled appts that involve the user (a client or a cousnelor)
$db->setQuery('SELECT datetime FROM session WHERE user_id = "'.$userEmail.'" OR counselor1 = "'.$userEmail.'" OR counselor2 = "'.$userEmail.'"');
$scheduledAppts=$db->loadRow();
$times=array();
foreach($scheduledAppts as $S)
{
	$times[]=$S['datetime'];
}
	
//get the exceptions so they can be displayed as well
$db->setQuery('SELECT datetime FROM exceptions WHERE user_id = "'.$userEmail.'"');
$exceptions=$db->loadRow();
foreach($exceptions as $S)
{
	$exceptTimes[]=$S['datetime'];
}
//Now I have two arrays, $times and $exceptTimes, that represent the mysql datetimes or the upcoming scheduled appts and the upcoming exceptions. So Ill smarty assign them :)
$smarty->assign('upcomingAppts', $times);
$smarty->assign('upcomingExceptions', $exceptTimes);

End nonfunctional HUD stuff **/

if ($user->getUserClass() == 'administrator') {
  include_once($cfg->getValue('smrty_path') . 'templates/' . $cfg->getValue('smrty_tmpl') . 
              '/' . $user->getUserClass() . '/homeAdmin.php');
  $smarty->display('homeAdmin.tpl');
} else {
  $smarty->display('home.tpl');
}
?>
