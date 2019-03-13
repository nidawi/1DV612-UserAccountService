<?php

namespace view;

class GetAccountView {

  private $navigator;
  private $body;

  private static $usernameId = "username";
  private static $passwordId = "password";

  public function __construct(\view\Navigator $navigator) {
    $this->navigator = $navigator;
    $this->body = $this->getJSONBody();
  }

  public function userWantstoAccessAccountRetrieval() : bool {
    return $this->userWantsToViewAccount() || $this->userWantsToAuthenticateAccount() || $this->userWantsToViewAccountsSubscribedToOrganization();
  }
  public function userWantsToViewAccountsSubscribedToOrganization() : bool {
    return ($this->navigator->isValidAPICall() && $this->navigator->isGET() && $this->navigator->isQueryPresent($this->navigator->getOrgLink()));
  }
  public function userWantsToViewAccount() : bool {
    return ($this->navigator->isValidAPICall() && $this->navigator->isGET() && $this->getDesiredAccount() !== "");
  }
  public function userWantsToAuthenticateAccount() : bool {
    return ($this->navigator->isValidAPICall() && $this->navigator->isPOST() && $this->getDesiredAccount() === "" && $this->navigator->isQueryPresent($this->navigator->getAuthenticateLink()));
  }

  public function getDesiredAccount() : string {
    return $this->navigator->getRelevantAccount();
  }
  public function getSelectedOrganization() : string {
    return $_GET[$this->navigator->getOrgLink()] ?? "";
  }

  public function getAuthentication() : \model\AccountCredentials {
    return new \model\AccountCredentials($this->getUsername(), $this->getPassword());
  }

  public function accountRetrievalSuccessful(\model\Account $account) {
    http_response_code(200);

    $response = array_merge(
      $account->toArray(),
      array(
        "_itemslink" => $this->createItemLink($account->getUsername()),
        "_contactlink" => $this->createContactLink($account->getUsername())
      )
    );
    echo json_encode($response);
  }
  public function accountsRetrievedSuccessfully(array $accounts) {
    http_response_code(200);

    $output = array_map(
      function ($acc) {
        return $acc->toArray();
      },
      $accounts
    );

    echo json_encode($output);
  }
  public function accountRetrievalUnsuccessful(\Exception $err) {
    $message =  "Account Retrieval failed due to an unknown error.";
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
      case $err instanceof \model\AccountDoesNotExistException:
        $message = "The requested account does not exist.";
        $code = 404;
        break;
      case $err instanceof \model\AccountCredentialsInvalidException:
        $message = "Account credentials were invalid.";
        $code = 401;
        break;
      case $err instanceof \model\NothingToCommitException:
        $message = "Nothing to do. Missing input.";
        $code = 400;
    }
    $this->navigator->errorOccured($code, $message);
  }

  private function getUsername() : string {
    return $this->body[self::$usernameId] ?? "";
  }
  private function getPassword() : string {
    return $this->body[self::$passwordId] ?? "";
  }
  private function createItemLink(string $username) : string {
    return "/?" . $this->navigator->getMainAPILink() . "=$username&" . $this->navigator->getItemsLink();
  }
  private function createContactLink(string $username) : string {
    return "/?" . $this->navigator->getMainAPILink() . "=$username&" . $this->navigator->getContactsLink();
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