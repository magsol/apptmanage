<?php

defined('IC') or die('Restricted access');

/**
 * This class is meant to assist with the creation and maintenance of a 
 * lightweight PHP calendar, providing wrappers for the basic PHP date
 * functionality and contributing to the rendering of the calendar.
 *
 * Created on Jul 19, 2008
 *
 * @package Introverted.Champions
 * @author Shannon Quinn
 * @version 0.5
 */

// ---------------------------------------------
// Revisions
// 0.1  - 7/19/08
//      - Begun
//      - Wrote getPrintableWeeks()
// 0.2  - Added tsMySQL
// 0.3  - Moved dateMySQL(), dateUnix() and now() from Utility class
// 0.4  - Improved documentation of getPrintableWeeks()
// 0.5  - 7/20/08
//      - Added getNumDays(), getFirstDay()
//      - Added buildMonth()
// 0.6  - 7/23/08
//      - Added buildDateFromCalendar()
// 0.7  - 7/24/08
//      - Eliminated $offset parameter from dateMySQL(), dateUnix(),
//        now(), and buildDateFromCalendar()
//      - Added static $timezone attribute
// 0.8  - 7/25/08
//      - Added getStartOfMonth()
//      - Added getEndOfMonth()
//      - Modified buildDateFromCalendar()
// 0.9  - 7/28/08
//      - Added getBetweenDates()
//      - Added finalizeAppts()
//      - Removed several broken functions
//      - Added UNAVAILABLE, AVAILABLE, SCHEDULED, and EXCEPTION attributes

class Calendar {
  
  /***********************\
  |   Static Attributes   |
  \***********************/
  
  /**
   * This is the constant used by the Utility class in generating
   * MySQL timestamps.
   * 
   * @static 
   * @access public
   * @var string $tsMySQL
   */
  static $tsMySQL = 'Y-m-d H:i:s';
  
  /**
   * This constant is the timezone offset for the base time of all users.
   * 
   * @static
   * @access public
   * @var int $timezone The offset from GMT of this timezone.
   */
  static $timezone = -5;
  
  /**
   * This is used in building the calendar data for Smarty to indicate if
   * a timeslot should be marked as unavailable.
   *
   * @static
   * @var int $UNAVAILABLE
   */
  static $UNAVAILABLE = 0;
  
  /**
   * This is used in building the calendar data for Smarty to indicate if
   * a timeslot should be marked as available.
   *
   * @static
   * @var int $AVAILABLE
   */
  static $AVAILABLE = 1;
  
  /**
   * This is used in building the calendar data for Smarty to indicate if
   * a timeslot should be marked as scheduled.
   *
   * @static
   * @access public
   * @var int $SCHEDULED
   */
  static $SCHEDULED = 2;
  
  /**
   * This is used in building the calendar data for Smarty to indicate if
   * a timeslot should be marked as an exception to recurring availability.
   *
   * @static
   * @access public
   * @var int $EXCEPTION
   */
  static $EXCEPTION = 3;
  
  /***********************\
  |       Functions       |
  \***********************/
  
  /**
   * This function calculates how many printable weeks are in a month.
   * 
   * Using the timestamp provided, this function determines that, if this
   * month were to be printed in a standard 7-day calendar format, how
   * many rows would be necessary to render all the days of the month.
   *
   * @static
   * @access public
   * @param int $time The Unix timestamp, representing the month to calculate.
   * @return int The number of printable weeks in that month.
   */
  public static function getPrintableWeeks($time) {
    $month = date('m', $time);
    $year  = date('Y', $time);
    $numDays = date('t', $time); // total number of days in this month
    
    // the day, 0-6, of the first day of the month
    $startIndex = date('w', strtotime($year . '-' . $month . '-01 13:13:13'));

    $numWeeks = 1; // this will track how many printable weeks we have
    $startDay = 7 - $startIndex; // how many days LEFT in this week
    for ($i = $startDay; $i <= $numDays; $i += 7) {
      if ($i + 7 > $numDays) {
        $lastIndex = date('w',
                     strtotime($year . '-' . $month . '-' . $numDays . ' 13:13:13'));
        $curIndex  = date('w',
                     strtotime($year . '-' . $month . '-' . $i . ' 13:13:13'));
        if ($lastIndex < $curIndex) {
          $numWeeks++;
        }
      } else {
        $numWeeks++;
      }
    }
    return $numWeeks;
  }
  
