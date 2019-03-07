<?php

namespace view;

class GetAccountView {

  private $navigator;

  public function __construct(\view\Navigator $navigator) {
    $this->navigator = $navigator;
  }

  public function getDesiredAccount() : string {
    return $this->navigator->getRelevantAccount();
  }

  public function accountRetrievalSuccessful(\model\Account $account) {
    http_response_code(200);

    $response = array_merge(
      $account->toArray(),
      array(
        "_itemslink" => $this->createItemLink(),
        "_contactlink" => $this->createContactLink()
      )
    );
    echo json_encode($response);
  }
  public function accountRetrievalUnsuccessful(\Exception $err) {
    $message =  "Account Retrieval failed due to an unknown error.";
    $code = 500;
    switch (true) {
      case $err instanceof \model\AccountDoesNotExistException:
        $message = "The requested account does not exist.";
        $code = 404;
        break;
    }
    $this->navigator->errorOccured($code, $message);
  }

  private function createItemLink() : string {
    return "/?" . $_SERVER["QUERY_STRING"] . "&" . $this->navigator->getItemsLink();
  }
  private function createContactLink() : string {
    return "/?" . $_SERVER["QUERY_STRING"] . "&" . $this->navigator->getContactsLink();
  }
}