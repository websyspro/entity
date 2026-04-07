<?php

namespace Websyspro\Entity\Core;

use PDO;

class Database
{
  private static PDO $connect;

  public static function connect(
  ): void { 
    if( isset( Database::$connect ) === false ){
      // Database::$connect = new PDO( "mysql:host=localhost;dbname=edocente;charset=utf8mb4", "root", "qazwsx" );
      Database::$connect = new PDO( "sqlsrv:Server=localhost;Database=pnld_crm_api_production", "sa", "@Qazwsx190483" );
      Database::$connect->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    }
  }

  public static function getDriver(
  ): string|null {
    Database::connect();
    if( isset( Database::$connect ) === false ){
      return null;
    }
    
    return Database::$connect->getAttribute(
      PDO::ATTR_DRIVER_NAME
    );
  }

  public static function query(
    string $sql,
    array $prepareds
  ): array {
    Database::connect();

    $stmt = Database::$connect->prepare( $sql );
    $stmt->execute( $prepareds );

    return $stmt->fetchAll( PDO::FETCH_ASSOC );
  }  
}