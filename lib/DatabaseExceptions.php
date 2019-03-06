<?php

namespace lib;

class DatabaseConnectionFailureException extends \Exception {}
class InvalidDatabaseTypeException extends \Exception {}
class DatabaseInternalFailureException extends \Exception {}
class DatabaseConnectionAlreadyOpenException extends \Exception {}