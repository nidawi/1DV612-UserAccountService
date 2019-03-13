<?php

namespace model;

class UsernameMissingOrTooShortException extends \Exception {}
class PasswordMissingOrTooShortException extends \Exception {}
class UsernameTooLongException extends \Exception {}
class PasswordTooLongException extends \Exception {}

class AccountDoesNotExistException extends \Exception {}
class AccountAlreadyExistsException extends \Exception {}
class AccountCredentialsInvalidException extends \Exception {}
class UpdateParamtersMissingException extends \Exception {}
class NothingToCommitException extends \Exception {}

class ContactMethodAlreadyExistsException extends \Exception {}
class ContactMethodDoesNotExistException extends \Exception {}

class SettingsAlreadyExistException extends \Exception {}
class SettingsDoNotExistException extends \Exception {}

class InvalidValueException extends \Exception {
  public function __construct(string $message) {
    parent::__construct($message);
  }
}