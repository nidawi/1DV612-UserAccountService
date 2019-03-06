<?php

namespace controller;

class AccountCreationController {

  private $register;
  private $view;

  public function __construct(\model\AccountRegister $register, \view\CreateAccountView $view) {
    $this->register = $register;
    $this->view = $view;
  }

  public function doCreateAccount() {
    try {
      $userCreds = $this->view->getAccountCredentials();
      $this->register->createAccount($userCreds);
      $this->view->accountCreationSuccessful();
    } catch (\Exception $err) {
      $this->view->accountCreationUnsuccessful($err);
    }
  }
}