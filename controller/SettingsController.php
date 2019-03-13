<?php

namespace controller;

class SettingsController {

  private $register;
  private $view;

  public function __construct(\model\AccountRegister $register, \view\SettingsView $view) {
    $this->register = $register;
    $this->view = $view;
  }

  public function doSettingsInteraction() {
    try {
      $username = $this->view->getDesiredAccount();

      if ($this->view->userWantsToAddSettings())
        $this->doAddSettings($username);
      else if ($this->view->userWantsToUpdateSettings())
        $this->doUpdateSettings($username);
      else if ($this->view->userWantsToDeleteSettings())
        $this->doDeleteSettings($username);
      else
        $this->doGetSettings($username);


    } catch (\Exception $err) {
      $this->view->settingsInteractionFailed($err);
    } catch (\Error $error) {
      $this->view->settingsInteractionFailed($error);
    }
  }

  private function doGetSettings(string $username) {
    $settings = $this->register->getAccountSettings($username);
    $this->view->settingsRetrievalSuccessful($settings);
  }

  private function doAddSettings(string $username) {
    $stsToAdd = $this->view->getSettingsToAdd();
    if (count($stsToAdd) < 1)
      throw new \view\NothingToAddException();
    
    $this->register->addAccountSettings($username, $stsToAdd);
    $this->view->settingsCreationSuccessful();
  }

  private function doUpdateSettings($username) {
    $updates = $this->view->getSettingsUpdates();
    if (!isset($updates)) {
      throw new \view\NothingToUpdateException();
    }
    
    $this->register->updateAccountSettings($username, $updates);
    $this->view->settingsUpdateSuccessful();
  }

  private function doDeleteSettings(string $username) {
    $orgnameToDelete = $this->view->getSelectedOrgName();
    if (!isset($orgnameToDelete) || strlen($orgnameToDelete) < 2)
      throw new \view\NothingToDeleteException();

    $this->register->deleteAccountSettings($username, $orgnameToDelete);
    $this->view->settingsDeletionSuccessful();
  }
}