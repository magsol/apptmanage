<?php

include_once('config.inc.php');

import('user');
import('factory');
import('request');
import('dbconn');
import('utility');
import('calendar');

/******************************************\
* Created on Jul 30, 2008                  *
********************************************
* This script handles the assignment and   *
* confirmation of sessions between clients *
* and counselors.  This is another page    *
* that only the administrator can access.  *
\******************************************/

$db = Factory::getDB();
$smarty = Factory::getSmarty();
$cfg = Factory::getConfig();

// first, is the user logged in?
$user = Factory::getUser();
if ($user->isGuest()) {
  Utility::redirect('index.php');
}

// second, are they an administrator?
if ($user->getUserClass() != 'administrator') {
  Utility::redirect('home.php');
}

// third...has the page been submitted?
if (Request::getSet('submitted', 'post')) {
  // heck to make sure that the same counselor wasn't assigned twice
  // to the same appointment...that wouldn't work in the least
  if ((Request::getSet('9counselor1', 'post') && 
       Request::getString('9counselor1', 'post') != Request::getString('9counselor2', 'post')) ||
      (Request::getSet('12counselor1', 'post') &&
       Request::getString('12counselor1', 'post') != Request::getString('12counselor2', 'post')) ||
      (Request::getSet('3counselor1', 'post') && 
       Request::getString('3counselor1', 'post') != Request::getString('3counselor2', 'post')) ||
      (Request::getSet('6counselor1', 'post') && 
       Request::getString('6counselor1','post') != Request::getString('6counselor2', 'post'))) {
    // everything checks out, so let's commit these counselors to their
    // respective appointments in the database

    $result = true;
    if (Request::getSet('9counselor1', 'post')) {
      $db->setQuery('UPDATE session SET counselor1 = (SELECT email FROM user WHERE ' .
                    'userid = ' . Request::getInt('9counselor1', 'post') . '), counselor2 = ' .
                    '(SELECT email FROM user WHERE userid = ' . 
                    Request::getInt('9counselor2', 'post') . ') WHERE session_id = ' .
                    Request::getInt('nineid', 'post'));
      $result = $result && $db->query();
      
      // pull the email
      $db->setQuery('SELECT user_id, counselor1, counselor2, datetime FROM session WHERE ' .
                    'session_id = ' . Request::getInt('nineid', 'post'));
      $result = $db->loadRow();
      // send the three emails - first to the client, last two to the counselors
      mailThree($result['user_id'], $result['counselor1'], $result['counselor2'], $result['datetime']);

    }
    if (Request::getSet('12counselor1', 'post')) {
      $db->setQuery('UPDATE session SET counselor1 = (SELECT email FROM user WHERE ' .
                    'userid = ' . Request::getInt('12counselor1', 'post') . '), ' .
                    'counselor2 = (SELECT email FROM user WHERE userid = ' .
                    Request::getInt('12counselor2', 'post') . ') WHERE session_id = ' .
                    Request::getInt('twelveid', 'post'));
      $result = $result && $db->query();
      
      // pull the email
      $db->setQuery('SELECT user_id, counselor1, counselor2, datetime FROM session WHERE ' .
                    'session_id = ' . Request::getInt('twelveid', 'post'));
      $result = $db->loadRow();
      // send the three emails - first to the client, last two to the counselors
      mailThree($result['user_id'], $result['counselor1'], $result['counselor2'], $result['datetime']);
    }
    if (Request::getSet('3counselor1', 'post')) {
      $db->setQuery('UPDATE session SET counselor1 = (SELECT email FROM user WHERE ' .
                    'userid = ' . Request::getInt('3counselor1', 'post') . '), ' . 
                    'counselor2 = (SELECT email FROM user WHERE userid = ' .
                    Request::getInt('3counselor2', 'post') . ') WHERE session_id = ' .
                    Request::getInt('threeid', 'post'));
      $result = $result && $db->query();
      
      // pull the email
      $db->setQuery('SELECT user_id, counselor1, counselor2, datetime FROM session WHERE ' .
                    'session_id = ' . Request::getInt('threeid', 'post'));
      $result = $db->loadRow();
      // send the three emails - first to the client, last two to the counselors
      mailThree($result['user_id'], $result['counselor1'], $result['counselor2'], $result['datetime']);
    }
    if (Request::getSet('6counselor1', 'post')) {
      $db->setQuery('UPDATE session SET counselor1 = (SELECT email FROM user WHERE ' .
                    'userid = ' . Request::getInt('6counselor1', 'post') . '), ' .
                    'counselor2 = (SELECT email FROM user WHERE userid = ' .
                    Request::getInt('6counselor2', 'post') . ') WHERE session_id = ' .
                    Request::getInt('sixid', 'post'));
      $result = $result && $db->query();
      
      // pull the email
      $db->setQuery('SELECT user_id, counselor1, counselor2, datetime FROM session WHERE ' .
                    'session_id = ' . Request::getInt('sixid', 'post'));
      $result = $db->loadRow();
      // send the three emails - first to the client, last two to the counselors
      mailThree($result['user_id'], $result['counselor1'], $result['counselor2'], $result['datetime']);
    }
    
    // check the return status...
    if ($result) {
      // show the success message
      $smarty->assign('title', 'Appointments set!');
      $smarty->assign('message', 'You have successfully set the appointment times with counselors.');
      $smarty->assign('url', 'home.php');
      $smarty->assign('urltext', 'Go to your home page');
      $smarty->display('redirectmsg.tpl');
      exit; // kill execution for this particular page, and let the redirect take over
    } else {
      Error::errorMsg('A database error has occurred.');
    }
  }
}

