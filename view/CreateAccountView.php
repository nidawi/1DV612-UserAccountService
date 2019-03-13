<?php

namespace view;

class CreateAccountView {

  private $navigator;
  private $body;

  private static $usernameId = "username";
  private static $passwordId = "password";

  public function __construct(\view\Navigator $navigator) {
    $this->navigator = $navigator;
    $this->body = $this->getJSONBody();
  }

  public function userWantsToCreateAccount() : bool {
    return ($this->navigator->isValidAPICall() && $this->navigator->isPOST() && $this->navigator->getRelevantAccount() === "");
  }
  public function getAccountCredentials() : \model\AccountCredentials {
    return new \model\AccountCredentials($this->getUsername(), $this->getPassword());
  }

  public function accountCreationSuccessful() {
    $userCreds = $this->getAccountCredentials();
    http_response_code(201);
    header('Location: /?account=' . $userCreds->getUsername()); // string dependency fix this
    echo "";
  }
  public function accountCreationUnsuccessful(\Exception $err) {
    $message = "User creation failed due to an unknown error.";
    $code = 500;
    switch (true) {
      case $err instanceof \model\UsernameMissingOrTooShortException:
      case $err instanceof \model\UsernameTooLongException:
        $message = "Username is of an invalid length. It has to be between " . \model\AccountCredentials::USERNAME_MIN_LENGTH . " and " . \model\AccountCredentials::USERNAME_MAX_LENGTH . " characters long.";
        break;
      case $err instanceof \model\PasswordMissingOrTooShortException:
      case $err instanceof \model\PasswordTooLongException:
        $message = "Password is of an invalid length. It has to be between " . \model\AccountCredentials::PASSWORD_MIN_LENGTH . " and " . \model\AccountCredentials::PASSWORD_MAX_LENGTH . " characters long.";
        break;
      case $err instanceof \model\AccountAlreadyExistsException:
        $message = "That username is taken.";
        break;
    }
  
    $this->navigator->errorOccured($code, $message);
  }

  private function getUsername() : string {
    return $this->body[self::$usernameId] ?? "";
  }
  private function getPassword() : string {
    return $this->body[self::$passwordId] ?? "";
  }
  private function areAccountFieldsPresent() : bool {
    return isset($this->body[self::$usernameId]) && isset($this->body[self::$passwordId]);
  }
  private function getJSONBody() : array {
    if ($this->navigator->isPOST()) {
      try {
        $inputJSON = file_get_contents('php://input');
        $json = json_decode($inputJSON, TRUE);
        return $json ?? array();
      } catch (\Exception $err) { }
    }
    return array();
  }
}