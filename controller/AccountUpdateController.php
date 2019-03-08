<?php

namespace controller;

class AccountUpdateController {

  private $register;
  private $view;

  public function __construct(\model\AccountRegister $register, \view\UpdateAccountView $view) {
    $this->register = $register;
    $this->view = $view;
  }

  public function doUpdateAccount() {
    try {
      $account = $this->view->getDesiredAccount();
      $accountUpdates = $this->view->getUpdates();
      $this->register->updateAccountGithubData($account, $accountUpdates);
      $this->view->accountUpdateSuccessful();
    } catch (\Exception $err) {
      $this->view->accountUpdateUnsuccessful($err);
    }
  }
}