// if we've reached this point in the script, it either means the page was not
// submitted, or the user assigned the same counselor twice to a single session

// Here's how we have to do this
// 1) Retrieve all the entries in the sessions table which have BLANK counselor1
//    and counselor2 fields.  This means those particular sessions are UNCONFIRMED.
// 2) We can infer from the presence of those entries that there are counselors
//    available which meet the necessary criteria to set up an appointment.  
//    Nevertheless, we need to double-check that information, in case a counselor
//    has canceled their availability.  Therefore, for *every* unconfirmed session,
//    we need to grab all the entries in the available table where the session
//    time falls in between the start_time and end_time of the available entry.
// 3) For each entry in the available result (second "for" loop), we need
//    to deconstruct the start_time and end_time into distinct dates, one per week.
// 4) Looping through each of those dates in the week (third "for" loop), if
//    one matches the ORIGINAL SESSION DATE, we have found a POSSIBLE counselor
//    for that time slot.  There's one more check we have to make, though...
// 5) Query the exceptions table for any exceptions defined on this particular
//    day.  If we don't find one, or if the exceptions we find don't have 
//    user_ids that match the counselor we're looking at, then we can proceed!
// 6) Everything checks out; we need to add this counselor to the list
//    of possible candidates for that time slot.
// 7) Wash, rinse, repeat! woot

// when are all the blank sessions? this means they are UNCONFIRMED
// if they have blank counselor1 and counselor2 fields
$db->setQuery('SELECT * FROM session WHERE counselor1 = "" AND ' . 
              'counselor2 = ""');
$sessions = $db->loadRows();
$numSessions = count($sessions);
// loop through all the sessions, comparing their dates to the ones
// of counselor availabilities
$counselors = array();
$requests = array();
for ($i = 0; $i < $numSessions; $i++) {
  $query = 'SELECT * FROM available, user WHERE start_time <= "' . $sessions[$i]['datetime'] . 
           '" AND end_time >= "' . $sessions[$i]['datetime'] . '" AND available.user_id = ' . 
           'user.email';
  $db->setQuery($query);
  $availables = $db->loadRows();
  $numAvailables = count($availables); // count all the availabilities
  $cfg->fireDebugOutput('Query', $query);
  $cfg->fireDebugOutput('Result', $availables);
  
  // create these four array variables for all the counselors that are available
  // at the four separate timeslots.  these will be assigned upon the completion
  // of the following loops for every unconfirmed session.
  $ninecounselors = array();
  $twelvecounselors = array();
  $threecounselors = array();
  $sixcounselors = array();
  
  // loop through these dates, expanding them
  for ($j = 0; $j < $numAvailables; $j++) {
    // expand the dates
    $arr = Calendar::getBetweenDates($availables[$j]['start_time'], $availables[$j]['end_time']);
    $numWeeks = count($arr);
    // now loop through these dates, looking for the session
    
    for ($k = 0; $k < $numWeeks; $k++) {
      if ($sessions[$i]['datetime'] == $arr[$k]) {
        // if the requested session matches with the available time,
        // check that the counselor has not defined an exception for that day
        $db->setQuery('SELECT * FROM exceptions WHERE datetime = "' . 
                      $arr[$k] . '" AND user_id = "' . $availables[$j]['user_id'] . '"');
        $except = $db->loadRow();
        if (!isset($except['e_id'])) {
          // WE HAVE A VALID APPOINTMENT
          // if there's no exception set for this date and time by this counselor
          $db->setQuery('SELECT first_name, last_name FROM user WHERE email = "' . 
                        $sessions[$i]['user_id'] . '"');
          $name = $db->loadRow(); 
          // set the userid in the $name array
          $name['user_id'] = $sessions[$i]['user_id'];
          $name['id'] = $sessions[$i]['session_id'];
          
          // switch on the time of day of this appointment
          // 1) assign the array with the client's information...this is
          //    $name, which has first_name, last_name, and user_id
          // 2) create the counselor arrays. this will be a bit more tricky.
          //    they must follow the structure:
          //
          //    array(counselorID => counselorName)
          //
          //
          $unixts = Calendar::dateUnix($sessions[$i]['datetime']);    
          switch(date('g', $unixts)) {
            case '9':
              $smarty->assign('date9', $unixts);
              $smarty->assign('nine', $name); // this may happen several times...no biggie
              $ninecounselors[$availables[$j]['userid']] = 
                              $availables[$j]['first_name'] . ' ' .
                              $availables[$j]['last_name'];
              break;
            case '12':
              $smarty->assign('date12', $unixts);
              $smarty->assign('twelve', $name);
              $twelvecounselors[$availables[$j]['userid']] = 
                                $availables[$j]['first_name'] . ' ' .
                                $availables[$j]['last_name'];
              break;
            case '3':
              $smarty->assign('date3', $unixts);
              $smarty->assign('three', $name);
              $threecounselors[$availables[$j]['userid']] =
                               $availables[$j]['first_name'] . ' ' .
                               $availables[$j]['last_name'];
              break;
            case '6':
              $smarty->assign('date6', $unixts);
              $smarty->assign('six', $name);
              $sixcounselors[$availables[$j]['userid']] = 
                             $availables[$j]['first_name'] . ' ' .
                             $availables[$j]['last_name'];
              break;
            default:
              // if execution gets here, something's wrong
              $cfg->fireDebugOutput('ERROR', 'Date given was "' . 
                                    date('g', $unixts . '"'));
              break;
          } // end switch statement
        } // end if that checks to make sure no exception exists for today
      } // end if that checks if today is a session and an availability
    } // end for loop through the expanded availability array
  } // end for loop through the list of all availabilities in a range
  // ASSIGN THE AVAILABLE COUNSELORS FOR THIS APPOINTMENT
  $smarty->assign('ninecounselors', $ninecounselors);
  $smarty->assign('twelvecounselors', $twelvecounselors);
  $smarty->assign('threecounselors', $threecounselors);
  $smarty->assign('sixcounselors', $sixcounselors);
} // end for loop through all the unconfirmed sessions

