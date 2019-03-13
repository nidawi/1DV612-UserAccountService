<?php

namespace view;

class ContactView {

  private $navigator;
  private $body;

  public function __construct(\view\Navigator $navigator) {
    $this->navigator = $navigator;
    $this->body = $this->getJSONBody();
  }
  
  public function userWantsToAccessContactMethods() : bool {
    return $this->navigator->isQueryPresent($this->navigator->getContactsLink());
  }
  public function userWantsToViewContactMethods() : bool {
    return $this->navigator->isGET();
  }
  public function userWantsToAddContactMethods() : bool {
    return $this->navigator->isPOST();
  }
  public function userWantsToUpdateContactMethod() : bool {
    return $this->navigator->isPATCH();
  }
  public function userWantsToDeleteContactMethods() : bool {
    return $this->navigator->isDELETE();
  }

  public function getDesiredAccount() : string {
    return $this->navigator->getRelevantAccount();
  }
  public function getSelectedMethodType() : string {
    return $_GET[$this->navigator->getContactsLink()] ?? "";
  }
  public function getMethodsToAdd() : array {
    $methods = array_map(function ($method) {
      return new \model\AccountContact($method);
    }, $this->body);
    return $methods;
  }
  public function getMethodUpdate() : \model\AccountContact {
    return new \model\AccountContact(
      array(
        "account" => $this->getDesiredAccount(),
        "type" => $this->getSelectedMethodType(),
        "value" => $this->body["value"] ?? "",
        "enabled" => $this->body["enabled"] ?? ""
      )
    );
  }


  public function methodRetrievalSuccessful(array $items) {
    http_response_code(200);

    $result = array_map(function($it) {
      return $it->toArray();
    }, $items);

    echo json_encode($result);
  }
  public function methodUpdateSuccessful() {
    http_response_code(200);
    echo "";
  }
  public function methodCreationSuccessful() {
    http_response_code(201);
    echo "";
  }
  public function methodDeletionSuccessful() {
    http_response_code(204);
    echo "";
  }

  public function methodInteractionFailed($err) {
    $message =  "Contact Method Operation failed due to an unknown error.";
    $code = 500;
    switch (true) {
      case $err instanceof \model\AccountDoesNotExistException:
        $message = "The requested account does not exist.";
        $code = 404;
        break;
      case $err instanceof \model\ContactMethodAlreadyExistsException:
        $message = "Contact Method already exists.";
        $code = 400;
        break;
      case $err instanceof \model\ContactMethodDoesNotExistException:
        $message = "Contact Method does not exist.";
        $code = 404;
        break;
      case $err instanceof \model\InvalidValueException:
      case $err instanceof \TypeError:
          $message = "One or more values are invalid or missing.";
          $code = 400;
          break;
      case $err instanceof \model\UpdateParamtersMissingException:
      case $err instanceof \model\NothingToCommitException:
      case $err instanceof \view\NothingToAddException:
      case $err instanceof \view\NothingToDeleteException:
          $message = "Nothing to update. Details missing or identical to current.";
          $code = 200;
          break;
    }
    $this->navigator->errorOccured($code, $message);
  }

  private function areMethodsProvided() : bool {
    return isset($this->body) && is_array($this->body);
  }
  private function getJSONBody() : array {
    if ($this->userWantsToAddContactMethods() || $this->userWantsToUpdateContactMethod()) {
      try {
        $inputJSON = file_get_contents('php://input');
        $json = json_decode($inputJSON, TRUE);
        return $json ?? array();
      } catch (\Exception $err) { }
    }
    return array();
  }
}