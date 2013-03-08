<?php

defined('IC') or die('Restricted access');

/******************************************\
* Created on Jul 22, 2008                  *
********************************************
* This script handles the cancelation of   *
* appointments from the perspective of the *
* counselor.  This will most likely happen *
* in the event of a sudden conflict of     *
* scheduling.                              *
\******************************************/

$smarty = Factory::getSmarty();
$db = Factory::getDB();

// first, has this form been submitted?
if (Request::getSet('submitted', 'post')) {
  // get the information from the form
  $month = Request::getSet('ahead', 'post');
  $day   = Request::getInt('day', 'post');
  $hour  = Request::getInt('hour', 'post');
  $type  = Request::getString('cancel', 'post');
  $reinstate = Request::getString('reinstate', 'post');
  
  // first, let's build a timestamp from the day that was canceled
  $timestamp = Calendar::buildDateFromCalendar($day, $hour, $month);
  
  // now...if the user opted to create an exception, our job is easy.
  // we just have to make sure there isn't already an exception defined
  // for this user at this specific time, and then provided this is the case,
  // insert this date into the exceptions table.
  if ($type == 'one') {
    // first, check to make sure an exception like this doesn't already exist
    $db->setQuery('SELECT e_id FROM exceptions WHERE user_id = "' . 
                  $user->getUserID() . '" AND datetime = "' . $timestamp . '"');
    $result = $db->loadRow();
    if (isset($result['e_id'])) {
      Error::errorMsg('You have already defined this time as an exception.  Please ' .
                      'make sure you are choosing the correct day and time to cancel.',
                      '', 'regError.tpl');
    }
    // good, let's insert this exception
    $db->setQuery('INSERT INTO exceptions (user_id, datetime) VALUES ("' . 
                  $user->getUserID() . '", "' . $timestamp . '")');
    if ($db->query()) {
      $smarty->assign('title', 'Exception created');
      $smarty->assign('message', 'Your single appointment cancelation has been noted.');
      $smarty->assign('url', 'calendar.php');
      $smarty->assign('urltext', 'Go to the Calendar');
      $smarty->display('redirectmsg.tpl');
      exit;
    } else {
      // an error occurred
      Error::errorMsg('A database error has occurred.', 'Query: ' . 
                      $db->getActiveQuery() . '<br />MySQL: ' . $db->getError()); 
    }
  } else if ($type == 'all') {
    // this is a bit more complicated: the user wants to cancel an
    // entire range of availability.
    
    // our first step, then, is to figure out exactly what range of 
    // dates the user is specifying.  Our hints are as follows:
    // -the user's username (email address)
    // -the day of the week of the cancelation
    // -the time (hour) of the cancelation
    //
    // we have to use these three parameters to figure out what tuple
    // in the availability table the user wishes to delete...
    $db->setQuery('SELECT * FROM available WHERE user_id = "' . $user->getUserID() . '"');
    $availables = $db->loadRows();
    
    // loop through all the availabilities, generating all the dates in 
    // between the start and end, and see if the current timestamp matches
    // any of them
    $numSchedules = count($availables);
    for ($i = 0; $i < $numSchedules; $i++) {
      $arr = Calendar::getBetweenDates($availables[$i]['start_time'], 
                                       $availables[$i]['end_time']);
      if (in_array($timestamp, $arr)) {
        // we found our needle!
        // delete this entry from the available table
        $db->setQuery('DELETE FROM available WHERE avail_id = ' . $availables[$i]['avail_id']);
        if ($db->query()) {
          // success!
          $smarty->assign('title', 'Appointments deleted');
          $smarty->assign('message', 'All your appointments at this day and time have been canceled.');
          $smarty->assign('url', 'calendar.php');
          $smarty->assign('urltext', 'Go to the Calendar');
          $smarty->display('redirectmsg.tpl');
          exit;
        } else {
          // crap on a stick
          Error::errorMsg('An database error has occurred.', 'Query: ' . 
                          $db->getActiveQuery() . '<br />MySQL: ' . $db->getError());
        }
      }
    }
    // we did not find our needle
    // just in case, let's add some debugging...
    $cfg = Factory::getConfig();
    $cfg->fireDebugOutput('Availabilities', $availables);
    Error::errorMsg('The appointment you specified to cancel does not seem ' .
                    'to exist in the first place.  If you believe this ' .
                    'to be in error, please contact the site administrator.');
    
  } else {
    // first, let's see if the user submitted a deletion of an exception
    if (Request::getSet('reinstate', 'post') && $reinstate == 'yes') {
      $db->setQuery('DELETE FROM exceptions WHERE datetime = "' . $timestamp . '" ' .
                    'AND user_id = "' . $user->getUserID() . '"');
      if ($db->query()) {
        $smarty->assign('title', 'Exception deleted');
        $smarty->assign('message', 'You are now listed as available for this time slot.');
        $smarty->assign('url', 'calendar.php');
        $smarty->assign('urltext', 'Go to the Calendar');
        $smarty->display('redirectmsg.tpl');
        exit;
      } else {
        // this database has a sad case of the stupid virus
        Error::errorMsg('An error has been encountered with the database.', 
                        'Query: ' . $db->getActiveQuery() . '<br />MySQL: ' .
                        $db->getError());
      }
    } else if (Request::getSet('reinstate', 'post') && $reinstate == 'no') {
      $smarty->assign('title', 'No Changes Made');
      $smarty->assign('message', 'Your current appointment schedule has not been changed.');
      $smarty->assign('url', 'calendar.php');
      $smarty->assign('urltext', 'Go to the Calendar');
      $smarty->display('redirectmsg.tpl');
      exit;
    } else {
      // user error...or something
      Error::errorMsg('You have improperly submitted this form.', 'User ' . 
                      'submitted form with "type" POST value of "' . $type . 
                      '" and a "reinstate" POST value of "' . $reinstate . '"');
    }
    
  }
}

// this check is made in case the user incorrectly submitted the form
$date = Calendar::buildDateFromCalendar(Request::getInt('day', 'get'),
                                        Request::getInt('appt', 'get'),
                                        Request::getSet('ahead', 'get'));

$unixdate = strtotime($date);
$smarty->assign('date', date('F j', $unixdate));
$smarty->assign('dayname', date('l', $unixdate));
$smarty->assign('time', date('g:00a', $unixdate));

// which template do we show?
$db->setQuery('SELECT e_id FROM exceptions WHERE user_id = "' . $user->getUserID() . '"');
$result = $db->loadRow();
if (isset($result['e_id'])) {
  $smarty->display($user->getUserClass() . '/reinstateappointment.tpl');  
} else {
  $smarty->display($user->getUserClass() . '/cancelappointment.tpl');
}

?>