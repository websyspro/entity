<?php

namespace Websyspro\Test\Connect;

use Websyspro\Commons\Collection;
use PDO;

class DB
{
  private static PDO $handle;

  public static function connect(
  ): void { 
    if( isset( DB::$handle ) === false ){
      DB::$handle = new PDO("mysql:host=localhost;port=3307;dbname=shops;charset=utf8mb4", "root", "qazwsx");
      DB::$handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
  }

  public static function query(
    string $query
  ): Collection {
    DB::connect();

    $stmt = DB::$handle->query($query);
    return new Collection( $stmt->fetchAll( PDO::FETCH_ASSOC ));
  }

  public static function queryWithPrepared(
    string $sql,
    Collection $prepareds
  ): Collection {
    DB::connect();

    $stmt = DB::$handle->prepare( $sql );
    $stmt->execute( $prepareds->toArray() );

    return new Collection( $stmt->fetchAll( PDO::FETCH_ASSOC ));
  }  
}