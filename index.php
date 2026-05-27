<?php

if( defined( "BASEDIR" ) === false ){
  define( "BASEDIR", __DIR__ );
}

if( defined( "BASEDIR_APP" ) === false ){
  define( "BASEDIR_APP", BASEDIR . "/src" );
}

require_once dirname(__FILE__) . "/vendor/autoload.php";
require_once dirname(__FILE__) . "/test/main.php";