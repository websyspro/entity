<?php

namespace Websyspro\Entity\Shareds;

use ReflectionFunction;
use SplFileObject;

define( "K_STATEMENTS", "statements" );
define( "K_VARIABLE", "variable" );

class ContextFile
extends Utils
{
  private static array $contextUseStatements = [];

  public static function extractSignary(
    ReflectionFunction $reflectionFunction
  ): string {
    return md5($reflectionFunction->getShortName());
  }

  public function extractUseStatements(
    ReflectionFunction $reflectionFunction
  ): array {
    $handleId = spl_object_id(
      $reflectionFunction->getClosure()
    );

    if( isset( self::$contextUseStatements[ $handleId ])){
      return self::$contextUseStatements[ $handleId ];
    }

    $handle = new SplFileObject(
      $reflectionFunction->getFileName()
    );

    while( $handle->eof() === false ){
      $handleFgets = $handle->fgets();
      self::$contextUseStatements[ $handleId ][] = trim( $handleFgets, "\r\n;" );
      if( str_contains( $handleFgets, "class" )){
        break;
      }
    }

    self::$contextUseStatements[ $handleId ] = $this->where( 
      self::$contextUseStatements[ $handleId ], fn( string $useStatements ) => (
        str_starts_with($useStatements, "use ")
      )
    );

    self::$contextUseStatements[ $handleId ] = $this->mapper(
      self::$contextUseStatements[ $handleId ], function( string $useStatements ){
        $useStatements = str_replace( "use ", "", $useStatements );
        if( str_contains( $useStatements, "as" )){
          [ $statements, $alias ] = explode( "as", $useStatements );
          return [ K_STATEMENTS => $statements, K_VARIABLE => $alias ];
        } else {
          $statementsPaths = explode( "\\", $useStatements );
          [ $statements, $alias ] = [ $useStatements, ...$this->slice( $statementsPaths, -1, 1 )];
          return [ K_STATEMENTS => $statements, K_VARIABLE => $alias ];
        }
      }
    );

    return self::$contextUseStatements[ $handleId ];
  }
}