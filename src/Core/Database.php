<?php

namespace Websyspro\Entity\Core;

use PDO;
use PDOStatement;

class Database
{
  private static PDO $handle;
  private static array $statements = [];

  public static function connect(
  ): PDO { 
    if( isset( self::$handle ) === false ){
      self::$handle = new PDO( 
        self::getConnectString( HOSTNAME, DATABASE ), USERNAME, PASSWORD, [
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_EMULATE_PREPARES => false
        ]
      );
    }

    return self::$handle;
  }

  private static function getConnectString(
    string $hostname,
    string $database
  ): string {
    return "sqlsrv:Server={$hostname};Database={$database}";
  }

  public static function getDriver(
  ): string|null {
    return self::connect()->getAttribute(
      PDO::ATTR_DRIVER_NAME
    );
  }

  public static function query(
    string $sql,
    array $statementsParams
  ): array {
    self::connect();

    if( isset( self::$statements[ $sql ]) === false){
      self::$statements[ $sql ] = self::connect()->prepare($sql);
    }

    $statements = self::$statements[ $sql ];
    $statements->execute( $statementsParams );
    $statementsResults = $statements->fetchAll();

    $statements->closeCursor();
    return $statementsResults;
  }
}