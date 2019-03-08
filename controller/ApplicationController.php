<?php

namespace controller;

require_once 'AccountCreationController.php';
require_once 'AccountRetrievalController.php';
require_once 'ContactController.php';
require_once 'ItemsController.php';
require_once 'AccountUpdateController.php';
require_once __DIR__ . '/../view/NavigatorView.php';
require_once __DIR__ . '/../view/GetAccountView.php';
require_once __DIR__ . '/../view/CreateAccountView.php';
require_once __DIR__ . '/../view/ItemsView.php';
require_once __DIR__ . '/../view/ContactView.php';
require_once __DIR__ . '/../view/UpdateAccountView.php';

class ApplicationController {

  private $creationController;
  private $retrievalController;
  private $itemsController;
  private $contactController;
  private $updateController;

  private $navigatorView;
  private $createAccountView;
  private $getAccountView;
  private $itemsView;
  private $contactView;
  private $updateView;

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
      else if ($this->getAccountView->userWantsToViewAccount())
        $this->retrievalController->doRetrieveAccount();
      else if ($this->updateView->userWantsToUpdateAccount())
        $this->updateController->doUpdateAccount();
      else
        $this->navigatorView->userRequestsInexistantResource();

    } catch (\Exception $err) {
      $this->navigatorView->serverFailure();
    }
  }

  private function createControllers(\model\AccountRegister $register) {
    $this->creationController = new \controller\AccountCreationController($register, $this->createAccountView);
    $this->retrievalController = new \controller\AccountRetrievalController($register, $this->getAccountView);
    $this->itemsController = new \controller\ItemsController($register, $this->itemsView);
    $this->contactController = new \controller\ContactController($register, $this->contactView);
    $this->updateController = new \controller\AccountUpdateController($register, $this->updateView);
  }
  private function createViews() {
    $this->navigatorView = new \view\Navigator();
    $this->createAccountView = new \view\CreateAccountView($this->navigatorView);
    $this->getAccountView = new \view\GetAccountView($this->navigatorView);
    $this->itemsView = new \view\ItemsView($this->navigatorView);
    $this->contactView = new \view\ContactView($this->navigatorView);
    $this->updateView = new \view\UpdateAccountView($this->navigatorView);
  }
}