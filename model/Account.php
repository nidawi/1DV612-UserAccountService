<?php

namespace model;

class Account {

  const ACCOUNT_TYPE_NORMAL = 0;
  const ACCOUNT_TYPE_ELEVATED = 1;

  private $username;
  private $password;
  private $type;
  private $createdAt;
  private $updatedAt;

  private $githubId;
  private $githubToken;

  private $storedItemCount;
  private $contactMethods;

  public function __construct(array $accountData) {
    $this->username = $this->setStringValue($accountData["username"] ?? "");
    $this->password = $accountData["password"] ?? "";
    $this->type = $accountData["type"] ?? "";
    $this->createdAt = $accountData["createdat"] ?? "";
    $this->updatedAt = $accountData["updatedat"] ?? "";

    $this->githubId = $this->setStringValue($accountData["githubId"] ?? "");
    $this->githubToken = $this->setStringValue($accountData["githubToken"] ?? "");

    $this->storedItemCount = $accountData["storedItemCount"] ?? "";
    $this->contactMethods = $accountData["contactMethods"] ?? "";
  }

  public function getUsername() : string {
    return $this->username;
  }
  public function getGithubId() : string {
    return $this->githubId;
  }
  public function getGithubToken() : string {
    return $this->githubToken;
  }

  public function hasElevatedPermissions () : bool {
    return $this->type === self::ACCOUNT_TYPE_ELEVATED;
  }

  public function isPasswordMatch (string $passwordToCompare) : bool {
    return password_verify($passwordToCompare, $this->password);
  }

  private function setStringValue(string $value) {
    if (isset($value) && strlen($value) > 225) {
      throw new \InvalidArgumentException();
    }
    return $value;
  }

  public function toArray() : array {
    return array(
      "username" => $this->username,
      "storedItemCount" => $this->storedItemCount,
      "contactMethods" => $this->contactMethods,
      "githubId" => $this->githubId,
      "githubToken" => $this->githubToken
    );
  }
}