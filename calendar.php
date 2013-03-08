<?php

include_once('config.inc.php');

import('factory');
import('request');
import('calendar');
import('dbconn');
import('user');

/******************************************\
* Created on Jul 20, 2008                  *
********************************************
* This page handles all calendar activity, *
* from displaying the entire month to      *
* individual dates and registration for    *
* appointments.                            *
\******************************************/

//get the current user
$user = Factory::getUser();

//check to make sure the user is an actual user. 
if ($user->isGuest()) { // LOL...nice try, home boy
  Utility::redirect('index.php');
}

// customize how the calendar is filled out
$cfg = Factory::getConfig(); 
include_once($cfg->getValue('smrty_path') . 'templates/' . $cfg->getValue('smrty_tmpl') . 
             '/' . $user->getUserClass() . '/' . 'render_calendar.php');

// done and done
$smarty = Factory::getSmarty();
$smarty->assign('nextMonthYear', date('F Y', strtotime('+1 month')));
$smarty->display('calendar.tpl');

?>