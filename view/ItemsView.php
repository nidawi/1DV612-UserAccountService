<?php

namespace view;

class ItemsView {

  private $navigator;
  private $body;

  private static $storedItemLink = "items";

  public function __construct(\view\Navigator $navigator) {
    $this->navigator = $navigator;
    $this->body = $this->getJSONBody();
  }
  
  public function userWantsToAccessAccountItems() : bool {
    return $this->navigator->isQueryPresent(self::$storedItemLink);
  }
  public function userWantsToViewAccountItems() {
    return $this->navigator->isGET();
  }
  public function userWantsToAddAccountItems() : bool {
    return $this->navigator->isPOST();
  }
  public function userWantsToDeleteAccountItems() : bool {
    return $this->navigator->isDELETE();
  }

  public function getDesiredAccount() : string {
    return $this->navigator->getRelevantAccount();
  }
  public function getItemsToAdd() : array {
    $items = array_map(function ($item) {
      return new \model\AccountStoredItem(array(
        "eventType" => $item["eventType"] ?? "",
        "data" => $this->encodeData($item["data"] ?? "")
      ));
    }, $this->body);
    return $items;
  }


  public function itemRetrievalSuccessful(array $items) {
    http_response_code(200);

    $result = array_map(function($it) {
      return array_merge(
        $it->toArray(),
        array("data_type" => "base64url")
      );
    }, $items);

    echo json_encode($result);
  }
  public function itemCreationSuccessful() {
    http_response_code(201);
    echo "";
  }
  public function itemDeletionSuccessful() {
    http_response_code(204);
    echo "";
  }

  public function itemInteractionFailed($err) {
    $message =  "Item Operation failed due to an unknown error.";
    $code = 500;
    switch (true) {
      case $err instanceof \model\AccountDoesNotExistException:
        $message = "The requested account does not exist.";
        $code = 404;
        break;
      case $err instanceof \TypeError:
        $message = "One or more input parameters were invalid or missing.";
        $code = 400;
        break;
    }
    $this->navigator->errorOccured($code, $message);
  }

  private function areItemsProvided() : bool {
    return isset($this->body) && is_array($this->body);
  }
  private function getJSONBody() : array {
    if ($this->userWantsToAddAccountItems()) {
      try {
        $inputJSON = file_get_contents('php://input');
        $json = json_decode($inputJSON, TRUE);
        return $json ?? array();
      } catch (\Exception $err) { }
    }
    return array();
  }

  private function encodeData($data) {
    if ($data !== "") {
      $json = json_encode($data);
      $encoded = $this->base64url_encode($json);
      return $encoded;
    }
  }
  private function base64url_encode($data) { 
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
  }
}