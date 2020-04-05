<?php

/**
 * @package MariaFramework
 * @subpackage MySQLPlugin
 * @author Jiju Thomas Mathew
 */
class mysql
{

  /**
   * @access private
   * @var resource mysql connection
   */
  private $link;

  /**
   * @access private
   * @var string ServerInfo
   */
  private $ServerInfo;

  /**
   * @access private
   * @var int last insert id
   */
  private $insert_id;

  /**
   * @access private
   * @var rows affected by last operation
   */
  private $affected_rows;

  /**
   * @access private
   * @var boolean intransaction
   */
  private $inTransaction;

  /**
   * @access private
   * @var mixed servers
   *      server associative array with keys 'host','port','user','pass','db'
   */
  private $readServers;

  /**
   * @access private
   * @var mixed servers
   *      server associative array with keys 'host','port','user','pass','db'
   */
  private $writeServer;

  /**
   * @access private
   * @var mixed querylog
   */
  private $querylog;

  /**
   * @access private
   * @var int ttl for objects in memcached
   */
  private $ttl = 500;

  /**
   * @access private
   * @var $cache instanceOf MFCache
   */

  public function __construct()
  {
    $this->link = false;
    $this->inTransaction = false;
    $this->querylog = array();
    $this->cache = false;
  }

  public function __destruct()
  {
    ($this->link instanceof mysqli) && $this->link->close();
  }

  public function getlog()
  {
    return $this->querylog;
  }

  private function logQuery($query, $ts, $isCatch = false)
  {
    if (MF::get('query_logging') == 'off')
      return;
    $process = trim($query);
    $process = substr($process, 0, 6);
    $process = strtoupper($process);
    //if(in_array($process, array('DELETE','INSERT','UPDATE','REPLAC'))  or $isCatch == true){
    $name = $_SERVER['SERVER_ADDR'] . ':' . date("Y-m-d\TH:i:s") . substr((string) microtime(), 1, 8);
    $this->querylog[$name] = array(
      'query' => $query,
      'remote_addr' => $_SERVER['REMOTE_ADDR'],
      'uri' => $_SERVER['REQUEST_URI'],
      'isCatch' => $isCatch,
      'qtime' => microtime(true) - $ts
    );
    //}
  }

  private function parsedsn($dsn){
    $parsed = parse_url($dsn);
    $parsed['db'] = substr($parsed['path'], 1);
    return $parsed; 
  }

  /**
   * Apply server pools
   */
  public function connect($dsnStr, $cache = false)
  {
    $this->writeServer = $dsnStr;
    $dsn = $this->parsedsn($dsnStr);
    $this->link = new mysqli($dsn['host'], $dsn['user'], $dsn['pass'], $dsn['db'], $dsn['port']);
    if ($this->link->connect_error) {
      die('Connect Error (' . $this->link->connect_errno . ') '
        . $this->link->connect_error);
    }
    $this->cache = $cache;
    MF::fireevent('mysql-connect', $this->link);
  }

  private function checkConnection($query)
  {
    if (is_a($this->link, 'mysqli')) {
      $this->link->ping();
      return;
    }

    $this->connect($this->writeServer, $this->cache);
  }

  private function closeConnection($query)
  {

    list($key,) = explode(' ', $query);
    $key = strtoupper($key);

    if ('SET' === $key) {
      return;
    }

    if ($this->inTransaction === false || 'COMMIT' === $key) {
      MF::fireevent('mysql-disconnect', $this->link);
      $this->link->close();
      $this->inTransaction = false;
      $this->link = false;
    }

  }

  /**
   * Sends a query to the database
   *
   * @param sqlquery $query
   * @return result-resource
   */
  public function query($query)
  {

    $this->affected_rows = 0;
   // try {
      $this->checkConnection($query);
      $worked = $this->pquery($query);
      $this->insert_id = $this->link->insert_id;
      $this->affected_rows = $this->link->affected_rows;
      $this->closeConnection($query);
   // } catch (Exception $e) {
   //   echo($e->getMessage());
   //   $worked = false;
   // }
    return $worked;
  }

  private function pquery($query)
  {
    $this->countqueries();
    $ts = microtime(true);
    $this->logQuery($query, $ts, true);
    return $this->link->query($query);
  }

