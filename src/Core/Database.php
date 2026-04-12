<?php

namespace Websyspro\Entity\Core;

use PDO;
use PDOStatement;
use Websyspro\Commons\Collection;
use Websyspro\Entity\Shareds\HierarchyBuilder;

class Database
{
  private static PDO $handle;

  public static function connect(
  ): PDO { 
    if( isset( Database::$handle ) === false ){
      Database::$handle = new PDO( "sqlsrv:Server=localhost;Database=pnld_crm_api_production", "sa", "@Qazwsx190483" );
      Database::$handle = new PDO( "mysql:host=localhost;dbname=edocente;charset=utf8mb4", "root", "qazwsx" );
      Database::$handle->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    }

    return Database::$handle;
  }

  public static function getDriver(
  ): string|null {
    Database::connect();
    if( isset( Database::$handle ) === false ){
      return null;
    }
    
    return Database::$handle->getAttribute(
      PDO::ATTR_DRIVER_NAME
    );
  }

  private static function hierarchyBuilder(
    array $joins
  ): HierarchyBuilder {
    return new HierarchyBuilder( $joins );
  }

  public static function query(
    string $sql,
    array $prepareds,
    array $joins = []
  ): array {
    if( Database::connect() instanceof PDO ){
      if( empty( $prepareds ) === false ){
        $stmt = Database::$handle->prepare( $sql );
        $stmt->execute( $prepareds );
      } else {
        $stmt = Database::$handle->query( $sql );
      }

      if( $stmt instanceof PDOStatement ){
        return Database::hierarchyBuilder( $joins )->build(
          $stmt->fetchAll( PDO::FETCH_ASSOC )
        );
      }
    }

    return [];
  }
}