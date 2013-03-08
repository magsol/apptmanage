<?php

defined('IC') or die('Restricted access');

/******************************************\
* Created on Jul 22, 2008                  *
********************************************
* Handles requesting a single appointment  *
* date and time.                           *
\******************************************/

$date = null;
$db = Factory::getDB();
$cfg = Factory::getConfig();

// first things first, has the form been submitted?
if (Request::getSet('submitted', 'post')) {
  // it has!  yay!  get the info needed to complete the request
  $month = Request::getSet('ahead', 'post');
  $day   = Request::getInt('day', 'post');
  $appt  = Request::getInt('times', 'post');
  
  // now, build a date out of this combination
  $timestamp = Calendar::buildDateFromCalendar($day, $appt, $month);
  
  // oy...we have to check this date's validity AGAIN.  DAMMIT.
  // alright. here's how this goes.
  // 1) grab any availabilities in the database, expanding start-end
  //    times that encompass the timestamp we possess ($timestamp)
  // 2) if >= 2 counselors overlap availability, then we can continue
  // 3) cut out any exceptions during this exact time period
  // 4) cut out any scheduled sessions with these counselors
  // 5) check the genders of the remaining counselors to see if one
  //    matches the gender of the client
  // 6) if there are still >= 2 counselors, great!  we have an appointment
  // otherwise, give the user an error message
  
  // BUT FIRST! check to make sure the user hasn't used up their quota of sessions
  $db->setQuery('SELECT * FROM session WHERE user_id = "' . $user->getUserID() . '" ' .
                'AND datetime > "' . $timestamp . '"');
  $sessions = $db->loadRows();
  $scheduled = array();
  if (count($sessions) >= 1) {
    if (($sessions[0]['counselor1'] != "") ||
        (count($sessions) >= 2 && $sessions[1]['counselor1'] != "") ||
        (count($sessions) == 3 && $sessions[2]['counselor1'] != "")) {
      // JUST DO IT
      Error::errorMsg('You are already registered for a session.  Please wait to ' .
                      'complete the session, or cancel it before you register for ' .
                      'another.', '', 'regError.tpl');
    }
  }
  
  // ok, now follow through with the numbers up thar
  $query = 'SELECT * FROM available WHERE start_time <= "' . $timestamp . '" ' . 
           'AND end_time >= "' . $timestamp . '"';
  $db->setQuery($query);
  $availables = $db->loadRows();
  $cfg->fireDebugOutput('Query', $query);
  $cfg->fireDebugOutput('Availabilities', $availables);
  // get the exceptions...
  $db->setQuery('SELECT * FROM exceptions WHERE datetime = "' . $timestamp . '"');
  $exceptions = $db->loadRows();

  $numCounselors = 0;
  $counselors = array();
  $len = count($availables);
  // now run through all the availabilities and expand them to see
  // if any match up with this current timestamp
  for ($i = 0; $i < $len; $i++) {
    $arr = Calendar::getBetweenDates($availables[$i]['start_time'], $availables[$i]['end_time']);
    for ($j = 0; $j < count($arr); $j++) {
      if ($arr[$j] == $timestamp) {
        // this counts as an availability!...though even if it's just canceling
        // out the exceptions above, it's still weighted in the right direction
        // store this counselor so we can look up his/her stats later...
        $counselors[$numCounselors++] = $availables[$i];
      }
    }
  }
  $cfg->fireDebugOutput('Counselors after availability', $counselors);
  
  // run through the exceptions
  $len = count($exceptions);
  for ($i = 0; $i < $len; $i++) {
    for ($j = 0; $j < $numCounselors; $j++) {
      if ($exceptions[$i]['user_id'] == $counselors[$j]['user_id']) {
        // one of our counselors set an exception for this date...REMOVE
        unset($counselors[$j]);
        $numCounselors--;
        $j = 0;
      }
    }
  }
  $cfg->fireDebugOutput('Counselors after exceptions', $counselors);
  
  // alrighty, we've cross-referenced availabilities with exceptions. what's
  // left?
  // ah yes. sessions.
  $db->setQuery('SELECT * FROM session WHERE datetime = "' . $timestamp . '"');
  $sessions = $db->loadRows();
  for ($i = 0; $i < count($sessions); $i++) {
    for ($j = 0; $j < count($counselors); $j++) {
      if ($counselors[$j]['user_id'] == $sessions[$i]['counselor1'] || 
          $counselors[$j]['user_id'] == $sessions[$i]['counselor2']) {
        // if we have a match with a counselor already in a session,
        // delete this counselor from the running
        $numCounselors--;
        unset($counselors[$j]);
        $j = 0;      
      }
    }
  }
  $cfg->fireDebugOutput('Counselors after sessions', $counselors);
  
  // h'okay, let's check on the genders of any remaining counselors
  $gender = false;
  for ($i = 0; $i < $numCounselors; $i++) {
    $db->setQuery('SELECT gender FROM user WHERE email = "' . $counselors[$i]['user_id'] . '"');
    $counselorgender = $db->loadRow();
    $db->setQuery('SELECT gender FROM user WHERE email = "' . $user->getUserID() . '"');
    $usergender = $db->loadRow();
    if ($counselorgender['gender'] == $usergender['gender']) {
      $gender = true; 
    }
  }
  
  // FINALLY.  Check?
  if ($gender && $numCounselors >= 2) {
    // yaaaaay
    // commit the information for this session, and get the hell out of here
    $db->setQuery('INSERT INTO session (user_id, datetime) VALUES ("' . 
                  $user->getUserID() . '", "' . $timestamp . '")');
    if ($db->query()) {
      // success!
      $smarty->assign('title', 'Session Request Submitted');
      $smarty->assign('message', 'You have successfully submitted a session request to the administrator.');
      $smarty->assign('url', 'calendar.php');
      $smarty->assign('urltext', 'Go to the Calendar');
      $smarty->display('redirectmsg.tpl');
      exit;
    } else {
      // whoops
      Error::errorMsg('A database error has occurred.', 'Query: ' . $db->getActiveQuery() . 
                      '<br />MySQL: ' . $db->getError()); 
    }
  } else {
    // do some debugging...
    $cfg->fireDebugOutput('Gender Match', ($gender ? 'true' : 'false'));
    $cfg->fireDebugOutput('Counselors array', $counselors);
    // display the error message
    Error::errorMsg('You have tried to register for an appointment time which is not available. ' .
                    'Please double-check your appointment time and try again.', '', 'regError.tpl');
  }
}

if (!$date) {
  $date = Calendar::buildDateFromCalendar(Request::getInt('day', 'get'),
                                          Request::getInt('appt', 'get'),
                                          Request::getSet('ahead', 'get'));
}

// assign all the necessary stuffs
$unixdate = strtotime($date);
$smarty->assign('date', date('F j', $unixdate));
$smarty->assign('dayname', date('l', $unixdate));
$smarty->assign('time', date('g:00a', $unixdate));
$smarty->assign('hours', array(9, 12, 3, 6));
$smarty->assign('hours_txt', array('9:00am', '12:00pm', '3:00pm', '6:00pm'));
$smarty->display($user->getUserClass() . '/appointment.tpl');

?>
