<?php

namespace Websyspro\Entity\Shareds;

use SplFileObject;
use function count;

class ClosureStatics
{
  public static array $usesStatics = [];

  public function __construct(        
  ){}

  public static function getSignary(
    Repository $repository
  ): string {
    return md5( $repository->reflectionFunction->getShortName());
  }  

  public static function getUses(
    Repository $repository,
    string $splFileObjectLines = ""
  ): array {
    if( isset( self::$usesStatics[ $repository->signary ])){
      return self::$usesStatics[ $repository->signary ];
    }
    
    $splFileObject = new SplFileObject(
      $repository->reflectionFunction->getFileName()
    );

    while( $splFileObject->eof() === false ){
      $splFileObjectLines .= $splFileObject->fgets();
      if( str_contains( $splFileObjectLines, "class" )){
        break;
      }
    }
    
    $tokens = token_get_all( $splFileObjectLines );
    for( $i=0; $i<count( $tokens ); $i++ ){
      if( $tokens[$i][0] === T_USE ){
        print_r( $tokens[$i] );
      }
    }

    return [];
  }
}