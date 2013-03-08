<?php

defined('IC') or die('Restricted access');

/******************************************\
* Created on Jul 22, 2008                  *
********************************************
* This script handles the cancelation of   *
* appointments from the perspective of the *
* standard user.  This will most likely    *
* occur the most often.                    *
\******************************************/

$db = Factory::getDB();
$smarty = Factory::getSmarty();
$cfg = Factory::getConfig();

// has the form been submitted?
if (Request::getSet('submitted', 'post')) {
  // quick check - did the user actually want to delete this appointment?
  if (Request::getString('cancel', 'post') == 'no') {
    $smarty->assign('title', 'No Changes submitted');
    $smarty->assign('message', 'Your appointments were not changed.');
    $smarty->assign('url', 'calendar.php');
    $smarty->assign('urltext', 'Go to the Calendar');
    $smarty->display('redirectmsg.tpl');
    exit;
  }
  
  // it has!  yay!  get the info needed to complete the request
  $month = Request::getSet('ahead', 'post');
  $day   = Request::getInt('day', 'post');
  $appt  = Request::getInt('hour', 'post');
  
  // now, build a date out of this combination
  $timestamp = Calendar::buildDateFromCalendar($day, $appt, $month);
  
  // using this timestamp, we'll simply query the session table for 
  // any session that is in process at this particular time with this
  // particular user, and if that exists, disband it
  
  // oh, and send out some reminder emails to everyone involved...
  $query = 'SELECT * FROM session WHERE datetime = "' . $timestamp . '" ' .
           'AND user_id = "' . $user->getUserID() . '"';
  $cfg->fireDebugOutput('Query', $query);
  $db->setQuery($query);
  $session = $db->loadRow();
  if (isset($session[0]['datetime'])) {
    // query successful, wipe it out
    $db->setQuery('DELETE FROM session WHERE session_id = ' . $session[0]['session_id']);
    if ($db->query()) {
      // successful deletion, redirect the user
      $smarty->assign('title', 'Appointment Removed');
      $smarty->assign('message', 'You have successfully canceled your appointment.');
      $smarty->assign('url', 'calendar.php');
      $smarty->assign('urltext', 'Go to the Calendar');
      $smarty->display('redirectmsg.tpl');
      exit;
    } else {
      Error::errorMsg('A database error has occurred.', 'Query: ' . $db->getActiveQuery() .
                      '<br />MySQL: ' . $db->getError());
    }
  } else {
    // error!
    Error::errorMsg('The appointment you requested to cancel was not found.  ' .
                    'Please double-check the time and try again.', '', 'regError.tpl');
  }
}

// set up all that smarty crap
$date = Calendar::buildDateFromCalendar(Request::getInt('day', 'get'),
                                        Request::getInt('appt', 'get'),
                                        Request::getSet('ahead', 'get'));
// bunches of date stuff
$unixdate = strtotime($date);
$smarty->assign('date', date('F j', $unixdate));
$smarty->assign('dayname', date('l', $unixdate));
$smarty->assign('time', date('g:00a', $unixdate));
$smarty->display($user->getUserClass() . '/cancelappointment.tpl');

?>