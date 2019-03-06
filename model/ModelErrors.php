<?php

namespace model;

class UsernameMissingOrTooShortException extends \Exception {}
class PasswordMissingOrTooShortException extends \Exception {}
class UsernameTooLongException extends \Exception {}
class PasswordTooLongException extends \Exception {}

class AccountDoesNotExistException extends \Exception {}
class AccountAlreadyExistsException extends \Exception {}

class InvalidValueException extends \Exception {
  public function __construct(string $message) {
    parent::__construct($message);
  }
}