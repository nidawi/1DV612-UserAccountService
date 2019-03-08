<?php

namespace model;

class AccountCredentials {

  const USERNAME_MAX_LENGTH = 50;
  const USERNAME_MIN_LENGTH = 2;
  const PASSWORD_MAX_LENGTH = 60;
  const PASSWORD_MIN_LENGTH = 3;

  private $username;
  private $password;

  public function __construct(string $username, string $password) {
    $this->setUsername($username);
    $this->setPassword($password);
  }

  public function getUsername() : string {
    return $this->username;
  }
  public function getPassword() : string {
    return $this->password;
  }
  public function getPasswordHash() : string {
    return password_hash($this->password, PASSWORD_BCRYPT);
  }

  private function setUsername(string $username) {
    if (strlen($username) < self::USERNAME_MIN_LENGTH)
      throw new UsernameMissingOrTooShortException();
    else if (strlen($username) > self::USERNAME_MAX_LENGTH)
      throw new UsernameTooLongException();
    else
      $this->username = $username;
  }
  private function setPassword(string $password) {
    if (strlen($password) < self::PASSWORD_MIN_LENGTH)
      throw new PasswordMissingOrTooShortException();
    else if (strlen($password) > self::PASSWORD_MAX_LENGTH)
      throw new PasswordTooLongException();
    else
      $this->password = $password;
  }
}