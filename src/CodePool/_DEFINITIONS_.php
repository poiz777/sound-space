<?php
	/**
	 * Created by PhpStorm.
	 * User: poizcampbell
	 * Date: 21/04/16
	 * Time: 10:55
	 */

	namespace App\CodePool;

	class _DEFINITIONS_{}

	defined("CODE_POOL")        or define("CODE_POOL",          __DIR__);
	defined("WEB_ROOT")         or define("WEB_ROOT",           __DIR__     . "/../../");
	defined("APP_ROOT")         or define("APP_ROOT",           __DIR__     . "/../../");
	defined("SRC_ROOT")         or define("SRC_ROOT",           __DIR__     . "/../");
	defined("ENTITY_ROOT")      or define("ENTITY_ROOT",        APP_ROOT    . "Entity");


	//DATABASE CONNECTION CONFIGURATION:
	defined("ADAPTER")          or define("ADAPTER",            "sqlite");
	defined("HOST")             or define("HOST",               "localhost");
	defined("DB_FILE")          or define("DB_FILE",            APP_ROOT    . "var/poiz.db");
	defined("DSN_SQLITE")       or define("DSN_SQLITE",         'sqlite:'       . DB_FILE);

