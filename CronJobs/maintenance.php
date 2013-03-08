<?
//Get the ability to use the all-important "import()" function,
include('../config.inc.php');
//import the things I will need for this script
import('dbconn');
import('utility');
import('factory');
import('user');
import('calendar');


//here, we get the timrstamp of a day two days from now. We wiull use this to search for a day with a timestamp less than this, which will mean the appt is tomorrow. 
$tomorrow = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));
$tomorrow= Calendar::dateMySQL($tomorrow);
//Here we get the time stamp of a day four days away. We will use this and the $twodaysaway variable to zero in on appointments 3 days from today. 
$threeDaysAway= mktime(0, 0, 0, date("m")  , date("d")+3, date("Y"));
$threeDaysAway= Calendar::dateMySQL($threeDaysAway);

//query the session table for any appointments that are happening soon. save the emails of all parties involved. 
$db = Factory::getDB();
//Get the appointments tomorrow
//$db->setQuery('SELECT * FROM session WHERE datetime < "' . $tomorrow . '"');
//$appointmentsTomorrow = $db->loadRow();
//Get the appointments 2 days from now
$db->setQuery('SELECT * FROM session WHERE datetime > "' . $tomorrow . '" AND datetime < "' . $threeDaysAway .' "');
$appointmentsTwoDaysAway = $db->loadRow();

//send emails to the saved emails.

//First, for tomorrow
/*foreach($appointmentsTomorrow as $value)
{
	$message= 'This is an auto-generated message to remind you that you have an appointment with Dynamic Master Systems Tomorrow at '. substr($value['datetime'],11,5) .'.';
	Utility::email($value['user_id'],'Appointment Tomorrow Reminder', $message, 'Dynamic Master Sytems');
	Utility::email($value['counselor1'],'Appointment Tomorrow Reminder', $message, 'Dynamic Master Sytems');
	Utility::email($value['counselor2'],'Appointment Tomorrow Reminder', $message, 'Dynamic Master Sytems');
}*/

//Now for 2 days away
foreach($appointmentsTwoDaysAway as $value)
{
	$message= 'This is an auto-generated message to remind you that you have an appointment with Dynamic Master Systems in Three days at '. substr($value['datetime'],11,5) .'.';
	Utility::email($value['user_id'],'Appointment Tomorrow Reminder', $message, 'Dynamic Master Sytems');
	Utility::email($value['counselor1'],'Appointment Tomorrow Reminder', $message, 'Dynamic Master Sytems');
	Utility::email($value['counselor2'],'Appointment Tomorrow Reminder', $message, 'Dynamic Master Sytems');
}

//Now some Datbase Cleaning is a good idea

//get the time now
$timeNow = Calendar::dateMySQL(Calendar::now());
//delete the appointments that are passed
$db->setQuery('DELETE FROM sessions WHERE datetime < "'. $timeNow .'"');
$db->query();
//delete the exceptions that are passed
$db->setQuery('DELETE FROM exceptions WHERE datetime < "'. $timeNow .'"');
$db->query();
//delete the openings that are passed
$db->setQuery('DELETE FROM available WHERE end_date < "'. $timeNow .'"');
$db->query();
?> 
