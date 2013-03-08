<?php

include_once('config.inc.php');

import('factory');
import('user');
import('request');
import('dbconn');
import('utility');
import('calendar');

/******************************************\
* Created on Jul 21, 2008                  *
********************************************
* This script handles assignment of        *
* individual appointments, creating and    *
* setting sessions.  For example, clients  *
* can request appointment times,           *
* counselors can set recurring             *
* availabilities, and administrators can   * 
* confirm sessions.                        *
\******************************************/

// need this for pathnames
$cfg = Factory::getConfig();
$smarty = Factory::getSmarty();

// get the current user
$user = Factory::getUser();
if ($user->isGuest()) {
  Utility::redirect('index.php');
}

// determine the action required
if (Request::getSet('action', 'get')) {
  $action = Request::getString('action', 'get');  
} else {
  $action = Request::getString('action', 'post');
}

$handler = "";
switch ($action) {
  case 'create':
    $handler = 'create_appt';
    break;
  case 'cancel':
    $handler = 'cancel_appt';
    break;
  default: // if the user made up some action, send them back to the calendar
    Utility::redirect('calendar.php' . 
                     (Request::getSet('ahead', 'get') ? '?ahead=1' : ''));
    break;
}

// include the proper handler based on the permissions of the user
include_once($cfg->getValue('smrty_path') . 'templates/' . $cfg->getValue('smrty_tmpl') . 
             '/' . $user->getUserClass() . '/' . $handler . '_handler.php');

?>