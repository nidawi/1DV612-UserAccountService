<?php

namespace view;

class UpdateAccountView {

  private $navigator;
  private $body;

  private static $githubId = "githubId";
  private static $githubToken = "githubToken";

  public function __construct(\view\Navigator $navigator) {
    $this->navigator = $navigator;
    $this->body = $this->getJSONBody();
  }

  public function userWantsToUpdateAccount() : bool {
    return ($this->navigator->isValidAPICall() && $this->navigator->isPOST() && $this->getDesiredAccount() !== null);
  }
  public function getDesiredAccount() : string {
    return $this->navigator->getRelevantAccount();
  }

  public function getUpdates() : \model\Account {
    return new \model\Account(
      array(
        "githubId" => $this->body[self::$githubId] ?? "",
        "githubToken" => $this->body[self::$githubToken] ?? ""
      )
    );
  }

  public function accountUpdateSuccessful() {
    http_response_code(204);
    echo "";
  }
  public function accountUpdateUnsuccessful(\Exception $err) {
    $message = "Account Update failed due to an unknown error.";
    $code = 500;
    switch (true) {
      case $err instanceof \model\AccountDoesNotExistException:
        $message = "The requested account does not exist.";
        $code = 404;
        break;
      case $err instanceof \model\UpdateParamtersMissingException:
      case $err instanceof \model\NothingToCommitException:
        $message = "Nothing to update. Details missing or identical to current.";
        $code = 400;
        break;
      case $err instanceof \InvalidArgumentException:
        $message = "One or more input arguments were invalid.";
        $code = 400;
        break;
    }
    $this->navigator->errorOccured($code, $message);
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