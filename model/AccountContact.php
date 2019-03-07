<?php

namespace model;

class AccountContact {
  
  private $account;
  private $type;
  private $value;
  private $enabled;

  public function __construct(array $contactData) {
    $this->account = $contactData["account"] ?? "";
    $this->setType($contactData["type"]);
    $this->setValue($contactData["value"]);
    $this->enabled = boolval($contactData["enabled"] ?? "");
  }

  public function setType(string $type) {
    if (!isset($type) || strlen($type) < 2)
      throw new InvalidValueException("type");
    $this->type = $type;
  }
  public function setValue(string $data) {
    if (!isset($data) || strlen($data) < 2)
      throw new InvalidValueException("value");
    $this->value = $data;
  }

  public function isEnabled() : bool {
    return $this->enabled;
  }
  public function getType() : string {
    return $this->type;
  }
  public function getValue() : string {
    return $this->value;
  }

  public function toArray() : array {
    return array(
      "type" => $this->type,
      "value" => $this->value,
      "enabled" => $this->enabled
    );
  }
}