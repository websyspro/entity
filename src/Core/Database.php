<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\Collection;
use PDO;

class Database
{
  private static PDO $connect;

  public static function connect(
  ): void { 
    if( isset( Database::$connect ) === false ){
      // Database::$connect = new PDO("mysql:host=localhost;port=3307;dbname=shops;charset=utf8mb4", "root", "qazwsx");
      Database::$connect = new PDO("sqlsrv:Server=production-apps-sqlserver-7b4c37df.cqpasqcacfg3.us-east-1.rds.amazonaws.com,1433;Database=pnld_crm_api_production", "pnld-crm-api", "e6VDdL4bwRej04X}}");
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
  ): Collection {
    Database::connect();

    $stmt = Database::$connect->prepare( $sql );
    $stmt->execute( $prepareds );

    return new Collection( $stmt->fetchAll( PDO::FETCH_ASSOC ));
  }  
}