  /**
   * This function converts the parameter into a MySQL-style date format,
   * which is as follows:
   * 
   * YYYY-MM-DD HH:MM:SS
   * 
   * @static
   * @access public
   * @param mixed $date The date to be converted, in GMT
   * @return string A proper MySQL representation of the date.
   */
  public static function dateMySQL($date) {
    // convert to an integer form, if necessary
    if (!is_int($date)) {
      $date = strtotime($date);
    }

    // return the new date
    return date(self::$tsMySQL, $date);
  }
  
  /**
   * This function converts the parameter into an integer Unix-style
   * timestamp, with the specified timezone offset.
   *
   * @static
   * @access public
   * @param mixed $date The time to be converted, in GMT
   * @return int A Unix timestamp of the requested date.
   */
  public static function dateUnix($date) {
    // convert the string to a unix timestamp, if necessary
    if (!is_int($date)) {
      $time = strtotime($date);
    }
    
    // apply the offset and return the new timestamp
    return $time;
  }
  
  /**
   * This function builds a MySQL timestamp from the day, time, and month.
   * 
   * Using the information provided by the calendar interface through the URL,
   * this function constructs a MySQL timestamp to be used in database queries
   * that corresponds to the selected appointment time.
   * 
   * @static
   * @access public
   * @param int $day The day of the month selected.
   * @param int $time The appointment time of the selected day.
   * @param bool $month True if this is NEXT month, false if it is the current one.
   * @return string The MySQL timestamp corresponding to the appointment time.
   */
  public static function buildDateFromCalendar($day, $time, $month) {
    $current_time = self::now();
    if ($month) {
      $current_time = strtotime("+1 month", $current_time);
    }
    if ($day < 10) {
      $day = '0' . intval($day);
    }
    
    // military time
    if ($time == 3 || $time == 6) {
      $time += 12;
    } else if ($time == 9) {
      $time = '0' . intval($time);
    }
    
    // return the MySQL timestamp
    return date('Y-m-', $current_time) . $day . ' ' . $time . ':00:00';
  }
  
  /**
   * This function is an alias to PHP's time() function, a wrapper to 
   * provide this functionality through the Utility class.
   * 
   * @static
   * @access public
   * @return int The Unix timestamp of the current time.
   */
  public static function now() {
    // This creates a MySQL timestamp of the current time,
    // takes into account the timezone offset, and
    // converts it back into a unix timestamp
    
    // there must be a bug in PHP, as gmdate('U') returns exactly
    // the same value as date('U'), and that should *NOT* be the case!
    $utcStr = gmdate(self::$tsMySQL, time());
    $tzTime = strtotime(self::dateMySQL(strtotime($utcStr)));
    return $tzTime + ((self::$timezone + date('I')) * 3600);
  }
  
  /**
   * Returns the number of days in the month indicated by the timestamp.
   *
   * @static
   * @access public
   * @param mixed $time This can be a Unix timestamp, or a string for the date.
   * @return int The number of days in the month.
   */
  public static function getNumDays($time) {
    $ts = $time;
    if (!is_int($time)) {
      $ts = strtotime($ts);
    }
    // sends back the number of days in the month
    return date('t', $ts);
  }
  
  /**
   * Returns the day of the week that the first day of the month falls on.
   * 
   * This function uses the PHP date() function with the 'w' parameter, which
   * assumes a numbering system for the standard 7-day week: 0 for Sunday, 
   * all the way up to 6 for Saturday.  This function determines what day
   * the first day of a given month falls on, 0 through 6.
   *
   * @static
   * @access public
   * @param mixed $time This can be a Unix timestamp, or a string for the date.
   * @return int The day, 0 through 6, that is the first day of the month.
   */
  public static function getFirstDay($time) {
    $ts = $time;
    if (!is_int($time)) {
      $ts = strtotime($ts);
    }
    
    // decompose the date, so we can recompose it as the 1st of the month
    $year  = date('Y', $ts);
    $month = date('m', $ts);
    $hour  = date('H', $ts);
    $min   = date('i', $ts);
    $sec   = date('s', $ts);
    
    // recompose the date
    $newdate = date('Y-m-d H:i:s', strtotime($year . '-' . $month . '-01 ' .
                                             $hour . ':' . $min . ':' . $sec));
    // send back the first day
    return date('w', strtotime($newdate));
  }
  
