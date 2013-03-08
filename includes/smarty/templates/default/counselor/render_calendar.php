<?php

defined('IC') or die('Restricted access');

/******************************************\
* Created on Jul 25, 2008                  *
********************************************
* This script is responsible for setting   *
* up the calendar view as required by the  *
* specified permissions level.             *
\******************************************/

// set the $offset variable to reflect the current user's preferred offset (set when the user signs up)
$renderTime = Calendar::now(); 

//pass the user's timestamp and the boolean about which month is being rendered
$month = Calendar::buildMonth($renderTime, Request::getSet('ahead', 'get'));

// use the $month variable
$cfg = Factory::getConfig();

// convert the render time to a MySQL timestamp
$timestamp = Calendar::dateMySQL($renderTime);
$nextMonth = Request::getSet('ahead', 'get');
$start = Calendar::getStartOfMonth($renderTime, $nextMonth);
$end   = Calendar::getEndOfMonth($renderTime, $nextMonth);

// when is the counselor available?
$db = Factory::getDB();
$db->setQuery('SELECT * FROM available WHERE end_time >= "' . $start . '" AND ' .
              'start_time <= "' . $end . '" AND user_id = "' . 
              $user->getUserID() . '"');
$results = $db->loadRows();
$availabilities = array();
for ($i = 0; $i < count($results); $i++) {
  $arr = Calendar::getBetweenDates($results[$i]['start_time'], $results[$i]['end_time']);
  unset($arr[count($arr) - 1]); // destroy the last date, which doesn't really count
  $availabilities = array_merge($availabilities, $arr);
}

// a little debug output
$cfg->fireDebugOutput('Availabilities for ' . $user->getUserID(), $availabilities);

// get the exceptions
$db->setQuery('SELECT * FROM exceptions WHERE datetime >= "' . $start . '" AND ' .
              'datetime <= "' . $end . '" AND user_id = "' . $user->getUserID() . '"');
$results = $db->loadRows();
$exceptions = array();
for ($i = 0; $i < count($results); $i++) {
  $exceptions[$i] = $results[$i]['datetime'];
}

// more debugging output
$cfg->fireDebugOutput('Exceptions for ' . $user->getUserID(), $exceptions);

// merge everything together...oy
$month = Calendar::finalizeAppts($month, $nextMonth, null, $availabilities, 
                                 $exceptions, null, Calendar::$AVAILABLE);

// debugging
$days = array(array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 
                    'Friday', 'Saturday'));
$cfg->fireDebugOutput('Month Template Variable', array_merge($days + $month));

// assign the month...this comes from the appropriate render_calendar.php file
$smarty = Factory::getSmarty();
$smarty->assign('month', $month);

?>