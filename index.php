<?php

define( "BASEDIR", __DIR__ );
define( "BASEDIR_APP", BASEDIR . "/src" );

if(!defined( "CACHE_DISABLED" )) 
  define( "CACHE_DISABLED", false );
if(!defined( "HOSTNAME" )) 
  define( "HOSTNAME", "localhost" );
if(!defined( "DATABASE" )) 
  define( "DATABASE", "pnld_crm_api_production" );
if(!defined( "USERNAME" )) 
  define( "USERNAME", "sa" );
if(!defined( "PASSWORD" )) 
  define( "PASSWORD", "@Qazwsx190483" );

require_once dirname(__FILE__) . "/vendor/autoload.php";
require_once dirname(__FILE__) . "/test/main.php";