  /**
   * Perform a modification query on database
   *
   * @param string $table
   * @param array $data
   * @param string $action
   * @param string $parameters
   * @return data resource
   */
  function perform($table, $data, $action = 'insert', $parameters = '')
  {
    reset($data);
    if ($action == 'insert' or $action == 'replace') {
      $query = strtoupper($action) . ' INTO ' . $table . ' (' . join(', ', array_keys($data)) . ') VALUES (';
      reset($data);
      foreach ($data as $value) {
        if (strtolower(substr($value, 0, 5)) == "func:") {
          $query .= substr($value, 5) . ', ';
        } else {
          switch (strtolower($value)) {
            case 'now()':
              $query .= 'NOW(), ';
              break;
            case 'null':
              $query .= 'NULL, ';
              break;
            default:
              $query .= '\'' . $this->input($value) . '\', ';
              break;
          }
        }
      }
      $query = substr($query, 0, -2) . ')';
    } elseif ($action == 'update') {
      $query = 'UPDATE ' . $table . ' SET ';
      foreach ($data as $columns => $value) {
        if (strtolower(substr($value, 0, 5)) == "func:") {
          $query .= $columns . ' = ' . substr($value, 5) . ', ';
        } else {
          switch (strtolower($value)) {
            case 'now()':
              $query .= $columns . ' = NOW(), ';
              break;
            case 'null':
              $query .= $columns . ' = NULL, ';
              break;
            case '++':
              $query .= $columns . ' = ' . $columns . ' + 1, ';
              break;
            default:
              $query .= $columns . ' = \'' . $this->input($value) . '\', ';
              break;
          }
        }
      }
      $query = substr($query, 0, -2);
      if ($parameters !== '')
        $query .= ' WHERE ' . $parameters;
    }
    $res = $this->query($query);

    return $res;
  }

  /**
   * Compliments mysql_fetch_array
   * @param mysql_result $result
   * @return mixed
   */
  private function fetch_array($result)
  {
    if (!is_a($result, 'mysqli_result'))
      return false;
    return $result->fetch_array(MYSQLI_ASSOC);
  }

  /**
   * Compliments mysql_fetch_row
   * @param mysql_result $result
   * @return mixed
   */
  private function fetch_row($result)
  {
    if (!is_a($result, 'mysqli_result'))
      return false;
    return $result->fetch_row();
  }

  /**
   * Compliments mysql_fetch_object
   * @param mysql_result $result
   * @return StdClassObject
   */
  private function fetch_object($result)
  {
    if (!is_a($result, 'mysqli_result'))
      return false;
    return $result->fetch_object();
  }

  /**
   * Compliments mysql_num_rows
   * @param mysql_result $result
   * @return int
   */
  private function num_rows($result)
  {
    if (!is_a($result, 'mysqli_result'))
      return false;
    return $result->num_rows;
  }

  /**
   * Compliments mysql_insert_id
   * @return int 
   */
  public function insert_id()
  {
    return $this->insert_id;
  }

  /**
   * Compliments mysql_affected_rows
   * @return int
   */
  public function affected_rows()
  {
    return $this->affected_rows;
  }

  /**
   * Compliments mysql_free_result
   * @param mysql_result $result
   * @return bool 
   */
  function free_result($result)
  {
    if (!is_a($result, 'mysqli_result'))
      return false;
    $result->free();
  }

  /**
   * Compliments mysql_fetch_fields
   * @param mysql_result $result
   * @return mixed
   */
  private function fetch_fields($result)
  {
    if (!is_a($result, 'mysqli_result'))
      return false;
    return $result->fetch_fields();
  }

  /**
   * function for sanitizing output
   * @param string $string
   * @return string 
   */
  function output($string)
  {
    return htmlspecialchars($string);
  }

  /**
   * Sanitizing input before sending to database
   * @param string $string
   * @return string
   */
  function input($string)
  {
    return addslashes($string);
  }

  /**
   * @method isCached
   * @access private
   * @param string $query
   * @param int $ttl
   * @param string $fetch_array valid values are row,array,object
   * @return boolean false or string $key 
   */
  function isCached($query, $ttl, $fetch_array = 'row')
  {
    if ($ttl > 0 && $this->cache !== false) {
      $key = $fetch_array . '-' . $query;
      return $this->cache->get($key);
    }
    return false;
  }

  /**
   * @method setCache
   * @access private
   * @param mixed $data
   * @param string $query
   * @param int $ttl
   * @param string $fetch_array valid values are row,array,object
   * @return boolean 
   */
  function setCache($data, $query, $ttl, $fetch_array = 'row')
  {
    if ($ttl > 0 && $this->cache !== false) {
      $key = $fetch_array . '-' . $query;
      return $this->cache->set($key, $data, $ttl);
    }
    return false;
  }