  /**
   * This function builds an array representation of a month for use in Smarty.
   * 
   * Using the Unix timestamp representing the selected month (can be the
   * current month, or one month in the future), this function constructs
   * a 3D array to be used in the Smarty template responsible for rendering
   * the calendar.  It contains information about the start of the week, 
   *
   * @static
   * @access public
   * @param int $curMonth The timestamp representing the current month.
   * @param bool $nextMonth True if we want to render the next month, false otherwise. 
   * @return array A 3D array for the HTML representation of the month.
   */
  public static function buildMonth($curMonth, $nextMonth) {
    $retVal = array();
    
    // first things first...are we rendering *this* month, or *next* month?
    if ($nextMonth) {
      $curMonth = strtotime("+1 month", $curMonth);
    }
    
    // get the first day and the last day of this month
    $lastDay  = self::getNumDays($curMonth);
    $firstDay = self::getFirstDay($curMonth);
    $weeks    = self::getPrintableWeeks($curMonth);
    $curDay = -1;
    
    // start looping!
    for ($i = 0; $i < $weeks; $i++) {
      $retVal[$i] = array();
      // inner loop for each day of the week
      for ($j = 0; $j < 7; $j++) {
        // where do we start counting?
        if ($i == 0 && $j == $firstDay) {
          $curDay = 1;
        }
        // is this day an actual calendar day? or a blank space?
        if ($curDay > 0 && $curDay <= $lastDay) {
          // quick test for any days of the CURRENT month that have passed
          if (!$nextMonth && $curDay > 0 && date('j', $curMonth) > $curDay) {
            $retVal[$i][$j]['dayExists'] = 1;
            $retVal[$i][$j]['passed'] = 1;
            $retVal[$i][$j]['dayNum'] = $curDay++;
          } else {
            // this is either the future month, or the current month with a date
            // at or beyond the current one in the loop
            $retVal[$i][$j]['dayExists'] = 1;
            $retVal[$i][$j]['passed'] = 0;
            $retVal[$i][$j]['dayNum'] = $curDay++;
          }  
        } else { // this is a day that's off the calendar
          $retVal[$i][$j]['dayExists'] = 0;
          $retVal[$i][$j]['passed'] = 0;
        }
      } // end inner loop
    } // end outer loop
    
    // send back the month
    return $retVal;
  }
  
  /**
   * This returns a MySQL timestamp representing the first day of the given month.
   *
   * @static
   * @access public
   * @param int $curTime The Unix timestamp for the current time.
   * @param bool $nextMonth True if we want next month, false for the current one.
   * @return string A MySQL timestamp for day 1 of the specified month.
   */
  public static function getStartOfMonth($curTime, $nextMonth) {
    if ($nextMonth) {
      $curTime = strtotime("+1 month", $curTime);
    }
    
    return date('Y-m-', $curTime) . '01 00:00:00';
  }
  
  /**
   * This function returns a MySQL timestamp representing the last day of the month.
   *
   * @static
   * @access public
   * @param int $curTime The Unix timestamp for the current time.
   * @param bool $nextMonth True if we want next month, false otherwise.
   * @return string A MySQL timestamp for the last day of the month.
   */
  public static function getEndOfMonth($curTime, $nextMonth) {
    if ($nextMonth) {
      $curTime = strtotime("+1 month", $curTime);
    }
    
    return date('Y-m-', $curTime) . self::getNumDays($curTime) . ' 23:59:59';
  }
  
