<?php

defined('IC') or die('Restricted access');

/******************************************\
* Created on Jul 25, 2008                  *
********************************************
* This script is responsible for setting   *
* up the calendar view as required by the  *
* specified permissions level.             *
\******************************************/

// ready, steady, GO
$renderTime = Calendar::now();

// build a skeleton version of the Smarty month template for this user
$nextMonth = Request::getSet('ahead', 'get');
$month = Calendar::buildMonth($renderTime, $nextMonth);

// now, collect a bunch of information about the current time
$timestamp = Calendar::dateMySQL($renderTime);
$start = Calendar::getStartOfMonth($renderTime, $nextMonth);
$end = Calendar::getEndOfMonth($renderTime, $nextMonth);

$cfg = Factory::getConfig();

// now. here's the tricky part with the clients.
// there are a lot of restrictions clients have to look out for.
// this logic is going to be extremely tricky.
//
// 1) A timeslot is UNAVAILABLE if:
//    -it has only 0 or 1 counselors
//    -it has 2+ counselors, but 0 of the same gender as the client
//    (hint: we don't really need this, since unmarked slots for clients default to unavailable)
// 2) A timeslot is AVAILABLE if:
//    -it has 2+ counselors, at least 1 of which is same gender as client
// 3) A timeslot is SCHEDULED if:
//    -the client's userid appears in the session table
// 4) OVERRIDING ALL OF THESE - If a client has EXACTLY THREE sessions, or
//    ONE SESSION with "counselor1" and "counselor2", 
//    they CANNOT register for any more and all others will appear as UNAVAILABLE

// let's check #4 first...
$db = Factory::getDB();
$db->setQuery('SELECT * FROM session WHERE user_id = "' . $user->getUserID() . '" ' .
              'AND datetime > "' . $timestamp . '"');
$sessions = $db->loadRows();
$scheduled = array();
$trulyavailable = array();
// this is supposed to check if the user has either used up their 3-request
// quota, or has 1 actual confirmed appointment, in either case of which
// the user is not allowed to request any more appointments...but for some
// reason it seems to wipe everything out after a single appointment request

// The bug must be right here somewhere...
if (count($sessions) >= 1 && isset($sessions[0]['counselor1'])) {
  if (($sessions[0]['counselor1'] != "") ||
      (count($sessions) >= 2 && $sessions[1]['counselor1'] != "") ||
      (count($sessions) == 3 && $sessions[2]['counselor1'] != "")) {
    // JUST DO IT
    $trulyavailable = null;
  }
  
  // this will create the schedule for the client...whatever they've signed up for
  for ($i = 0; $i < count($sessions); $i++) {
    $scheduled[$i] = $sessions[$i]['datetime'];
  }
} else {
  $scheduled = null;
  // get what's available...

  $db->setQuery('SELECT * FROM available WHERE end_time >= "' . $start . '" AND ' .
                'start_time <= "' . $end . '"');
  $availables = $db->loadRows();

  // get the exceptions...
  $db->setQuery('SELECT * FROM exceptions WHERE datetime >= "' . $timestamp . 
                '" AND datetime <= "' . $end . '"');
  $results = $db->loadRows();
  
  $exceptions = array();
  for ($i = 0; $i < count($results); $i++) {
    $exceptions[$i] = $results[$i]['datetime'];
  }
  // and while we're at it, let's get the scheduled sessions
  $db->setQuery('SELECT * FROM session WHERE user_id = "' . $user->getUserID() . '"');
  $sessions = $db->loadRows();

  // now, we merge these together into something...remotely coherent
  // both of these arrays have to be straight timestamps
  $available = array();
  $counselors = array(); // a helper array
  $numAvailables = count($availables);
  for ($i = 0; $i < $numAvailables; $i++) {
    $arr = Calendar::getBetweenDates($availables[$i]['start_time'], $availables[$i]['end_time']);
    unset($arr[count($arr) - 1]); // torch the last one
    $numElems = count($arr);
    for ($j = 0; $j < $numElems; $j++) {
      if (!isset($available[$arr[$j]])) {
        $available[$arr[$j]] = 1;
      } else {
        $available[$arr[$j]]++;
      }
      // have the $counselors array mirror the $available array with indices
      $counselors[$arr[$j]][$availables[$i]['user_id']] = 1;
    }
  }

  $trulyavailable = array();

  // do some debugging
  $cfg->fireDebugOutput('Availabilities', $available);
  $cfg->fireDebugOutput('Counselors', $counselors);

  // this is sadly inefficient, but it's the best way to get this done...
  // we must loop through the $available array, tallying up any counselor
  // counts that are 2+ and contain at least 1 of the correct gender.
  // THEN, any hits we have there, we must check against the session
  // table.  if that username does NOT come up as counselor1 or
  // counselor2 for a session at that exact time, then we can finally
  // and confidently put that counselor's name down as a DEFINITE...possibility.
  $db->setQuery('SELECT gender FROM user WHERE email = "' . $user->getUserID() . '"');
  $gender = $db->loadRow();
  foreach ($available as $key => $value) {
    $yes = false;
    if ($value >= 2) {
      // 1 - do any of these counselors have exceptions for today?
      $db->setQuery('SELECT * FROM exceptions WHERE datetime = "' . $key . '"');
      $results = $db->loadRows();
      for ($j = 0; $j < count($results); $j++) {
        if (isset($results[$j]) && isset($results[$j]['e_id'])) {
          $value--;
          unset($counselors[$key][$results[$j]['user_id']]);
        }
      }
    
      // 2 - are any of these counselors already signed up for sessions?
      $db->setQuery('SELECT counselor1, counselor2 FROM session WHERE ' .
                    'datetime = "' . $key . '"');
      $results = $db->loadRows();
      for ($j = 0; $j < count($results); $j++) {
        if (isset($results[$j]) && isset($results[$j]['counselor1']) && 
            isset($results[$j]['counselor2'])) {
          // two more counselors off the total
          $value -= 2;
          unset($counselors[$key][$results[$j]['counselor1']]);
          unset($counselors[$key][$results[$j]['counselor2']]);
        }
      }
    
      // 3 - test to make sure one of these counselors is the right gender
      foreach ($counselors[$key] as $id => $num) {
        $db->setQuery('SELECT gender FROM user WHERE email = "' . $id . '"');
        $result = $db->loadRow();
        if ($result['gender'] == $gender['gender']) {
          // hoorays!...
          if ($value >= 2) { 
            // probably not the case anymore
            // but if we get here, we have a goooooooooaaaaaalllllllllll
            $trulyavailable[count($trulyavailable)] = $key;
          }
        }
      }
    }
  }
}
    
// FINALLY. merge everything together
$month = Calendar::finalizeAppts($month, $nextMonth, null, $scheduled, null, $trulyavailable, Calendar::$UNAVAILABLE);

// debugging
$days = array(array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 
                    'Friday', 'Saturday'));
$cfg->fireDebugOutput('Month Template Variable', array_merge($days + $month));

// assign the month...this comes from the appropriate render_calendar.php file
$smarty = Factory::getSmarty();
$smarty->assign('month', $month);

?>