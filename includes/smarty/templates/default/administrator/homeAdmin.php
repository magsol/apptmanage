<?php

defined('IC') or die('Restricted access');

/******************************************\
* Created on Jul 30, 2008                  *
********************************************
* Functions as a helper script to set up   *
* administrator's home page.               *
\******************************************/

// we need to fetch a few variables that smarty can use

// first, how many outstanding appointments are there awaiting approval?
$db->setQuery('SELECT * FROM session WHERE counselor1 = "" AND counselor2 = "" ' .
              'AND datetime >= "' . Calendar::dateMySQL(Calendar::now()) . '"');
$results = $db->loadRows();

// set the count of the results to the smarty variable
$smarty->assign('appointments', count($results));

// now, how many users of various permissions levels do we have?
$db->setQuery('SELECT COUNT(*) FROM user WHERE userclass = "counselor"');
$counselors = $db->loadRow();
$db->setQuery('SELECT COUNT(*) FROM user WHERE userclass = "client"');
$clients = $db->loadRow();

// set the smarty variables
$smarty->assign('counselors', $counselors['COUNT(*)']);
$smarty->assign('clients', $clients['COUNT(*)']);

?>