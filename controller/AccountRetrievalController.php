<?php

namespace controller;

class AccountRetrievalController {

  private $register;
  private $view;

  public function __construct(\model\AccountRegister $register, \view\GetAccountView $view) {
    $this->register = $register;
    $this->view = $view;
  }

  public function doAccessAccountRetrieval() {
    try {
      if ($this->view->userWantsToViewAccountsSubscribedToOrganization())
        $this->doGetAccounts();
      else if ($this->view->userWantsToAuthenticateAccount())
        $this->doAuthenticateAccount();
      else
        $this->doRetrieveAccount();
      
    } catch (\Exception $err) {
      $this->view->accountRetrievalUnsuccessful($err);
    }
  }
  
  private function doRetrieveAccount() {
    $accountName = $this->view->getDesiredAccount();
    $account = $this->register->getAccount($accountName);
    $this->view->accountRetrievalSuccessful($account);
  }

  private function doGetAccounts() {
    $org = $this->view->getSelectedOrganization();
    $result = $this->register->getAccounts($org);
    $this->view->accountsRetrievedSuccessfully($result);
  }

  private function doAuthenticateAccount() {
    $credentials = $this->view->getAuthentication();
    $account = $this->register->authenticateAccount($credentials);
    $this->view->accountRetrievalSuccessful($account);
  }
}