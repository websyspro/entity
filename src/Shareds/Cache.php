<?php

namespace Websyspro\Entity\Shareds;

use function sprintf;

class Cache
{
  private const string CACHE_DIRETOCTORY_DEFAULT = "Cache";

  private static function cacheDirectory(
  ): void {
    if(file_exists(BASEDIR_APP . DIRECTORY_SEPARATOR . Cache::CACHE_DIRETOCTORY_DEFAULT) === false){
      mkdir(BASEDIR_APP . DIRECTORY_SEPARATOR . Cache::CACHE_DIRETOCTORY_DEFAULT);
    }    
  }

  public static function cache(
    string $cacheName,
    array $cacheValue = [] 
  ): string {
    Cache::cacheDirectory();

    return sprintf( "%s/cache/cache-%s-%s.php", 
      BASEDIR_APP, $cacheName, md5( serialize( $cacheValue ))
    );
  }

  public static function exist(
    string $cacheName,
    array $cacheValue = [] 
  ): bool {
    return file_exists(Cache::cache($cacheName, $cacheValue));
  }
  
  public static function save(
    string $cacheName,
    array $cacheValue = [],
    array $cacheContext = [] 
  ): array {
    file_put_contents( 
      Cache::cache($cacheName, $cacheValue), sprintf(
        "<?php\n\nreturn %s;", var_export($cacheContext, true)
      )
    );

    return $cacheContext;
  }
  
  public static function load(
    string $cacheName,
    array $cacheValue = []
  ): array {
    return require Cache::cache($cacheName, $cacheValue);
  }  
}