  /**
   * Finalizes the Smarty month data to be fed to the template.
   * 
   * This is probably one of the most complex functions in the Calendar
   * class, if not the appliation on a whole.  After a "month" has been
   * created through the buildMonth() function, the template skeleton is then
   * fed into this function to be customized for the particular user.  This
   * customization is done through the numerous parameters.  Upon successful
   * completion, this function returns an array structurally identical to
   * $monthData, but containing all the individual appointment availabilities
   * as specified by the parameters.
   *
   * @static
   * @access public
   * @param array $monthData The initial Smarty calendar template data.
   * @param bool $nextMonth True if the data is for next month, false for the current month.
   * @param array $notAvailable Array of dates that shouldn't be marked as anything.
   * @param array $scheduled Array of dates to be marked as SCHEDULED.
   * @param array $exceptions Array of dates to be marked as EXCEPTED.
   * @param array $available Array of dates to be marked as AVAILABLE.
   * @param int $default If a date is not found in any of the arrays, it should 
   *                     be marked with this.
   * @return array The finalized Smarty template data for the calendar.  This
   *               can be fed straight into an assign() call.
   */
  public static function finalizeAppts($monthData,
                                       $nextMonth, 
                                       $notAvailable,
                                       $scheduled,
                                       $exceptions,
                                       $available, 
                                       $default) {
    // first, get the number of weeks to interate over
    $numWeeks = count($monthData);

    // here is our return array
    $array = array();
          
    // build the current time
    $curTime = self::dateMySQL(self::now());

    // next, build the double-loop to go through the data array
    for ($i = 0; $i < $numWeeks; $i++) {
      for ($j = 0; $j < 7; $j++) { // each week has only 7 days, duh...
        $array[$i][$j] = $monthData[$i][$j]; // copy the day over
        if ($array[$i][$j]['dayExists'] == 1 && $array[$i][$j]['passed'] != 1) {
          // if the current day falls in the current calendar, and the
          // day has not yet passed
          
          // here is where all the magic happens. veg-o-matic. eat your heart out
          $nine   = self::buildDateFromCalendar($array[$i][$j]['dayNum'], 9, $nextMonth);
          $twelve = self::buildDateFromCalendar($array[$i][$j]['dayNum'], 12, $nextMonth);
          $three  = self::buildDateFromCalendar($array[$i][$j]['dayNum'], 15, $nextMonth);
          $six    = self::buildDateFromCalendar($array[$i][$j]['dayNum'], 18, $nextMonth);

          // first...are there any times that we've passed, though the day itself
          // may not be gone quite yet?
          if (($j > 0 && $array[$i][$j - 1]['passed'] == 1) || 
              ($j == 0 && $i > 0 && $array[$i - 1][6]['passed'] == 1) && 
               $array[$i][$j]['passed'] == 0) {
                 
            // mark as unavailable
            if ($curTime > $nine) {
              $array[$i][$j]['nine'] = self::$UNAVAILABLE;
            }
            if ($curTime > $twelve) {
              $array[$i][$j]['twelve'] = self::$UNAVAILABLE;
            }
            if ($curTime > $three) {
              $array[$i][$j]['three'] = self::$UNAVAILABLE;
            }
            if ($curTime > $six) {
              $array[$i][$j]['six'] = self::$UNAVAILABLE;
            }
          }
          
          // now, we start going through the validation arrays
          // first, let's look at what is decidedly unavailable
          $array[$i][$j] = self::setAvailability($array[$i][$j],
                                                 $notAvailable, 
                                                 self::$UNAVAILABLE,
                                                 $nextMonth);
          // second - for counselors only - what days are exceptions?
          $array[$i][$j] = self::setAvailability($array[$i][$j],
                                                 $exceptions,
                                                 self::$EXCEPTION,
                                                 $nextMonth);
          // third, what days are scheduled?
          $array[$i][$j] = self::setAvailability($array[$i][$j],
                                                 $scheduled,
                                                 self::$SCHEDULED,
                                                 $nextMonth);
          // finally, what days are available?
          $array[$i][$j] = self::setAvailability($array[$i][$j],
                                                 $available,
                                                 self::$AVAILABLE,
                                                 $nextMonth);
          // now, set any remaining unset times to their default
          if (!isset($array[$i][$j]['nine'])) {
            $array[$i][$j]['nine'] = $default;
          }
          if (!isset($array[$i][$j]['twelve'])) {
            $array[$i][$j]['twelve'] = $default;
          }
          if (!isset($array[$i][$j]['three'])) {
            $array[$i][$j]['three'] = $default;
          }
          if (!isset($array[$i][$j]['six'])) {
            $array[$i][$j]['six'] = $default;
          }
          // how 'bout some debugging?
          $cfg = Factory::getConfig();
          $cfg->fireDebugOutput('Day ' . $array[$i][$j]['dayNum'], $array[$i][$j]);
        }
      } // end inner for loop
    } // end outer for loop

    // oh jeebus, please work
    return $array;
  }
  
