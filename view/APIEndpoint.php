<?php

namespace view;

class APIEndpoint {

  private static $accountLink = "account";


  protected function getAccountLink() : string {
    return self::$accountLink;
  }

  protected function isRequestGETHeaderPresent(string $header) : bool {
    return array_key_exists($header, $_GET);
  }
  protected function isRequestPOSTHeaderPresent(string $header) : bool {
    return array_key_exists($header, $_POST);
  }
  protected function getQueryValue(string $key) : string {
    return $_GET[$key];
  }

  protected function isValidRequestMethod() : bool {
    return ($this->isRequestMethod("POST") || $this->isRequestMethod("GET"));
  }
  protected function isRequestMethod(string $method) : bool {
    return $this->getRequestMethod() === $method;
  }
  protected function getRequestMethod() : string {
    return $_SERVER['REQUEST_METHOD'];
  }
}