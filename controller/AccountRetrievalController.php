<?php

namespace controller;

class AccountRetrievalController {

  private $register;
  private $view;

  public function __construct(\model\AccountRegister $register, \view\GetAccountView $view) {
    $this->register = $register;
    $this->view = $view;
  }

  public function doRetrieveAccount() {
    try {
      $accountName = $this->view->getDesiredAccount();
      $account = $this->register->getAccount($accountName);
      $this->view->accountRetrievalSuccessful($account);
    } catch (\Exception $err) {
      $this->view->accountRetrievalUnsuccessful($err);
    }
  }

  public function doRetrieveAccountItems() {
    
  }
}