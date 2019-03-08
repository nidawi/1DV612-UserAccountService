<?php

namespace view;

// This deals with overall application structure and functionality, such as JWTs.
class Navigator {

  private static $accountLink = "account";
  private static $acceptedContentTypes = array("application/json", "text/html");
  private static $providedContentType = "application/json";
  
  private static $accountItemsLink = "items";
  private static $contactMethodLink = "contact";

  private $activeJwt;

  public function __construct() {
    // This is a JSON API
    header('Content-Type: ' . self::$providedContentType);

    if (!$this->isValidAPICall())
      $this->userRequestsInexistantResource();

    if (!$this->isValidRequestMethod())
      $this->userAttemptedUnallowedMethod();
    
    if (!$this->isValidAcceptedContentType())
      $this->userMadeUnacceptableRequest();

    // Verify the jwt.
    if (!$this->isAuthorizationProvided())
      $this->userIsUnauthorized();
    else
      $this->verifyJWT();
  }

  public function isPOST() : bool {
    return $this->isRequestMethod("POST");
  }
  public function isGET() : bool {
    return $this->isRequestMethod("GET");
  }
  public function isDELETE() : bool {
    return $this->isRequestMethod("DELETE");
  }

  public function getItemsLink() : string {
    return self::$accountItemsLink;
  }
  public function getContactsLink() : string {
    return self::$contactMethodLink;
  }

  public function isValidAPICall() : bool {
    return $this->isQueryPresent(self::$accountLink);
  }
  public function isQueryPresent(string $key) : bool {
    return array_key_exists($key, $_GET);
  }
  public function getRelevantAccount() : string {
    return $_GET[self::$accountLink];
  }

  public function userMadeBadRequest() {
    $this->errorOccured(400);
  }
  public function userIsUnauthorized() {
    $this->errorOccured(401);
  }
  public function userAccessForbidden() {
    $this->errorOccured(403);
  }
  public function userRequestsInexistantResource() {
    $this->errorOccured(404);
  }
  public function userAttemptedUnallowedMethod() {
    $this->errorOccured(405);
  }
  public function userAccessedUnimplementedFeature() {
    $this->errorOccured(501);
  }
  public function userMadeUnacceptableRequest() {
    $this->errorOccured(406);
  }
  public function serverFailure() {
    $this->errorOccured(500);
  }
  
  public function errorOccured(int $statusCode, string $message = null) {
    http_response_code($statusCode);
    $errorMessage = $message ?? $this->getStatusCodeMessage($statusCode);
    
    echo json_encode(array("code" => $statusCode, "message" => $errorMessage));
    die();
  }
  private function getStatusCodeMessage(int $statusCode) : string {
    switch ($statusCode) {
      case 400: return "Bad Request";
      case 401: return "Unauthorized";
      case 403: return "Forbidden";
      case 404: return "Not Found";
      case 405: return "Method Not Allowed";
      case 406: return "Unacceptable";
      case 501: return "Not Implemented";
      case 500: default: return "Internal Server Error";
    }
  }

  private function isAuthorizationProvided() : bool {
    return (isset($_SERVER['HTTP_AUTHORIZATION']) && preg_match("/Bearer/", $_SERVER['HTTP_AUTHORIZATION']));
  }
  private function isValidAcceptedContentType() : bool {
    return (isset($_SERVER["HTTP_ACCEPT"]) && in_array($_SERVER["HTTP_ACCEPT"], self::$acceptedContentTypes));
  }
  private function isValidRequestMethod() : bool {
    return ($this->isRequestMethod("POST") || $this->isRequestMethod("GET") || $this->isRequestMethod("DELETE"));
  }
  private function isRequestMethod(string $method) : bool {
    return $this->getRequestMethod() === $method;
  }
  private function getRequestMethod() : string {
    return $_SERVER['REQUEST_METHOD'];
  }

  private function verifyJWT() {
    try {
      if ($this->isAuthorizationProvided()) {
        $token = preg_filter("/Bearer /", "", $_SERVER['HTTP_AUTHORIZATION']);
        $this->activeJwt = \lib\jwt::verify(($token));
      } else {
        $this->userIsUnauthorized();
      }
    } catch (\Exception $err) {
      $this->userIsUnauthorized();
    }
  }
}