  /**
   * Counts the queries sent through this plugin into the MF registry
   * for a brief benchmarking
   * @method countqueries
   * @access private
   */
  private function countqueries()
  {
    $cc = MF::get('countqueries');
    ($cc == '') && $cc = 0;
    $cc++;
    MF::set('countqueries', $cc);
  }

  /**
   * For getting a table of data using a query
   * @method getData
   * @param string $query
   * @param string $type valid values are row,array,object
   * @param int $ttl
   * @return mixed
   */
  function getData($query, $type = 'row', $ttl = 0)
  {
    if (($retval = $this->isCached($query, $ttl, $type)) == false) {
      try {
        $this->checkConnection($query);
        $rs = $this->pquery($query);
        if ($rs && $rs->num_rows > 0) {
          $retval = array();
          $fetch = 'fetch_' . $type;
          while ($row = $this->$fetch($rs)) {
            $retval[] = $row;
          }
          $this->setCache($retval, $query, $ttl, $type);
          $rs->free();
        }
        $this->closeConnection($query);
      } catch (Exception $e) {
      }
    }
    return $retval;
  }

  /**
   * For getting a column of data using a query
   * @method getColumn
   * @param string $query
   * @param int $column
   * @param int $ttl 
   * @return array 
   */
  function getColumn($query, $column = 0, $ttl = 0)
  {
    if (($retval = $this->isCached($query, $ttl)) == false) {
      try {
        $this->checkConnection($query);
        $rs = $this->pquery($query);
        if ($rs && $rs->num_rows > 0) {
          $retval = array();
          while ($row = $this->fetch_row($rs)) {
            $retval[] = $row;
          }
          $this->setCache($retval, $query, $ttl);
          $rs->free();
        }
        $this->closeConnection($query);
      } catch (Exception $e) {
      }
    }
    if (!$retval) {
      return false;
    }
    $rv = array();
    reset($retval);
    foreach ($retval as $k => $v) {
      $rv[] = $v[$column];
    }

    return $rv;
  }

  /**
   * Fetch a single field from a single row
   * @method getValue
   * @param string $query
   * @param int $ttl
   * @return string
   */
  function getValue($query, $ttl = 0)
  {
    if (($retval = $this->isCached($query, $ttl)) == false) {
      $retval = array(false);
      $this->checkConnection($query);
      $rs = $this->pquery($query);
      if ($rs && $rs->num_rows > 0) {
        $retval = $this->fetch_row($rs);
        $this->setCache($retval, $query, $ttl);
        $rs->free();
      }
      $this->closeConnection($query);
    }
    return $retval[0];
  }

  /**
   * Fetch one record or a combination from multiple tables
   * @method getRow
   * @param string $query
   * @param string $type valid values are row,array,object
   * @param int $ttl
   * @return mixed
   */
  function getRow($query, $type = 'row', $ttl = 0)
  {
    if (($retval = $this->isCached($query, $ttl, $type)) == false) {
      $retval = false;
      $this->checkConnection($query);
      $rs = $this->pquery($query);
      if ($rs && $rs->num_rows > 0) {
        $fetch = 'fetch_' . $type;
        $retval = $this->$fetch($rs);
        $this->setCache($retval, $query, $ttl, $type);
        $rs->free();
      }
      $this->closeConnection($query);
    }
    return $retval;
  }

  /**
   * Fetch paginated data table, and extended properties
   * @param string $fields comma seperated aliased
   * @param string $tables simlulated with joins, subqueries etc, or single table
   * @param string $condition condition for filtering the selection table and for sorting too
   * @param int $page page number to show (base 1)
   * @param int $perpage items per page
   * @return mixed
   */
  public function getPage($fields, $tables, $condition, $page, $perpage, $type = 'array')
  {
    $count = $this->getValue("SELECT count(*) FROM {$tables} WHERE {$condition}");
    if ($count == 0) {
      return array('total' => 0, 'pages' => 0, 'current' => 0, 'range' => '', 'data' => array());
    }
    $pages = ceil($count / $perpage);
    $limits = (($page - 1) * $perpage) . ',' . $perpage;
    $data = $this->getData("SELECT {$fields} FROM {$tables} WHERE {$condition} LIMIT {$limits}", $type);
    $range = ((($page - 1) * $perpage) + 1) . ' to ' . ((($page - 1) * $perpage) + count($data)) . ' of ' . $count;
    return array('total' => $count, 'pages' => $pages, 'current' => $page, 'range' => $range, 'data' => $data);
  }
}
