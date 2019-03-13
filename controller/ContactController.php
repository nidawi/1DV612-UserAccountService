<?php

namespace controller;

class ContactController {

  private $register;
  private $view;

  public function __construct(\model\AccountRegister $register, \view\ContactView $view) {
    $this->register = $register;
    $this->view = $view;
  }

  public function doContactInteraction() {
    try {
      $username = $this->view->getDesiredAccount();

      if ($this->view->userWantsToAddContactMethods())
        $this->doAddContactMethods($username);
      else if ($this->view->userWantsToUpdateContactMethod())
        $this->doUpdateContactMethod($username);
      else if ($this->view->userWantsToDeleteContactMethods())
        $this->doDeleteContactMethod($username);
      else
        $this->doGetContactMethods($username);


    } catch (\Exception $err) {
      $this->view->methodInteractionFailed($err);
    } catch (\Error $error) {
      $this->view->methodInteractionFailed($error);
    }
  }

  private function doGetContactMethods(string $username) {
    $contactMethods = $this->register->getAccountContactMethods($username);
    $this->view->methodRetrievalSuccessful($contactMethods);
  }

  private function doAddContactMethods(string $username) {
    $methodsToAdd = $this->view->getMethodsToAdd();
    if (count($methodsToAdd) < 1)
      throw new \view\NothingToAddException();
    
    $this->register->addAccountContactMethods($username, $methodsToAdd);
    $this->view->methodCreationSuccessful();
  }

  private function doUpdateContactMethod(string $username) {
    $updates = $this->view->getMethodUpdate();
    if (!isset($updates)) {
      throw new \view\NothingToUpdateException();
    }
    
    $this->register->updateAccountContactMethod($username, $updates);
    $this->view->methodUpdateSuccessful();
  }

  private function doDeleteContactMethod(string $username) {
    $typeToDelete = $this->view->getSelectedMethodType();
    if (!isset($typeToDelete) || strlen($typeToDelete) < 2)
      throw new \view\NothingToDeleteException();

    $this->register->deleteAccountContactMethod($username, $typeToDelete);
    $this->view->methodDeletionSuccessful();
  }
}