<?php

namespace Websyspro\Entity\Shareds;

use function sprintf;

class Cache
{
  private static array $cacheContents = [];
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
    if(CACHE_DISABLED === true){
      return false;
    }

    return file_exists(
      self::file( $file )
    );
  }
  
  public static function save(
    string $file,
    array $contexts
  ): array {
    file_put_contents( 
      self::path( "{$file}.php"), sprintf(
        "<?php%s%sreturn %s;", PHP_EOL, PHP_EOL, var_export($contexts, true)
      ), LOCK_EX
    );

    self::$cacheContents[$file] = require self::file($file);
    return self::$cacheContents[$file];
  }

  public static function delete(
    string $file
  ): void {
    unset(self::$cacheContents[$file]);
    if(file_exists(self::file($file))){
        unlink(self::file($file));
    }
  }  
  
  public static function load(
    string $file
  ): array {
    if(isset(self::$cacheContents[$file])){
      return self::$cacheContents[$file];
    }

    self::$cacheContents[$file] = require self::file($file);
    return self::$cacheContents[$file];
  }  
}