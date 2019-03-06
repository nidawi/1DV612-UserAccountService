<?php

namespace model;

class AccountStoredItem {
  
  private $account;
  private $eventType;
  private $data;
  private $createdat;

  public function __construct(array $itemData) {
    $this->account = $itemData["account"] ?? "";
    $this->setEventType($itemData["eventType"]);
    $this->setData($itemData["data"]);
    $this->createdat = $itemData["createdat"] ?? "";
  }

  public function setEventType(string $type) {
    if (!isset($type) || strlen($type) < 2)
      throw new InvalidValueException("eventType");
    $this->eventType = $type;
  }
  public function setData(string $data) {
    if (!isset($data) || strlen($data) < 2)
      throw new InvalidValueException("data");
    $this->data = $data;
  }

  public function getEventType() : string {
    return $this->eventType;
  }
  public function getData() : string {
    return $this->data;
  }

  public function toArray() : array {
    return array(
      "eventType" => $this->eventType,
      "data" => $this->data,
      "date" => $this->createdat
    );
  }
}