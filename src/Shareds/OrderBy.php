<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Interfaces\Entity;
use Websyspro\Entity\Enums\MetaType;
use ReflectionParameter;
use ReflectionFunction;

class OrderBy
{
  public array $entitys;
  public array $tokens;

  public function __construct(
    public ReflectionFunction $reflectionFunction
  ){
    $this->setParametersList();
    $this->setTokensList();
  }

  private function setParametersList(
  ): void {
    foreach( $this->reflectionFunction->getParameters() as $parameter ){
      if( $parameter instanceof ReflectionParameter ){ 
        $entityStructure =  call_user_func_array(
          [ StructureUtil::getParameterTypeName( $parameter ), "meta" ], [ MetaType::Query ]
        );

        if( $entityStructure instanceof EntityStructure ){
          $this->entitys[ $parameter->getName() ] = $entityStructure->entity;
        }
      }
    }
  }  

  private function setTokensList(
  ): void {
    $tokens = preg_replace( 
      "#^\\(|\\)$#", "", StructureUtil::structureFileHidrate(
        $this->reflectionFunction
      )
    );

    if( sizeof( $tokens ) !== 0 ){
      [ $token ] = $tokens;
      $this->setParseToken( 
        explode( ",", $token )
      );
    }
  }

  private function setParseToken(
    array $tokens
  ): void {
    foreach( $tokens as $token ){
      $hasTokenFromEntity = str_contains( $token, "->" ) && str_starts_with( $token, "$" );
      if( $hasTokenFromEntity ){
        [ $parameterName, $parameterField ] = explode( 
          "->", trim( $token, "$" ), 2
        );

        if( $this->entitys[ $parameterName ] instanceof Entity ){
          $this->tokens[] = sprintf( 
            "%s.%s %s", $this->entitys[ $parameterName ]->table, $parameterField, static::class === OrderByAsc::class ? "Asc" : "Desc"
          );
        }
      }
    }
  }
}