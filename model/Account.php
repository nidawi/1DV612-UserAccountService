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

  public function __construct(array $accountData) {
    $this->username = $accountData["username"];
    $this->password = $accountData["password"];
    $this->type = $accountData["type"];
    $this->createdAt = $accountData["createdat"];
    $this->updatedAt = $accountData["updatedat"];

    $this->githubId = $accountData["githubId"];
    $this->githubToken = $accountData["githubToken"];

    $this->storedItemCount = $accountData["storedItemCount"];
  }

  public function getUsername() : string {
    return $this->username;
  }
  public function hasElevatedPermissions () : bool {
    return $this->type === self::ACCOUNT_TYPE_ELEVATED;
  }

  public function isPasswordMatch (string $passwordToCompare) : bool {
    return password_verify($passwordToCompare, $this->password);
  }

  public function toArray() : array {
    return array(
      "username" => $this->username,
      "storedItemCount" => $this->storedItemCount,
      "githubId" => $this->githubId,
      "githubToken" => $this->githubToken
    );
  }
}