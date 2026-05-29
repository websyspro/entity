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
    string $cacheName,
    array $cacheValue = [] 
  ): string {
    return sprintf( "%s/cache-%s-%s.php", 
      Cache::cacheDirectory(), $cacheName, md5( serialize( $cacheValue ))
    );
  }

  public static function exist(
    string $cacheName,
    array $cacheValue = [] 
  ): bool {
    if(CACHE_DISABLED === true){
      return false;
    }

    return file_exists(Cache::file($cacheName, $cacheValue));
  }
  
  public static function save(
    string $cacheName,
    array $cacheValue = [],
    array $cacheContext = [] 
  ): array {
    file_put_contents( 
      Cache::file($cacheName, $cacheValue), sprintf(
        "<?php\n\nreturn %s;", var_export($cacheContext, true)
      ), LOCK_EX
    );

    return $cacheContext;
  }
  
  public static function load(
    string $cacheName,
    array $cacheValue = []
  ): array {
    return require Cache::file($cacheName, $cacheValue);
  }  
}