<?php

namespace Websyspro\Entity\Core;

use PDO;
use PDOStatement;
use Websyspro\Entity\Shareds\HierarchyBuilder;

class Database
{
  private static PDO $handle;

  public static function connect(
  ): PDO { 
    if( isset( Database::$handle ) === false ){
      Database::$handle = new PDO( "sqlsrv:Server=localhost;Database=pnld_crm_api_production", "sa", "@Qazwsx190483" );
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

  public static function query(
    string $sql,
    array $prepareds,
    array $parameterQuery = []
  ): array {
    if( sizeof( $prepareds ) === 0 ){
      return [];
    }

    if( Database::connect() instanceof PDO ){
      $stmt = Database::$handle->prepare( $sql );

      if( $stmt instanceof PDOStatement ){
        $stmt->execute( $prepareds );
        
        return ( new HierarchyBuilder( $parameterQuery ))->build(
          $stmt->fetchAll( PDO::FETCH_ASSOC )
        );
      }
    }

    return [];
  }
}