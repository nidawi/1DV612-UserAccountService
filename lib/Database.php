<?php

namespace lib;

require_once 'DatabaseExceptions.php';

/**
 * Amateur Database Wrapper with Prepared Statement support.
 * This class relies on Mysqli.
 */
class Database {

  private $mysqli;
  private $address;
  private $user;
  private $password;
  private $database;

  private $verifyConnection = true;

  public function __construct(string $address,  string $user, string $password, string $database) {
    $this->address = $address;
    $this->user = $user;
    $this->password = $password;
    $this->database = $database;

    $this->connect();
  }

  /**
   * Checks whether a connection is currently open.
   * This includes pinging the database to verify a connection.
   * Returns true if there is a connection and false if there isn't.
   */
  public function isConnected() : bool {
    try {
      return (isset($this->mysqli) && $this->mysqli->ping()) ? true : false;
    } catch (\Exception $err) {
      return false;
    }
  }

  /**
   * Creates a prepared statement, executes, and returns the result as an array of values.
   * If the result has 0 rows, an empty array is returned.
   * If the result has 1 or more rows, the array is populated with one array per row.
   * Take note of this format when using this method. A simple count($result) > 0 will suffice.
   */
  public function query(string $statement, array $values) : array {
    return $this->executePreparedStatement($statement, $values);
  }

  /**
   * Closes the connection to this database.
   */
  public function kill() {
    $this->mysqli->close();
    unset($this->mysqli);
  }

  private function connect() {
    if ($this->isConnected())
      throw new DatabaseConnectionAlreadyOpenException();
    
    // Connect to the database using the stored information.
    $this->mysqli = new \mysqli($this->address, $this->user, $this->password, $this->database);
    $this->mysqli->set_charset('utf8'); // set charset to prevent awkward non-English characters (tip found on PHP.net)

    if ($this->mysqli->connect_errno) {
      unset($this->mysqli);
      throw new DatabaseConnectionFailureException();
    }
  }

  private function executePreparedStatement(string $statement, array $values) {
    $preppedStatement = $this->createPreparedStatement($statement, $values);
    $results = $this->getSqliResult($preppedStatement);
    $preppedStatement->close();

    return $results;
  }

  private function getSqliResult(\mysqli_stmt $dbStatement) : array {
    $results = $dbStatement->get_result();
    $resultsArray = array();

    if ($results) {
      while ($row = $results->fetch_assoc()) {
        $resultsArray[] = $row;
      }
    }

    return $resultsArray;
  }

  /**
   * Creates a prepared statement using the provided statement and values.
   * It will then execute the statement and return the statement object for the caller to use.
   */
  private function createPreparedStatement(string $statement, array $values) {
    $this->assertConnection();

    // make sure that the statement does not end with a ;
    $cleanStatement = preg_replace("/(?=.*);$/", "", $statement);

    // prepare statement
    $preppedStatement = $this->mysqli->prepare($cleanStatement); // such as "INSERT INTO TABLE_NAME (COLUMN1, COLUMN2) VALUES (?, ?)

    if (!$preppedStatement)
      throw new DatabaseInternalFailureException();

    // Bind the values. Use the lovely spread operator.
    $typesToBind = implode($this->convertTypesToMysqliTypeArray($values));
    if (count($values) > 0 && !$preppedStatement->bind_param($typesToBind, ...$values))
      throw new DatabaseInternalFailureException();

    // Execute the statement.
    if (!$preppedStatement->execute())
      throw new DatabaseInternalFailureException();
    
    return $preppedStatement;
  }

  private function convertTypesToMysqliTypeArray(array $values) : array {
    return array_map(function ($element) {
      return $this->convertTypeToMysqliTypeString($element);
    }, $values);
  }

  private function convertTypeToMysqliTypeString($var) : string {
    $type = gettype($var);
    switch ($type) {
      case "string": case "integer": case "double":
        return substr($type, 0, 1);
      default:
        throw new InvalidDatabaseTypeException();
    }
  }

  private function assertConnection() {
    if ($this->verifyConnection && !$this->isConnected())
      $this->connect();;
  }
}