<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use ReflectionFunction;

class UtilsRepository
{
  public function entityWithParam(
    string $parameter
  ): array {
    return explode( 
      " ", Util::replace([ 
        "#^(\s*)?fn\\(\s*#", "#\s*\\)\s*$#" ], $parameter
      )
    );
  }

  public function readRowsFromScript(
    ReflectionFunction $fn
  ): Collection {
    return new Collection(
      file( $fn->getFileName())
    );
  }

  public function readScripByFunc(
    Collection $rows,
    ReflectionFunction $reflectionFunction
  ): string {
    return $rows->slice(
      $reflectionFunction->getStartLine() - 1, 
      $reflectionFunction->getEndLine() - $reflectionFunction->getStartLine() + 1
    )->where(fn( string $row ) => !Util::match( "#^.*//#", $row ))->joinWithSpace();
  }  

  public function dropBreaksLines(
    string $scriptFull
  ): string {
    return Util::replace(
      [ "#\r#", "#\n\s*#" ], trim(
        $scriptFull
      )
    );
  }

  public function normalizedParenteses(
    string $scriptFull
  ): string {
    return preg_replace(
      [ "#\\(#", "#\\)#" ], [ "( ", " )" ], trim(
        $scriptFull,
      )
    );
  } 

  public function dropParenthesesExtra(
    string $scriptFull,
    string $scriptFullNew = "",
    int $parenthesesCount = 0
  ): string {
    for( $i = 0; $i < Util::sizeText( $scriptFull ); $i++){
      if( $scriptFull[ $i ] === ")" ){
        $parenthesesCount--;
        if( $parenthesesCount === 0 ){
          break;
        }
      }

      if( $parenthesesCount >= 1 ){
        $scriptFullNew .= $scriptFull[ $i ];
      }

      if( $scriptFull[ $i ] === "(" ){
        $parenthesesCount++;
      }
    }

    return trim( $scriptFullNew );
  } 

  public function normalizedScript(
    string $scriptFull,
    string $type
  ): string {
    return Util::match( "#^(->)?{$type}#", $scriptFull ) === false
      ? $this->dropParenthesesExtra( 
          Util::match("#^\\(#", $scriptFull ) === false 
            ? "({$scriptFull}" : "{$scriptFull}"
        )
      : $this->dropParenthesesExtra(
          Util::replace( "#^(->)?{$type}#", $scriptFull )
        );
  } 
  
  public function normalizedScriptInclude(
    AbstractRepository $abstractRepository,
    ReflectionFunction $reflectionFunction,
    string $scriptFull,
    string $type = "include"
  ): IncludeList {
    $scriptFull = $this->dropBreaksLines( $scriptFull );
    $scriptFull = $this->normalizedScript( $scriptFull, $type );
    $scriptFull = new IncludeList( explode( "->{$type}(", $scriptFull ));
    $scriptFull = $scriptFull->mapper(
      fn( string $script ) => new IncludeItem(
        $abstractRepository, $reflectionFunction, Util::replace( 
          [ "#^\\(#", "#\\)*$#", "#(^\s*)|(\s*$)#" ], $script 
        )
      )
    );

    return $scriptFull;
  }

  public function normalizedScriptWhere(
    AbstractRepository $abstractRepository,
    ReflectionFunction $reflectionFunction,
    string $scriptFull,
    string $type = "where"
  ): WhereList {
    $scriptFull = $this->normalizedParenteses( $scriptFull );
    $scriptFull = $this->normalizedScript( $scriptFull, $type );
    return new WhereList( $abstractRepository, $reflectionFunction, $scriptFull );
  }

  public function whereBodyParse(
    string $scope,
    Collection $tokens,
  ): Collection {
    return $tokens->mapper(
      function( string $token ) use( $scope ) {
        $token = new Token($token);
        return $token->defineScope( $scope );
      }
    );
  }

  public function normalizedScrpitWhereBodyList(
    string $whereBody,
    string $scope 
  ): Collection {
    return $this->whereBodyParse( 
      $scope, new Collection( 
        Util::matchAll(
          "#'[^']*'|\"[^\"]*\"|\\S+#", preg_replace(
          [ 
            "#/\*.*?\*/#",
            "#\r#",
            "#\n\s*#",
            "#^.*\\{.*return\s*#",
            "#\s*;\s*\\}\s*#",
            "#^[^(]*(fn|function)\s*\(#",
            "#\s*\);\s*$#",
            // "#^.*?\)\s*=>\s*#s",
            "#\\[\s*#s",
            "#\s*\\]#s",
            "#,\s*#s",
            "#\"#s",
            "#\s{2,}#",
            "#;\s*$#",
            "#&&#",
            "#\|\|#",
            "#(!==|!=)#",
            "#(===|==|=)#",
            "#true#",
            "#false#"
          ], [ 
            "",     // Remove /* ... */
            "",     // Remove carriage return
            " ",    // Remove quebras de linha
            "",     // Remove abertura de função
            "",     // Remove fechamento de função
            "fn(",  // Normaliza declaração de função
            "",     // Remove fechamento de parênteses
            // "",     // Remove arrow function
            "(",    // Converte colchetes em parênteses
            ")",    // Converte colchetes em parênteses
            ",",    // Normaliza vírgulas
            "'",    // Converte aspas duplas em simples
            " ",    // Remove espaços em brancos
            "",     // Remover ;
            "And",  // Converte && em And
            "Or",   // Converte || em Or
            "<>",   // Normaliza operador diferente
            "=",    // Normaliza operador igual
            "1",    // Converte true em 1
            "0"     // Converte false em 0
          ], trim( $whereBody ))
        )
      )
    );
  }  

  public function normalizedScrpitWhereBody(
    string $whereBody,
    string $guid
  ): Collection {
    return $this->normalizedScrpitWhereBodyList( $whereBody, $guid );
  }

  public function staticsFromFunction(
    ReflectionFunction $reflectionFunction
  ): Collection {
    return new Collection(
      $reflectionFunction->getStaticVariables()
    );
  }
}