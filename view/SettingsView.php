<?php

namespace view;

class SettingsView {

  private $navigator;
  private $body;

  public function __construct(\view\Navigator $navigator) {
    $this->navigator = $navigator;
    $this->body = $this->getJSONBody();
  }
  
  public function userWantsToAccessSettings() : bool {
    return $this->navigator->isQueryPresent($this->navigator->getSettingsLink());
  }
  public function userWantsToViewSettings() : bool {
    return $this->navigator->isGET();
  }
  public function userWantsToAddSettings() : bool {
    return $this->navigator->isPOST();
  }
  public function userWantsToUpdateSettings() : bool {
    return $this->navigator->isPatch();
  }
  public function userWantsToDeleteSettings() : bool {
    return $this->navigator->isDELETE();
  }

  public function getDesiredAccount() : string {
    return $this->navigator->getRelevantAccount();
  }
  public function getSelectedOrgName() : string {
    return $_GET[$this->navigator->getSettingsLink()] ?? "";
  }
  public function getSettingsToAdd() : array {
    $settings = array_map(function ($sts) {
      return new \model\AccountSettings($sts);
    }, $this->body);
    return $settings;
  }
  public function getSettingsUpdates() : \model\AccountSettings {
    return new \model\AccountSettings(
      array_merge(
        $this->body,
        array("account" => $this->getDesiredAccount()),
        array("orgCode" => $this->getSelectedOrgName())
      )
    );
  }


  public function settingsRetrievalSuccessful(array $settings) {
    http_response_code(200);

    $result = array_map(function($sts) {
      return $sts->toArray();
    }, $settings);

    echo json_encode($result);
  }
  public function settingsCreationSuccessful() {
    http_response_code(200);
    echo "";
  }
  public function settingsUpdateSuccessful() {
    http_response_code(201);
    echo "iupd";
  }
  public function settingsDeletionSuccessful() {
    http_response_code(204);
    echo "";
  }

  public function settingsInteractionFailed($err) {
    $message =  "Settings Operation failed due to an unknown error.";
    $code = 500;
    switch (true) {
      case $err instanceof \model\AccountDoesNotExistException:
        $message = "The requested account does not exist.";
        $code = 404;
        break;
      case $err instanceof \model\SettingsAlreadyExistException:
        $message = "Settings already exist.";
        $code = 400;
        break;
      case $err instanceof \model\UpdateParamtersMissingException:
      case $err instanceof \model\NothingToCommitException:
      case $err instanceof \view\NothingToAddException:
      case $err instanceof \view\NothingToDeleteException:
        $message = "Nothing to update. Details missing or identical to current.";
        $code = 200;
        break;
      case $err instanceof \model\SettingsDoNotExistException:
        $message = "Settings do not exist.";
        $code = 404;
        break;
      case $err instanceof \model\InvalidValueException:
      case $err instanceof \TypeError:
        $message = "One or more values are invalid or missing.";
        $code = 400;
        break;
    }
    $this->navigator->errorOccured($code, $message);
  }

  private function areMethodsProvided() : bool {
    return isset($this->body) && is_array($this->body);
  }
  private function getJSONBody() : array {
    if ($this->userWantsToAddSettings() || $this->userWantsToUpdateSettings()) {
      try {
        $inputJSON = file_get_contents('php://input');
        $json = json_decode($inputJSON, TRUE);
        return $json ?? array();
      } catch (\Exception $err) { }
    }
    return array();
  }
}