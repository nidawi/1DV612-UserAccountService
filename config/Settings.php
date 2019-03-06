<?php

date_default_timezone_set(\Environment::DEFAULT_TIME_ZONE);

if (\Environment::APPLICATION_STATUS === "development") {
  error_reporting(E_ALL);
  ini_set('display_errors', 'On');
  ini_set("error_log", "/var/log/php-errors.log");
}