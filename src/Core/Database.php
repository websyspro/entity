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
      //Database::$connect = new PDO();
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