<?php

defined('IC') or die('Restricted access');

/******************************************\
* Created on Jul 22, 2008                  *
********************************************
* Handles setting recurring availabilities *
* by the counselors.                       *
\******************************************/

$date = null;

// first, determine if the counselor has posted an availability
// time slot for themselves
if (Request::getSet('submitted', 'post')) {
  // snag the necessary information from the form
  // note: GET variables were placed in the form
  $month = Request::getSet('ahead', 'post');
  $day   = Request::getInt('day', 'post');
  $hour  = Request::getInt('hour', 'post');
  $weeks = Request::getInt('numweeks', 'post');
  
  // we have to make sure this is valid
  if ($weeks && intval($weeks) > 0) {
    $startdate = Calendar::buildDateFromCalendar($day, $hour, $month);
    $enddate   = Calendar::dateMySQL(strtotime("+" . $weeks . " weeks", strtotime($startdate)));
    $dates     = Calendar::getBetweenDates($startdate, $enddate);
  
    // debugging! yay!
    $cfg = Factory::getConfig();
    $cfg->fireDebugOutput('Starting date', $startdate);
    $cfg->fireDebugOutput('Ending date', $enddate);
    $cfg->fireDebugOutput('Dates in between', $dates);
    
    // now...we have somewhat of a predicament. even though the chance is 
    // miniscule that this would actually happen, we have to check for the
    // possibility that someome played with the URL and inserted a bogus date
    // for which this person was already scheduled.  there isn't any easy way
    // to do this, but we have to check the database against every single date
    // in the $dates array.
    $db = Factory::getDB();
    $db->setQuery('SELECT * FROM available WHERE user_id = "' . $user->getUserID() . '"');
    $appts = $db->loadRows();
    
    // there are two checks we need to make, one of which is significantly
    // more difficult than the other
    // 1) make sure none of the start_time or end_time fields match any of
    //    the dates in the $dates array
    // 2) provided #1 is true, do the more difficult check of ensuring the $dates
    //    array is not a subset of any tuple from the available table. we can
    //    do this by checking if the start_time is earlier than the first element
    //    of the $dates array and the end_time is after the last element of the
    //    array, and if so, building another array out of the tuple and making
    //    sure no element from the original is contained within the new one
    //
    // clear as mud? good
    $numAppts = count($appts);
    for ($i = 0; $i < $numAppts; $i++) {
      // check #1
      if (in_array($appts[$i]['start_time'], $dates) || 
          in_array($appts[$i]['end_time'], $dates)) {
        // no need to go any further
        Error::errorMsg('You already have an availability at this time!  Please ' .
                        'double-check that you are not signing up for a time ' . 
                        'you have already registered for.', '', 'regError.tpl');
      }
      
      // check #2
      if ($appts[$i]['start_time'] < $dates[0] && $appts[$i]['end_time'] > $dates[count($dates) - 1]) {
        $superset = Calendar::getBetweenDates($appts[$i]['start_time'],
                                              $appts[$i]['end_time']);
        // is the $dates array a subset of $superset?
        if (count(array_intersect($superset, $dates)) > 0) {
          Error::errorMsg('You have already specified a range which overlaps with this one!  Please ' .
                          'double-check that you are not signing up for a time you have already ' .
                          'registered for.', '', 'regError.tpl');
        }
      } 
    }
    // making it to this point means all the checks passed...whew

    $db->setQuery('INSERT INTO available (user_id, start_time, end_time) VALUES ("' . 
                  $user->getUserID() . '", "' . $startdate . '", "' . $enddate . '")');
    if ($db->query()) {
      $smarty->assign('title', 'Availability Submitted');
      $smarty->assign('message', 'Your weekly timetable has been successfully added.');
      $smarty->assign('url', 'calendar.php');
      $smarty->assign('urltext', 'Go to the Calendar');
      $smarty->display('redirectmsg.tpl');
      exit;
    } else {
      Error::errorMsg('A database error has occurred.', $db->getError());
    }
  } else {
    $date = Calendar::buildDateFromCalendar(Request::getInt('day', 'post'),
                                            Request::getInt('hour', 'post'),
                                            Request::getSet('ahead', 'post')); 
  }
}

// this check is made in case the user incorrectly submitted the form
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
$smarty->display($user->getUserClass() . '/appointment.tpl');

?>