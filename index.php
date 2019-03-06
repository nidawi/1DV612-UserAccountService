<?php
// User Account Service

// Load Stuff
require_once __DIR__ . '/Environment.php';
require_once 'config/Settings.php';

require_once 'lib/Database.php';
require_once 'lib/jwt.php';

require_once 'model/AccountRegister.php'; // Also loads Accounts.php
require_once 'model/ModelErrors.php';

require_once 'controller/ApplicationController.php';

// Create Necessary Objects
$database = new \lib\Database(\Environment::DATABASE_ADDRESS, \Environment::DATABASE_USER, \Environment::DATABASE_PASSWORD, \Environment::DATABASE_DB);
$accountRegister = new \model\AccountRegister($database);

// Create Controllers
$app = new \controller\ApplicationController($accountRegister);

// Run App
$app->run();

// Finish up, just in case.
$database->kill();