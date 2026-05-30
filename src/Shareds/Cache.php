<?php

namespace Websyspro\Entity\Shareds;

use function sprintf;

class Cache
{
  private const string CACHE_DIRETOCTORY_DEFAULT = "Cache";

  private static function cacheDirectory(
  ): string {
    if(is_dir(BASEDIR_APP . DIRECTORY_SEPARATOR . Cache::CACHE_DIRETOCTORY_DEFAULT) === false){
      mkdir(BASEDIR_APP . DIRECTORY_SEPARATOR . Cache::CACHE_DIRETOCTORY_DEFAULT, 0777, true);
    }
    
    return BASEDIR_APP . DIRECTORY_SEPARATOR . Cache::CACHE_DIRETOCTORY_DEFAULT;
  }

  public static function file(
    string $type,
    string $cacheName 
  ): string {
    return sprintf( "%s/%s-%s.php", 
      Cache::cacheDirectory(), $type, md5( $cacheName )
    );
  }

  public static function exist(
    string $type,
    string $cacheName
  ): bool {
    if(CACHE_DISABLED === true){
      return false;
    }

    return file_exists( Cache::file( $type, $cacheName ));
  }
  
  public static function save(
    array $cacheContext, 
    string $type,
    string $cacheName,
  ): array {
    file_put_contents( 
      Cache::file( $type, $cacheName ), sprintf(
        "<?php\n\nreturn %s;", var_export($cacheContext, true)
      ), LOCK_EX
    );

    return $cacheContext;
  }
  
  public static function load(
    string $type,
    string $cacheName
  ): array {
    return require Cache::file( $type, $cacheName );
  }  
}