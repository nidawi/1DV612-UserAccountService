<?php

namespace model;

require_once 'Account.php';
require_once 'AccountCredentials.php';
require_once 'AccountGithub.php';
require_once 'AccountStoredItem.php';
require_once 'AccountContact.php';

class AccountRegister {

  private $database;

  private static $accountsTableName = "accounts";
  private static $accountGithubTableName = "account_github";
  private static $accountStorageTableName = "account_storage";
  private static $accountContactTableName = "account_contact";

  public function __construct(\lib\Database $database) {
    $this->database = $database;
  }

  public function isUsernameFree(string $username) : bool {
    $alias = "userExists";
    $result = $this->database->query('SELECT EXISTS(SELECT * FROM ' . self::$accountsTableName . ' WHERE username = ?) AS ' . $alias, array($username));
    return !boolval($result[0][$alias]);
  }
  public function isContactMethodFree(string $username, $type) {
    $alias = "methodExists";
    $result = $this->database->query('SELECT EXISTS(SELECT * FROM ' . self::$accountContactTableName . ' WHERE account=? AND type=?) AS ' . $alias, array($username, $type));
    return !boolval($result[0][$alias]);
  }

  public function createAccount(\model\AccountCredentials $account) {
    // Check username.
    if (!$this->isUsernameFree($account->getUsername()))
      throw new AccountAlreadyExistsException();

    $argsArr = array($account->getUsername(), $account->getPasswordHash());
    $this->database->query('INSERT INTO ' . self::$accountsTableName . ' (username, password) VALUES (?, ?)', $argsArr);
  }

  public function getAccount(string $username) : \model\Account {
    if ($this->isUsernameFree($username))
      throw new AccountDoesNotExistException();

    $argsArr = array($username, $username, $username);
    $storedItemQuery = 'SELECT COUNT(*) FROM ' . self::$accountStorageTableName . ' WHERE ' . self::$accountStorageTableName . '.account=?';
    $contactMethodsQuery = 'SELECT COUNT(*) FROM ' . self::$accountContactTableName . ' WHERE ' . self::$accountContactTableName . '.account=?';
    $githubDataQuery = 'LEFT JOIN ' . self::$accountGithubTableName . ' ON ' . self::$accountsTableName . '.username = ' . self::$accountGithubTableName . '.account WHERE ' . self::$accountsTableName . '.username = ?';
    $result = $this->database->query("SELECT *, ($storedItemQuery) AS storedItemCount, ($contactMethodsQuery) AS contactMethods FROM " . self::$accountsTableName . " $githubDataQuery", $argsArr);
    
    if (isset($result[0])) {
      return new \model\Account($result[0]);
    }
  }

  public function getAccountStoredItems(string $username) : array {
    if ($this->isUsernameFree($username))
      throw new AccountDoesNotExistException();
    
    $argsArr = array($username);
    $result = $this->database->query("SELECT * FROM " . self::$accountStorageTableName . " WHERE account=?", $argsArr);
    $itemsArr = array_map(function ($item) {
      return new \model\AccountStoredItem($item);
    }, $result);
    return $itemsArr;
  }
  public function addAccountStoredItems(string $username, array $items) {
    if ($this->isUsernameFree($username))
      throw new AccountDoesNotExistException();
    
    // I am in a rush. This is ugly.
    foreach ($items as $item) {
      $argsArr = array($username, $item->getEventType(), $item->getData());
      $this->database->query("INSERT INTO " . self::$accountStorageTableName . " (account, eventType, data) VALUES (?, ?, ?)", $argsArr);
    }
  }
  public function clearAccountStoredItems(string $username) {
    if ($this->isUsernameFree($username))
      throw new AccountDoesNotExistException();
    
    $argsArr = array($username);
    $this->database->query("DELETE FROM " . self::$accountStorageTableName . " WHERE account=?", $argsArr);
  }

  public function getAccountContactMethods(string $username) : array {
    if ($this->isUsernameFree($username))
      throw new AccountDoesNotExistException();
    
    $argsArr = array($username);
    $result = $this->database->query("SELECT * FROM " . self::$accountContactTableName . " WHERE account=?", $argsArr);
    $contactArr = array_map(function ($method) {
      return new \model\AccountContact($method);
    }, $result);
    return $contactArr;
  }
  public function addAccountContactMethods(string $username, array $contacts) {
    if ($this->isUsernameFree($username))
      throw new AccountDoesNotExistException();

    foreach ($contacts as $contact) {
      if (!$this->isContactMethodFree($username, $contact->getType()))
        throw new \model\ContactMethodAlreadyExistsException();

      $argsArr = array($username, $contact->getType(), $contact->getValue());
      $this->database->query("INSERT INTO " . self::$accountContactTableName . " (account, type, value) VALUES (?, ?, ?)", $argsArr);
    }
  }
  public function deleteAccountContactMethod(string $username, string $type) {
    if ($this->isUsernameFree($username))
      throw new AccountDoesNotExistException();

    if ($this->isContactMethodFree($username, $type))
      throw new ContactMethodDoesNotExistException();

    $argsArr = array($username, $type);
    $this->database->query("DELETE FROM " . self::$accountContactTableName . " WHERE account=? AND type=?", $argsArr);
  }
}