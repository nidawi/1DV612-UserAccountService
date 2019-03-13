<?php

namespace model;

require_once 'Account.php';
require_once 'AccountCredentials.php';
require_once 'AccountStoredItem.php';
require_once 'AccountContact.php';
require_once 'AccountSettings.php';

class AccountRegister {

  private $database;

  private static $accountsTableName = "accounts";
  private static $accountGithubTableName = "account_github";
  private static $accountStorageTableName = "account_storage";
  private static $accountContactTableName = "account_contact";
  private static $accountSettingsTableName = "account_settings";

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
  public function isSettingsFree(string $username, string $orgName) {
    $alias = "settingsExist";
    $result = $this->database->query('SELECT EXISTS(SELECT * FROM ' . self::$accountSettingsTableName . ' WHERE account=? AND orgCode=?) AS ' . $alias, array($username, $orgName));
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
  public function getAccounts(string $orgName) : array {
    if (!isset($orgName))
      throw new \InvalidArgumentException();

    $argsArr = array($orgName);
    $result = $this->database->query("SELECT * FROM " . self::$accountSettingsTableName . " WHERE orgCode=?", $argsArr);
    $accountArr = array_map(
      function ($e) {
        return new \model\AccountSettings($e);
      },
      $result
    );

    return $accountArr;
  }
  public function authenticateAccount(\model\AccountCredentials $account) : \model\Account {
    $fetchedAccount = $this->getAccount($account->getUsername());

    if (isset($fetchedAccount) && $fetchedAccount->isPasswordMatch($account->getPassword())) {
      return $fetchedAccount;
    } else {
      throw new AccountCredentialsInvalidException();
    }
  }
  public function updateAccount(string $username, \model\Account $updates) {
    // Currently only support updating GithubId and GithubToken.
    $account = $this->getAccount($username);

    if ($account->getGithubId() !== $updates->getGithubId() || $account->getGithubToken() !== $updates->getGithubToken()) {
     $this->updateAccountGithubData($username, $updates);
    }
  }
  public function updateAccountGithubData(string $username, \model\Account $updates) {
    if ($updates->getGithubId() === "" && $updates->getGithubToken() === "") {
      throw new UpdateParamtersMissingException();
    }

    $account = $this->getAccount($username);
    if ($account->getGithubId() === $updates->getGithubId() && $account->getGithubToken() === $updates->getGithubToken()) {
      throw new NothingToCommitException();
    } else if ($account->getGithubId() === $updates->getGithubId() && $updates->getGithubToken() === "") {
      throw new NothingToCommitException();
    } else if ($account->getGithubId() === "" && $account->getGithubToken() === $updates->getGithubToken()) {
      throw new NothingToCommitException();
    }

    if ($account->getGithubId() === "" && $account->getGithubToken() === "") {
      // This means we need to ADD github data.
      $addArgs = array($account->getUsername(), $updates->getGithubId(), $updates->getGithubToken());
      $this->database->query("INSERT INTO " . self::$accountGithubTableName . " (account, githubId, githubToken) VALUES (?, ?, ?)", $addArgs);
    } else {
      // This means we need to UPDATE github data.
      $partsArr = array();
      $argsArr = array();

      if ($updates->getGithubId() !== "") {
        array_push($partsArr, "githubId=?");
        array_push($argsArr, $updates->getGithubId());
      }
      if ($updates->getGithubToken() !== "") {
        array_push($partsArr, "githubToken=?");
        array_push($argsArr, $updates->getGithubToken());
      }

      $statement = implode($partsArr, ", ");
      array_push($argsArr, $account->getUsername());
      $this->database->query("UPDATE " . self::$accountGithubTableName . " SET $statement WHERE account=?", $argsArr);
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
  public function updateAccountContactMethod(string $username, \model\AccountContact $updates) {
    if ($this->isUsernameFree($username))
      throw new AccountDoesNotExistException();

    if ($this->isContactMethodFree($username, $updates->getType()))
      throw new ContactMethodDoesNotExistException();

    $filteredMethods = array_filter(
      $this->getAccountContactMethods($username),
      function ($e) use ($updates) {
        return $e->getType() === $updates->getType();
      }
    );

    $currentMethod = reset($filteredMethods);

    if ($currentMethod->getValue() === $updates->getValue())
      throw new NothingToCommitException();
    
    // We do not edit "enabled" at this time.
    $argsArr = array($updates->getValue(), $username, $currentMethod->getType());
    $this->database->query("UPDATE " . self::$accountContactTableName . " SET value=? WHERE account=? AND type=?", $argsArr);
  }
  public function deleteAccountContactMethod(string $username, string $type) {
    if ($this->isUsernameFree($username))
      throw new AccountDoesNotExistException();

    if ($this->isContactMethodFree($username, $type))
      throw new ContactMethodDoesNotExistException();

    $argsArr = array($username, $type);
    $this->database->query("DELETE FROM " . self::$accountContactTableName . " WHERE account=? AND type=?", $argsArr);
  }

  public function getAccountSettings(string $username) : array {
    if ($this->isUsernameFree($username))
      throw new AccountDoesNotExistException();

    $argsArr = array($username);
    $result = $this->database->query("SELECT * FROM "  . self::$accountSettingsTableName . " WHERE account=?", $argsArr);
    $settingsArr = array_map(function ($settings) {
      return new \model\AccountSettings($settings);
    }, $result);
  
    return $settingsArr;
  }
  public function addAccountSettings(string $username, array $settings) {
    if ($this->isUsernameFree($username))
      throw new AccountDoesNotExistException();

    foreach ($settings as $sts) {
      if (!$this->isSettingsFree($username, $sts->getOrgName()))
        throw new \model\SettingsAlreadyExistException();
    
        $argsArr = array(
          $username,
          $sts->getOrgName(),
          $sts->getNotificationsEnabled() ?? true,
          $sts->getIssuesEnabled() ?? true,
          $sts->getCommentsEnabled() ?? true,
          $sts->getOrganizationEnabled() ?? true,
          $sts->getPushEnabled() ?? true,
          $sts->getReleaseEnabled() ?? true,
          $sts->getRepositoryEnabled() ?? true
        );
        $this->database->query("INSERT INTO " . self::$accountSettingsTableName . " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", $argsArr);
    }
  }
  public function updateAccountSettings(string $username, \model\AccountSettings $updates) {
    if ($this->isUsernameFree($username))
      throw new AccountDoesNotExistException();

    if ($this->isSettingsFree($username, $updates->getOrgName()))
      throw new SettingsDoNotExistException();

    // If this gives null, we're at a 500 internal server error no doubt...
    $filteredSettings = (array_filter(
      $this->getAccountSettings($username),
      function ($elem) use ($updates) { 
        return $elem->getOrgName() === $updates->getOrgName();
      }
    ));
    $currentSettings = (reset($filteredSettings))->toArray();

    $diff = array(); // Kinda ugly.
    foreach ($updates->toArray() as $key => $value) {
      if (isset($value) && array_key_exists($key, $currentSettings) && $currentSettings[$key] !== $value) {
        $diff[$key] = $value;
      }
    }

    if (count($diff) < 1)
      throw new NothingToCommitException();

    // Commit changes.
    $partsArr = array();
    $argsArr = array();

    foreach ($diff as $key => $value) {
      array_push($partsArr, "$key=?");
      array_push($argsArr, $value);
    }

    $statement = implode($partsArr, ", ");
    array_push($argsArr, $username, $updates->getOrgName());
    $this->database->query("UPDATE " . self::$accountSettingsTableName . " SET $statement WHERE account=? AND orgCode=?", $argsArr);
  }
  public function deleteAccountSettings(string $username, string $orgName) {
    if ($this->isUsernameFree($username))
      throw new AccountDoesNotExistException();
    
    if ($this->isSettingsFree($username, $orgName))
      throw new SettingsDoNotExistException();

    $argsArr = array($username, $orgName);
    $this->database->query("DELETE FROM " . self::$accountSettingsTableName . " WHERE account=? AND orgCode=?", $argsArr);
  }
}