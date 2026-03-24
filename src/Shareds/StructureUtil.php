<?php

namespace Websyspro\Entity\Shareds;

use ReflectionFunction;
use ReflectionNamedType;
use ReflectionParameter;
use Websyspro\Commons\Collection;
use Websyspro\Entity\Enums\TokenType;
use Websyspro\Entity\Enums\EntityRoot;
use Websyspro\Commons\Util;

class StructureUtil
{
  public static function getTypeName(
    ReflectionParameter $reflectionParameter
  ): string|null {
    if( $reflectionParameter->getType() instanceof ReflectionNamedType ){
      return $reflectionParameter->getType()->getName();
    }

    return null;
  }

  public static function createToken(
    TokenType $type,
    string $value,
    int $group,
    Structure $structure       
  ): Token {
    $isNotTokenString = Util::inArray( $type, [ 
      TokenType::Entity, 
      TokenType::Compare, 
      TokenType::Range, 
      TokenType::Logical,
      TokenType::StartGroup,
      TokenType::EndGroup 
    ]);

    if( $isNotTokenString ){
      [ $tokenEntity, $entityRoot ] = StructureUtil::getEntityAndRoot( $structure, $value );
      return new Token( $type, $value, $group, $tokenEntity, $entityRoot );
    } else return new Token( TokenType::String, $value, $group );
  }

  public static function getTokenByValue(
    string $value
  ): TokenType {
    if( Util::match( "#(=|==|===|<>|!=|!==|>=|<=)#", $value )){
      return TokenType::Compare;
    } else
    if( Util::match( "#^\\\$.*->.*$#", $value )){
      return TokenType::Entity;
    } else
    if( Util::match( "#\\\$(\{[a-zA-Z_][a-zA-Z0-9_]*\}|[a-zA-Z_][a-zA-Z0-9_]*)#", $value )){
      return TokenType::Static;
    } else
    if( Util::match( "#^(\\\"|').*(\\\"|')$#", $value )){
      return TokenType::String;
    } else
    if( Util::match( "#(&&|\|\||And|Or)#", $value )){
      return TokenType::Logical;
    }else
    if( Util::match( "#^[a-zA-Z]{1}.*::.*(->(?:name|value))?$#", $value )){
      return TokenType::Enum;
    } else
    if(Util::match( "#^,$#", $value )){
      return TokenType::Ignore;
    } else
    if( Util::match( "#^\($#", $value )){
      return TokenType::StartGroup;
    } else 
    if( Util::match( "#^\)$#", $value )){
      return TokenType::EndGroup;
    } else return TokenType::String;
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
    return Util::match( "#^[a-zA-Z]{1}.*::.*(->(?:name|value))?$#", $value );
  }

  public static function isContainsStatic(
    string $value
  ): bool {    
    return Util::match( "#{\\$|\\$#", $value );
  }  

  public static function isStaticValue(
    string $value
  ): bool {
    return Util::match( "#^\\$#", $value );
  }  

  private static function getParameterByName(
    string $name,
    Structure $structure
  ): Parameter|null {
    $entity = $structure->parametersList->getOneOrFail( $name );
    if( $entity === null ){
      return null;
    }

    $parameter = $structure->parameters->getOneOrFail( $entity );
    if( $parameter === null ){
      return null;
    }

    return $parameter;
  }

  public static function getTokenEntityByParameter(
    Parameter $parameter,
    string $field
  ): TokenEntity|null {
    return new TokenEntity( $parameter->entityStructure->entity, $field );
  }  

  public static function getEntityRootByJoins(
    Structure $structure,
    TokenEntity $tokenEntity
  ): EntityRoot|null {
    $join = $structure->joins->getOneOrFail( $tokenEntity->class );
    if( $join instanceof HierarchyJoin ){
      return $join->entityRoot;
    }

    return null;
  }
  
  public static function getEntityAndRoot(
    Structure $structure,
    string $value
  ): array {
    if( str_contains( $value, "->" )){
      [ $parameter, $field ] = explode( 
        "->", trim( $value, "$" ), 2
      );

      $parameter = StructureUtil::getParameterByName(
        $parameter, $structure
      );

      if( $parameter instanceof Parameter ){
        $tokenEntity = StructureUtil::getTokenEntityByParameter( $parameter, $field );
        return [ $tokenEntity, StructureUtil::getEntityRootByJoins( $structure, $tokenEntity )];
      }
    }

    return [ null, null ];
  }

  private static function getFileBody(
    ReflectionFunction $reflectionFunction
  ): Collection {
    $rowsFromFile = new Collection(
      file( $reflectionFunction->getFileName())
    );

    return $rowsFromFile->slice(
      $reflectionFunction->getStartLine() - 1, 
      $reflectionFunction->getEndLine() - $reflectionFunction->getStartLine() + 1
    );
  }

  public static function getSourceFile(
    ReflectionFunction $reflectionFunction
  ): string {
    $rowsFromFile = StructureUtil::getFileBody( $reflectionFunction );
    $rowsFromFile = $rowsFromFile->where( fn( string $row ) => !Util::match( "#^.*//#", $row ));

    return preg_replace([
        "#/\*.*?\*/#",
        "#\r#",
        "#\n\s*#",
        "#^.*\\{.*return\s*#",
        "#\s*;\s*\\}\s*#",
        "#^.*(fn|function)\s*\(#",
        "#\s*\);\s*$#",
        "#^.*?\)\s*=>\s*#s",
        "#\\[\s*#s",
        "#\s*\\]#s",
        "#,\s*#s",
        "#\"#s",
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
        "",     // Remove arrow function
        "(",    // Converte colchetes em parênteses
        ")",    // Converte colchetes em parênteses
        ",",    // Normaliza vírgulas
        "'",    // Converte aspas duplas em simples
        "And",  // Converte && em And
        "Or",   // Converte || em Or
        "<>",   // Normaliza operador diferente
        "=",    // Normaliza operador igual
        "1",    // Converte true em 1
        "0"     // Converte false em 0
      ], $rowsFromFile->toString()
    );
  }
}