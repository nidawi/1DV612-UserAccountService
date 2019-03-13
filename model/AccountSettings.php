<?php

namespace model;

class AccountSettings {

  private $account;
  private $orgName;

  private $notificationsEnabled;
  private $issuesEnabled;
  private $issueCommentEnabled;
  private $organizationEnabled;
  private $pushEnabled;
  private $releaseEnabled;
  private $repositoryEnabled;

  public function __construct(array $settingsData) {
    $this->account = $this->setStringValue($settingsData["account"] ?? "");
    $this->orgName = $this->setStringValue($settingsData["orgCode"] ?? $settingsData["orgName"] ?? "");

    $this->notificationsEnabled = $this->setBooleanValue($settingsData["notifications_enabled"] ?? $settingsData["notificationsEnabled"] ?? null);
    $this->issuesEnabled = $this->setBooleanValue($settingsData["issues_enabled"] ?? $settingsData["issuesEnabled"] ?? null);
    $this->issueCommentEnabled = $this->setBooleanValue($settingsData["issue_comment_enabled"] ?? $settingsData["issueCommentEnabled"] ?? null);
    $this->organizationEnabled = $this->setBooleanValue($settingsData["organization_enabled"] ?? $settingsData["organizationEnabled"] ?? null);
    $this->pushEnabled = $this->setBooleanValue($settingsData["push_enabled"] ?? $settingsData["pushEnabled"] ?? null);
    $this->releaseEnabled = $this->setBooleanValue($settingsData["release_enabled"] ?? $settingsData["releaseEnabled"] ?? null);
    $this->repositoryEnabled = $this->setBooleanValue($settingsData["repository_enabled"] ?? $settingsData["repositoryEnabled"] ?? null);
  }

  public function getOrgName() : string { return $this->orgName; }
  public function getNotificationsEnabled() { return $this->notificationsEnabled; }
  public function getIssuesEnabled() { return $this->issuesEnabled; }
  public function getCommentsEnabled() { return $this->issueCommentEnabled; }
  public function getOrganizationEnabled() { return $this->organizationEnabled; }
  public function getPushEnabled() { return $this->pushEnabled; }
  public function getReleaseEnabled() { return $this->releaseEnabled; }
  public function getRepositoryEnabled() { return $this->repositoryEnabled; }

  public function toArray() : array {
    return array(
      "account" => $this->account,
      "orgCode" => $this->orgName,
      "notifications_enabled" => $this->getNotificationsEnabled(),
      "issues_enabled" => $this->getIssuesEnabled(),
      "issue_comment_enabled" => $this->getCommentsEnabled(),
      "organization_enabled" => $this->getOrganizationEnabled(),
      "push_enabled" => $this->getPushEnabled(),
      "release_enabled" => $this->getReleaseEnabled(),
      "repository_enabled" => $this->getRepositoryEnabled()
    );
  }

  private function setStringValue(string $value) {
    if (!isset($value) || $value === "" || strlen($value) > 225) {
      throw new \InvalidArgumentException();
    }
    return $value;
  }

  private function setBooleanValue($value) {
    if (isset($value)) {
      return boolval($value);
    } else return null;
  }
}