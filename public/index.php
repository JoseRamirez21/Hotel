<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

require_once dirname(__DIR__) . '/app/Helpers/functions.php';

use App\Core\App;
use App\Core\Config;

Config::load();

App::run();