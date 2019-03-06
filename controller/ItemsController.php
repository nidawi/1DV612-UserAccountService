<?php

namespace controller;

class ItemsController {

  private $register;
  private $view;

  public function __construct(\model\AccountRegister $register, \view\ItemsView $view) {
    $this->register = $register;
    $this->view = $view;
  }

  public function doItemInteractions() {
    try {
      $username = $this->view->getDesiredAccount();

      if ($this->view->userWantsToAddAccountItems())
        $this->doAddItems($username);
      else if ($this->view->userWantsToDeleteAccountItems())
        $this->doDeleteItems($username);
      else
        $this->doGetItems($username);

    } catch (Exception $err) {
      $this->view->itemInteractionFailed($err);
    } catch (\Error $error) {
      $this->view->itemInteractionFailed($err);
    }
  }

  private function doGetItems(string $username) {
    $accountItems = $this->register->getAccountStoredItems($username);
    $this->view->itemRetrievalSuccessful($accountItems);
  }
  private function doAddItems(string $username) {
    $itemsToAdd = $this->view->getItemsToAdd();
    $this->register->addAccountStoredItems($username, $itemsToAdd);
    $this->view->itemCreationSuccessful();
  }
  private function doDeleteItems(string $username) {
    $this->register->clearAccountStoredItems($username);
    $this->view->itemDeletionSuccessful();
  }
}