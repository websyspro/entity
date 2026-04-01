<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Interfaces\Parameter;
use Websyspro\Entity\Interfaces\Join;
use Websyspro\Entity\Enums\MultiLine;
use Websyspro\Entity\Enums\Type;
use ReflectionNamedType;
use ReflectionParameter;

class StructureUtil
{
  public static function getParameterName(
    ReflectionParameter $reflectionParameter
  ): string|null {
    return $reflectionParameter->getName();
  }


  public static function getParameterTypeName(
    ReflectionParameter $reflectionParameter
  ): string|null {
    if( $reflectionParameter->getType() instanceof ReflectionNamedType ){
      return $reflectionParameter->getType()->getName();
    }

    return null;
  }

  public static function createToken(
    string $value,
    int $group,
    Type $type,
    StructureFile $structureFile       
  ): Token {
    $isNotTokenString = in_array( $type, [ 
      Type::Entity, 
      Type::Compare, 
      Type::Range, 
      Type::Logical,
      Type::StartGroup,
      Type::EndGroup 
    ]);

    if( $isNotTokenString ){
      [ $entity, $field, $multiLine ] = StructureUtil::getEntityAndRoot( $structureFile, $value );
      $tokenNew = new Token( $value, $group, $type );
      return $tokenNew->setEntity( $entity )->setField( $field )->setMultiLine( $multiLine );
    } else return new Token( $value, $group, Type::String );
  }

  public static function getTokenByValue(
    string $value
  ): Type {
    if( preg_match( "#(=|==|===|<>|!=|!==|>=|<=)#", $value )){
      return Type::Compare;
    } else
    if( preg_match( "#^\\\$.*->.*$#", $value )){
      return Type::Entity;
    } else
    if( preg_match( "#\\\$(\{[a-zA-Z_][a-zA-Z0-9_]*\}|[a-zA-Z_][a-zA-Z0-9_]*)#", $value )){
      return Type::Static;
    } else
    if( preg_match( "#^(\\\"|').*(\\\"|')$#", $value )){
      return Type::String;
    } else
    if( preg_match( "#(&&|\|\||And|Or)#", $value )){
      return Type::Logical;
    }else
    if( preg_match( "#^[a-zA-Z]{1}.*::.*(->(?:name|value))?$#", $value )){
      return Type::Enum;
    } else
    if(preg_match( "#^,$#", $value )){
      return Type::Ignore;
    } else
    if( preg_match( "#^\($#", $value )){
      return Type::StartGroup;
    } else 
    if( preg_match( "#^\)$#", $value )){
      return Type::EndGroup;
    } else return Type::String;
  }

  public static function getBreakTokens(
    string $source
  ): array {
    preg_match_all(
      "#'[^']*'|\"[^\"]*\"|\\S+#",
      preg_replace( "#^\(|\)$#", "", $source ), $tokensFromPattern
    );

    return $tokensFromPattern;
  }

  public static function isEnumValue(
    string $value
  ): bool {
    return preg_match( "#^[a-zA-Z]{1}.*::.*(->(?:name|value))?$#", $value );
  }

  public static function isContainsStatic(
    string $value
  ): bool {    
    return preg_match( "#{\\$|\\$#", $value );
  }  

  public static function isStaticValue(
    string $value
  ): bool {
    return preg_match( "#^\\$#", $value );
  }  

  public static function getTokenEntityByParameter(
    Parameter $parameter
  ): Entity {
    return new Entity( $parameter->entityStructure->entity->class );
  }  

  public static function getMultiLinesByJoins(
    StructureFile $structureFile,
    Entity $entity
  ): MultiLine|null {
    if( empty( $structureFile->joins )){
      return null;
    }

    foreach( $structureFile->joins as $join ){
      if( $join instanceof Join ){
        if( $join->entity === $entity->class ){
          return $join->multiLine;
        }
      }
    } 

    return null;
  }
  
  public static function getEntityAndRoot(
    StructureFile $structureFile,
    string $value
  ): array {
    if( str_contains( $value, "->" )){
      [ $parameter, $field ] = explode( 
        "->", trim( $value, "$" ), 2
      );

      $parameter = $structureFile
        ->parameters[ $parameter ] ?? null;
      
      if( $parameter instanceof Parameter ){
        $entity = StructureUtil::getTokenEntityByParameter( $parameter );
        return [ $entity, $field, StructureUtil::getMultiLinesByJoins( $structureFile, $entity ) ];
      }
    }

    return [ null, null, null ];
  }

  // private static function getFileBody(
  //   ReflectionFunction $reflectionFunction
  // ): Collection {
  //   $rowsFromFile = new Collection(
  //     file( $reflectionFunction->getFileName())
  //   );

  //   return $rowsFromFile->slice(
  //     $reflectionFunction->getStartLine() - 1, 
  //     $reflectionFunction->getEndLine() - $reflectionFunction->getStartLine() + 1
  //   );
  // }

  // public static function getSourceFile(
  //   ReflectionFunction $reflectionFunction
  // ): string {
  //   $rowsFromFile = StructureUtil::getFileBody( $reflectionFunction );
  //   $rowsFromFile = $rowsFromFile->where( fn( string $row ) => !Util::match( "#^.*//#", $row ));

  //   return preg_replace([
  //       "#/\*.*?\*/#",
  //       "#\r#",
  //       "#\n\s*#",
  //       "#^.*\\{.*return\s*#",
  //       "#\s*;\s*\\}\s*#",
  //       "#^[^(]*(fn|function)\s*\(#",
  //       "#\s*\);\s*$#",
  //       "#^.*?\)\s*=>\s*#s",
  //       "#\\[\s*#s",
  //       "#\s*\\]#s",
  //       "#,\s*#s",
  //       "#\"#s",
  //       "#&&#",
  //       "#\|\|#",
  //       "#(!==|!=)#",
  //       "#(===|==|=)#",
  //       "#true#",
  //       "#false#"
  //     ], [
  //       "",     // Remove /* ... */
  //       "",     // Remove carriage return
  //       " ",    // Remove quebras de linha
  //       "",     // Remove abertura de função
  //       "",     // Remove fechamento de função
  //       "fn(",  // Normaliza declaração de função
  //       "",     // Remove fechamento de parênteses
  //       "",     // Remove arrow function
  //       "(",    // Converte colchetes em parênteses
  //       ")",    // Converte colchetes em parênteses
  //       ",",    // Normaliza vírgulas
  //       "'",    // Converte aspas duplas em simples
  //       "And",  // Converte && em And
  //       "Or",   // Converte || em Or
  //       "<>",   // Normaliza operador diferente
  //       "=",    // Normaliza operador igual
  //       "1",    // Converte true em 1
  //       "0"     // Converte false em 0
  //     ], $rowsFromFile->toString()
  //   );
  // }
}