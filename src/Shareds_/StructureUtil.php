<?php

namespace Websyspro\Entity\Shareds_;

use ReflectionFunction;
use Websyspro\Entity\Interfaces\Parameter;
use Websyspro\Entity\Interfaces\Entity;
use Websyspro\Entity\Interfaces\Join;
use Websyspro\Entity\Enums\MultiLine;
use Websyspro\Entity\Enums\Type;
use ReflectionNamedType;
use ReflectionParameter;
use Websyspro\Entity\Consts\Patterns;

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
      [ $entity, $field, $fieldAlias, $multiLine ] = StructureUtil::getEntityAndRoot( $structureFile, $value );
      $tokenNew = new Token( $value, $group, $type );
      return $tokenNew->setEntity( $entity )->setField( $field )->setFieldAlias( $fieldAlias )->setMultiLine( $multiLine );
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
        if( $join->entity->class === $entity->class ){
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
        $entity = $parameter->entityStructure->entity;
        $fieldAlias = $parameter->entityStructure->alias[ $field ] ?? null;
        return [ $entity, $field, $fieldAlias, StructureUtil::getMultiLinesByJoins( $structureFile, $entity ) ];
      }
    }

    return [ null, null, null, null ];
  }

  private static function structureFileBody(
    ReflectionFunction $reflectionFunction,
    array $body = []
  ): array {
    $rows = file( $reflectionFunction->getFileName());
    $body = array_slice( 
      $rows,
      $reflectionFunction->getStartLine() - 1, 
      $reflectionFunction->getEndLine() - 
      $reflectionFunction->getStartLine() + 1
    );

    return $body;
  }

  public static function structureFileHidrate(
    ReflectionFunction $reflectionFunction,
    array $tokens = []
  ): array {
    $body = array_filter(
      StructureUtil::structureFileBody( $reflectionFunction ), fn( string $row ) => (
        !preg_match( Patterns::PATTERN_REMOVE_COMMENT_LINE, $row )
      )
    );

    [ $hydrateBodyFrom, $hydrateBodyTos 
    ] = Patterns::PATTERN_HYDRATE_BODY;

    preg_match_all( Patterns::PATTERN_TOKEN, preg_replace(
      $hydrateBodyFrom, $hydrateBodyTos, implode( "", $body ),
    ), $matchTokens );

    if( empty( $matchTokens ) === false ){
      [ $tokens ] = $matchTokens;
      return $tokens;
    };

    return [];
  }  
}