<?php

defined('IC') or die('Restricted access');

import('dbexception');

/**
 * This class acts as a simple wrapper for the PHP MySQL API.
 * 
 * @package Introverted.Champions 
 * @author Shannon Quinn
 * @version 1.5
 */

// ---------------------------------------------
// Revisions
// 1.0  -
// 1.1  - 6/25/08
//      - Added restricted statement to top
//      - Spruced up documentation
//      - Made constructor private
//      - Added static getDB() factory method
//      - Added $debug
//      - Added $active_result
//      - Removed setDatabase()
//      - Added _changeDB()
//      - Added _hitDB()
//      - Added setQuery()
//      - TODO: Implement state
//      - TODO: Rewrite query() method
//      - TODO: Write loadRow() and loadRows()
// 1.2  - 6/27/08
//      - Wrote loadRow()
//      - Wrote loadRows()
//      - Added $DB_INIT, $DB_SET, $DB_RET
//      - Rewrote query()
// 1.3  - 7/2/08
//      - Fixed function call error in loadRows()
//
// 1.4  - Everything seems to be working properly.
// 1.5  - 7/8/08
//      - Fixed a bug with loadRow() which made it skip querying
//      - Added getActiveQuery() function
// 1.6  - 7/18/08
//      - Added getError() function

class DBConn {
 
  /***********************\
  |   Static Attributes   |
  \***********************/

  /**
   * State of the database after being instantiated.
   *
   * @static
   * @access public
   * @var int $DB_INIT
   */
  public static $DB_INIT = 0;
  
  /**
   * State after call to setQuery()
   *
   * @static
   * @access public
   * @var int $DB_SET
   */
  public static $DB_SET  = 1;
  
  /**
   * State after call to query(), loadRow(), or loadRows()
   *
   * @static
   * @access public
   * @var int $DB_RET
   */
  public static $DB_RET  = 2;
 
  /***********************\
  |   Private Attributes  |
  \***********************/

  /**
   * Stores the resource for the database connection.
   * 
   * @access private
   * @var resource $db_handle Result of call to mysql_pconnect
   */
  private $db_handle;
  
  /**
   * Stores the last query to be submitted.
   * 
   * @access private
   * @var string $active_query
   */
  private $active_query;
  
  /**
   * Stores the last result of mysql_query.
   * 
   * @access private
   * @var resource $active_result
   */
  private $active_result;
  
  /**
   * Tracks the state of the DBConn class.
   * 
   * @access private
   * @var int $state 0|1|2
   * 0: Ready for query to be set.
   * 1: Query has been set; ready to execute.
   * 2: Query has been executed; results are ready.
   */
  private $state;
  
  /**
   * Specifies the debug mode.
   * 
   * @access private
   * @var bool $debug
   */
  private $debug;
  
  /***********************\
  |       Functions       |
  \***********************/
  
  /** 
   * Constructor
   *
   * @access private
   */
  private function __construct() {
    // grab the configuration and establish the database connection
    $cfg = Factory::getConfig();
    $this->db_handle = @mysql_pconnect($cfg->getValue('dbhost'), 
                                       $cfg->getValue('dbuser'),
                                       $cfg->getValue('dbpass'));
    if (!$this->db_handle) { // error handling      
      die('Error connecting to database.  Please contact the site admin. ' . mysql_error() . "\n<br />");
    }

    // set up the debug values and the current database
    $this->debug = $cfg->getValue('debug');
    $this->_changeDB($cfg->getValue('dbname'));
    $this->state = self::$DB_INIT;
  }
  
  /**
   * Public factory method.
   * 
   * @static
   * @access public
   * @return object An instance of a DBConn object
   */
  public static function getInstance() {
    return new DBConn();
  }
  
  /**
   * Sets the active query.
   * 
   * @access public
   * @param string $query The SQL query to be executed.
   */
  public function setQuery($query) {
    $this->active_query = $query;
    $this->state = self::$DB_SET;
  }

  /**
   * Executes the active query, indicating whether it was successful or not.
   *
   * @access public
   * @return bool True on success, False on failure
   */
  public function query() {
    $result = @mysql_query($this->active_query);
    if ($result) {
      return true;
    } else {
      return false;
    }
  }
  
  /**
   * Returns a single row of the query result from the active query in the 
   * form of an associative array.  This function can be used mostly with
   * SELECT statements that return a single row.
   * 
   * @access public
   * @return array Associative 1-D array of single row.
   */
  public function loadRow() {
    // so this function can be called successively...
    static $result = null;
   
    // obtain the result resource from the query
    if ($this->state != self::$DB_RET) {
      $result = @mysql_query($this->active_query);
    }
    // change the state
    $this->state = self::$DB_RET;
      
    // any errors from simply querying? then you're dumb
    if (!$result) {
      throw new DBException('Error performing query "' . $this->active_query .
                            '": ' . mysql_error());
      return false;
    } else {
      // retrieve the row
      $row = mysql_fetch_array($result, MYSQL_BOTH);

      // return it
      return $row;
    }
  }
  
  /**
   * Returns all rows of the query result from the active query.  This function
   * should be used mostly when there are multiple rows to be returned from
   * a SELECT statement.
   *
   * @access public
   * @return array Associative 2-D array of all rows.
   */
  public function loadRows() {
    $resArr = array();
    $i = 0;
    while ($row = $this->loadRow()) {
      $resArr[$i++] = $row;
    }
    return $resArr;
  }
  
  /**
   * Accessor for the current query.  Mainly used for debugging.  If you've
   * constructed with a query using quite a bit of string concatenation, this
   * can be useful to echo to the browser to ensure that the query you think
   * you're building is, in fact, the query that is being executed.
   *
   * @access public
   * @return string The active query, as set by setQuery().
   */
  public function getActiveQuery() {
    return $this->active_query;
  }
  
  /**
   * If an error has occurred in the query process, this function will
   * return the MySQL error message as returned by mysel_error().
   *
   * @access public
   * @return string The MySQL error message.
   */
  public function getError() {
    return mysql_error();
  }
  
  /***********************\
  |   Private Functions   |
  \***********************/
  
  /**
   * Performs the actual MySQL query.
   * 
   * @access private
   * @return mixed For SELECT statements, this returns the MySQL resource.
   *               For other queries, it returns TRUE or FALSE.
   */
  private function _hitDB() {
    $result = @mysql_query($this->active_query);
    if (!$result) {
      throw new DBException('Error performing query: "' . $this->active_query . '".' . mysql_error());
    }
    $this->active_result = $result;
  }
  
  /**
   * Changes the active database from which the queries are made.
   * 
   * @access private
   * @param string $dbname The name of the database to change to.
   */
  private function _changeDB($dbname) {
    $newdb = mysql_select_db($dbname);
    if (!$newdb) {
      throw new DBException('Error changing to database ' . $dbname . ': ' . mysql_error());
    }
  }

  /* end DBConn class */
}

?>