// the $requests array is now stored in such a fashion that calling
// count($requests) will yield the number of outstanding client appointment
// requests. still, we're not quite finished - we need to take gender into
// account

$cfg->fireDebugOutput('Counselors', $counselors);
$cfg->fireDebugOutput('Requests', $requests);

// set up the template
$smarty->assign('appts', $requests);
$smarty->display($user->getUserClass() . '/appointment.tpl');

/**
 * A bit of a utility function, specific to this file with the intent of
 * cutting down on what would otherwise be extremely repetitive (and most
 * likely buggy) code.
 * 
 * This function sends out the three reminder emails to the three respective
 * individuals comprising an appointment.  All modifications of the email
 * bodies should be made here.
 *
 * @access public
 * @param string $client
 * @param string $counselor1
 * @param string $counselor2
 * @param string $timestamp
 */
function mailThree($client, $counselor1, $counselor2, $timestamp) {
  
  // CLIENT email
  Utility::email($client, 'Appointment Set', "Hello,\n\n" .
                     "You have been confirmed for an appointment on " .
                     date('F j, Y', Calendar::dateUnix($timestamp)) .
                     " at " . date('g:ia', Calendar::dateUnix($timestamp)) . 
                     ".  If you have any questions, please contact David Smith, " .
                     "dmsmith@dms489.com.  Thank you.\n\n" .
                     "Regards,\nThe Sozo Team");

  // COUNSELOR 1 email
  $db = Factory::getDB();
  $db->setQuery('SELECT first_name, last_name FROM user WHERE email = "' . $client . '"');
  $result = $db->loadRow();
  Utility::email($counselor1, 'Appointment Set', "Hello,\n\n" .
                     "You have been confirmed for an appointment with " . 
							$result['first_name'] . " " . $result['last_name'] . " on " .
                     date('F j, Y', Calendar::dateUnix($timestamp)) .
                     " at " . date('g:ia', Calendar::dateUnix($timestamp)) . 
                     ".  If you have any questions, please contact David Smith, " .
                     "dmsmith@dms489.com.  Thank you.\n\n" .
                     "Regards,\nThe Sozo Team");

  // COUNSELOR 2 email
  Utility::email($counselor2, 'Appointment Set', "Hello,\n\n" .
                     "You have been confirmed for an appointment with " . 
							$result['first_name'] . " " . $result['last_name'] . " on " .
                     date('F j, Y', Calendar::dateUnix($timestamp)) .
                     " at " . date('g:ia', Calendar::dateUnix($timestamp)) . 
                     ".  If you have any questions, please contact David Smith, " .
                     "dmsmith@dms489.com.  Thank you.\n\n" .
                     "Regards,\nThe Sozo Team");
}

?>