  /**
   * Sets the status for any matching times for the day specified.
   * 
   * Given the four-element array representing the four available times,
   * this function sets the availability status if it finds any matching
   * timestamps in the array for this particular day.
   * 
   * NOTE: This function will only assign a status if the value has not
   *       already been set by some previous call to the function.
   *
   * @static
   * @access public
   * @param array $element The four-element array representing a calendar day.
   * @param array $availabilities Stores timestamps.
   * @param int $value Corresponds to availability statuses.
   * @param bool $nextMonth True if this is the month in the future, false otherwise.
   * @return array The new element representing the day with any statuses set.
   */
  public static function setAvailability($element, $availabilities, $value, $nextMonth) {
    $retval = $element;
    
    // first, a sanity check
    if (!$availabilities) {
      return $retval;
    }
    
    // get the four timestamps we'll be comparing
    $nine = self::buildDateFromCalendar($element['dayNum'], 9, $nextMonth);
    $twelve = self::buildDateFromCalendar($element['dayNum'], 12, $nextMonth);
    $three = self::buildDateFromCalendar($element['dayNum'], 15, $nextMonth);
    $six = self::buildDateFromCalendar($element['dayNum'], 18, $nextMonth);
    
    $cfg = Factory::getConfig();
    $cfg->fireDebugOutput('Timestamp', $nine);
    
    // now, loop through the array
    $numDates = count($availabilities);
    for ($i = 0; $i < $numDates; $i++) {
      if ($availabilities[$i] == $nine && !isset($retval['nine'])) {
        $retval['nine'] = $value;
      } else if ($availabilities[$i] == $twelve && !isset($retval['twelve'])) {
        $retval['twelve'] = $value;
      } else if ($availabilities[$i] == $three && !isset($retval['three'])) {
        $retval['three'] = $value;
      } else if ($availabilities[$i] == $six && !isset($retval['six'])) {
        $retval['six'] = $value;
      }
    }
    
    // send this element back
    return $retval;
  }
  
  /**
   * This function will return a list of MySQL timestamps over a period of time.
   * 
   * This is useful particularly for recurring sessions; given a start date
   * and an end date, both in MySQL timestamp format, this function will
   * generate the weekly timestamps in between and build an array of those
   * timestamps, including the start and end dates.  This array will be
   * returned.
   * 
   * NOTE: $start and $end *must* fall on the same day of the week and
   *       at the same exact hour, otherwise this function returns null.
   *
   * @static
   * @access public
   * @param string $start The starting timestamp.
   * @param string $end The ending timestamp.
   * @return array An array of MySQL timestamps for all days in between.
   */
  public static function getBetweenDates($start, $end) {
    $retarr = array();
    $week = 60 * 60 * 24 * 7; // one week, in seconds
    $start_int = strtotime($start);
    $end_int   = strtotime($end);
    $cur = $start_int;
    $index = 0;
    
    // do some sanity checks
    if ($end_int <= $start_int) {
      return null;
    }
    
    // loop until we hit the ending timestamp
    for ($cur = $start_int; $cur <= $end_int; $cur += $week) {
      // this starts the timestamp at the starting time, incrementing
      // by a single week until we reach the ending timestamp
      $retarr[$index++] = self::dateMySQL($cur);
    }
    
    // sanity check
    if ($retarr[count($retarr) - 1] != $end) {
      return null;
    }
    
    // return the array
    return $retarr;
  }
}

?>