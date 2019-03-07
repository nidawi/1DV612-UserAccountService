<?php

namespace controller;

require_once 'AccountCreationController.php';
require_once 'AccountRetrievalController.php';
require_once 'ContactController.php';
require_once 'ItemsController.php';
require_once __DIR__ . '/../view/NavigatorView.php';
require_once __DIR__ . '/../view/GetAccountView.php';
require_once __DIR__ . '/../view/CreateAccountView.php';
require_once __DIR__ . '/../view/ItemsView.php';
require_once __DIR__ . '/../view/ContactView.php';

class ApplicationController {

  private $creationController;
  private $retrievalController;
  private $itemsController;
  private $contactController;

  private $navigatorView;
  private $createAccountView;
  private $getAccountView;
  private $itemsView;
  private $contactView;

  public function __construct(\model\AccountRegister $register) {
    $this->createViews();
    $this->createControllers($register);
  }

  public function run() {
    try {

      if ($this->itemsView->userWantsToAccessAccountItems())
        $this->itemsController->doItemInteractions();
      else if ($this->contactView->userWantsToAccessContactMethods())
        $this->contactController->doContactInteraction();
      else if ($this->createAccountView->userWantsToCreateAccount())
        $this->creationController->doCreateAccount();
      else
        $this->retrievalController->doRetrieveAccount();

    } catch (\Exception $err) {
      $this->navigatorView->serverFailure();
    }
  }

  private function createControllers(\model\AccountRegister $register) {
    $this->creationController = new \controller\AccountCreationController($register, $this->createAccountView);
    $this->retrievalController = new \controller\AccountRetrievalController($register, $this->getAccountView);
    $this->itemsController = new \controller\ItemsController($register, $this->itemsView);
    $this->contactController = new \controller\ContactController($register, $this->contactView);
  }
  private function createViews() {
    $this->navigatorView = new \view\Navigator();
    $this->createAccountView = new \view\CreateAccountView($this->navigatorView);
    $this->getAccountView = new \view\GetAccountView($this->navigatorView);
    $this->itemsView = new \view\ItemsView($this->navigatorView);
    $this->contactView = new \view\ContactView($this->navigatorView);
  }
}