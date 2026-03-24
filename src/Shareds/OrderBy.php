<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use ReflectionParameter;
use ReflectionFunction;

class OrderBy
{
  public Collection $entitys;
  public Collection $tokens;

  public function __construct(
    public ReflectionFunction $reflectionFunction
  ){
    $this->setCreateList();
    $this->setParametersList();
    $this->setTokensList();
  }

  private function setCreateList(
  ): void {
    $this->tokens = new Collection();
    $this->entitys = new Collection();
  }  

  private function setParametersList(
  ): void {
    foreach( $this->reflectionFunction->getParameters() as $parameter ){
      if( $parameter instanceof ReflectionParameter ){       
        $this->entitys->add( 
          new Entity( StructureUtil::getTypeName( $parameter )
          ), $parameter->getName()
        );
      }
    }
  }  

  private function setTokensList(
  ): void {
    $tokens = preg_replace(
      "#^\\(|\\)$#", "", StructureUtil::getSourceFile(
        $this->reflectionFunction
      )
    );

    $this->tokens = $this->setParseToken( 
      new Collection( explode( ",", $tokens ))
    );
  }

  private function setParseToken(
    Collection $tokens
  ): Collection {
    return $tokens->mapper(
      function( string $token ){
        $hasTokenFromEntity = str_contains( $token, "->" ) 
                           && str_starts_with( $token, "$" );

        if( $hasTokenFromEntity ){
          [ $entity, $field ] = explode( 
            "->", trim( $token, "$" ), 2
          );

          $entity = $this->entitys->getOneOrFail( $entity );
          if( $entity instanceof Entity ){
            return Util::sprintFormat( "%s.%s %s", [
              $entity->table, $field, static::class === OrderByAsc::class ? "Asc" : "Desc"
            ]);
          }
        }

        return $token;
      }
    );
  }
}