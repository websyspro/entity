<?php

namespace Websyspro\Entity\Shareds;

use function sprintf;

class Cache
{
  private static array $cacheContexts = [];
  private const string CACHE_DIRETOCTORY_DEFAULT = "Cache";

  private static function path(
    string|null $file = null
  ): string {
    $path = implode( DIRECTORY_SEPARATOR, [
      BASEDIR_APP, self::CACHE_DIRETOCTORY_DEFAULT
    ]);
    
    return $file !== null 
      ? implode( DIRECTORY_SEPARATOR, [ $path, $file ])  
      : $path;
  }

  public static function file(
    string $file
  ): string {
    return self::path(
      "{$file}.php"
    );
  }

  public static function exist(
    string $file
  ): bool {
    if( CACHE_DISABLED === true ){
      return false;
    }

    if( isset( self::$cacheContexts[ $file ])){
      return true;
    }

    return file_exists(
      self::file( $file )
    );
  }
  
  public static function save(
    string $signary,
    array $contexts
  ): array {
    file_put_contents( 
      self::path( "{$signary}.php"), sprintf(
        "<?php%s%sreturn %s;", PHP_EOL, PHP_EOL, var_export($contexts, true)
      ), LOCK_EX
    );

    self::$cacheContexts[ $signary ] = $contexts;
    return self::$cacheContexts[ $signary ];
  }

  public static function delete(
    string $file
  ): void {
    unset( self::$cacheContexts[ $file ]);
    if( file_exists( self::file( $file ))){
      unlink( self::file( $file ));
    }
  }  
  
  public static function load(
    string $file
  ): array {
    if( isset( self::$cacheContexts[ $file ])){
      return self::$cacheContexts[ $file ];
    }

    self::$cacheContexts[ $file ] = require self::file( $file );
    return self::$cacheContexts[ $file ];
  }

  public static function entity(
    string $entity
  ): array {
    $hash = sprintf(
      "orm-entity-%s", md5( $entity )
    );

    $cacheFile = @include self::file($hash); 
    if( $cacheFile !== false ){
      return $cacheFile;
    }

    $entityStructure = new EntityStructure( $entity );
    return self::save( $hash, $entityStructure->contexts );
  }

  public static function getWhereOrNull(
    string $signary
  ): array|false {
    calcTimer( "Criar string ORM_WHERE dentro de getWhereOrNull" );
    $hash = "orm-where-{$signary}";
    calcTimer( "FIM string ORM_WHERE dentro de getWhereOrNull" );
    
    calcTimer( "Executar @include self::file( \$hash )" );
    $file = @include self::file( $hash );
    calcTimer( "Fim Executar @include self::file( \$hash )" );
    return $file;
  }
}