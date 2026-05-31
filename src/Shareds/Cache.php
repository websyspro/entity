<?php

namespace Websyspro\Entity\Shareds;

use function sprintf, in_array, count;

class Cache
{
  private const string CACHE_DIRETOCTORY_DEFAULT = "Cache";

  private static function path(
    string|null $file = null
  ): string {
    $path = BASEDIR_APP . DIRECTORY_SEPARATOR . Cache::CACHE_DIRETOCTORY_DEFAULT;

    if(is_dir($path) === false){
      mkdir($path, 0777, true);
    }
    
    return $file !== null 
      ? $path . DIRECTORY_SEPARATOR . $file 
      : $path;
  }

  public static function file(
    string $name,
    array $context
  ): string {
    return sprintf( "%s/%s-%s.php", 
      Cache::path(), 
      md5( $name ), md5( serialize( $context ))
    );
  }

  public static function exist(
    string $name,
    array $contexts
  ): bool {
    if(CACHE_DISABLED === true){
      return false;
    }

    $findName = self::path( 
      sprintf('%s-*.php', md5( $name ))
    );
    
    $finds = glob( $findName );
    if( count( $finds ) !== 0 ){
      return true;
    }

    return false;
  }
  
  public static function save(
    string $name,
    array $contexts
  ): array {
    file_put_contents( 
      Cache::file( $name, $contexts ), sprintf(
        "<?php\n\nreturn %s;", var_export($contexts, true)
      ), LOCK_EX
    );

    return $contexts;
  }

  public static function delete(
    string $name
  ): void {
    foreach( scandir( self::path()) as $file ){
      if( in_array( $file, [ '.', '..' ])){
        continue;
      }

      if( str_starts_with( $file, md5($name))){
        unlink( self::path() . DIRECTORY_SEPARATOR . $file );
      }
    }
  }  
  
  public static function load(
    string $name,
    array $contexts
  ): array {
    return require Cache::file( $name, $contexts );
  }  
}