<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Interfaces\Parameter;
use Websyspro\Entity\Interfaces\UsePath;
use Websyspro\Entity\Consts\Patterns;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use ReflectionParameter;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionProperty;

class StructureFile
{
  private Collection $rows;
  private Collection $namespace;
  private Collection $usePaths;
  private Collection $statics;
  private Collection $parameters;
  private Collection $joins;
  private Collection $body;
  private Collection $tokens;

  public function __construct(
    public ReflectionFunction $reflectionFunction
  ){
    $this->structureFile();
    $this->structureClear();
  }

  private function structureFile(
  ): void {
    $this->rows = new Collection(
      file( $this->reflectionFunction->getFileName())
    );

    $this->structureFileNamespace();
    $this->structureFileParameters();    
    $this->structureFileStatics();
    $this->structureFileUsePaths();
    $this->structureFileBody();
    $this->structureFileHidrate();
    $this->structureFileHidrateJoins();
  }

  private function structureFileNamespace(
  ): void {
    $this->namespace = $this->rows->where(
      fn( string $row ) => Util::match( "#^.*namespace#", $row )
    );
  }

  private function structureFileParameters(
  ): void {
    $this->parameters = new Collection(
      Util::mapper( $this->reflectionFunction->getParameters(), 
        fn( ReflectionParameter $reflectionParameters ) => new Parameter( 
          StructureUtil::getParameterName( $reflectionParameters ), 
          StructureUtil::getParameterTypeName( $reflectionParameters )         
        )
      )
    );
  }

  private function structureFileStatics(
  ): void {
    $this->statics = new Collection(
      $this->reflectionFunction->getStaticVariables()
    );    
  }  

  private function structureFileUsePaths(
  ): void {
    $this->usePaths = $this->rows
      ->where( fn( string $row ) => Util::match( Patterns::PATTERN_NAMESPACE_WHERES, $row ))
      ->mapper( fn( string $use ) => new UsePath( Util::replace( Patterns::PATTERN_NAMESPACE_HYDRATE, $use ) ));
  } 
  
  private function structureFileBody(
  ): void {
    $this->body = $this->rows->slice(
      $this->reflectionFunction->getStartLine() - 1, 
      $this->reflectionFunction->getEndLine() - 
      $this->reflectionFunction->getStartLine() + 1
    );
  }

  private function structureFileHidrate(
  ): void {
    $this->body = $this->body->where( fn( string $row ) => 
      Util::match( Patterns::PATTERN_REMOVE_COMMENT_LINE, $row ) === false
    );

    [ $hydrateBodyFrom, $hydrateBodyTos 
    ] = Patterns::PATTERN_HYDRATE_BODY;

    $this->tokens = new Collection( 
      Util::matchAll( Patterns::PATTERN_TOKEN, preg_replace(
        $hydrateBodyFrom, $hydrateBodyTos, $this->body->toString()
      ))
    );
  }

  private function isStructureJoins(
    string $token,
    int $index
  ): void {
    if( Util::match( Patterns::PATTERN_IS_HIERARCHY_JOINS, $token )){
      $strutureJoin = Util::replace( Patterns::PATTERN_REMOVE_END_HIERARCHY_JOINS, $token );
      $strutureJoinList = Util::split( Patterns::PATTERN_HIERARCHY_JOINS_SEPARETOR, $strutureJoin );

      $strutureJoinList->mapper(
        function( string $paramterName, int $i ) use ( $index, $strutureJoinList ){
          if( (int)$i !== 0 ){
            $parentEntity = $strutureJoinList->getOneOrFail( $i - 1 );
            if( $parentEntity ){
              $parameterParentEntityList = $this->parameters->where(
                fn( Parameter $parameter ) => $parameter->name === Util::replace( 
                  Patterns::PATTERN_REMOVE_DEFINED_VAR_KEY, lcfirst( $parentEntity )
                )
              );
              
              if( $parameterParentEntityList->exist() ){
                [ $parameterParentEntity ] = $parameterParentEntityList->toArray();
              
                if( $parameterParentEntity instanceof Parameter ){     
                  if ( property_exists( $parameterParentEntity->usePath->path, $paramterName )) {
                    $reflectionProperty = new ReflectionProperty(
                      $parameterParentEntity->usePath->path, $paramterName
                    );

                    if( $reflectionProperty->getType() instanceof ReflectionNamedType ){
                      if( $reflectionProperty->getType()->getName() === EntityList::class ){
                        $entity = $this->tokens->getOneOrFail( $index + 2 );
                        $parameter = $this->tokens->getOneOrFail( $index + 3 ); 

                        if( $entity && $parameter ){
                          $usePaths = $this->usePaths->where( fn( UsePath $usePath )  => $usePath->entity === $entity );
                          if( $usePaths instanceof Collection && $usePaths->exist() ){
                            [ $usePath ] = $usePaths->toArray();
                            if( $usePath instanceof UsePath ){
                              // var_dump( $parentEntity . " -> " . $paramterName . "[M]" );
                              $this->parameters->add( 
                                new Parameter( 
                                  $parameter,
                                  $usePath->path
                                )
                              );
                            } 
                          }
                        }
                      } else {
                        // var_dump( $parentEntity . " -> " . $paramterName . "[S]" );
                        $this->parameters->add( 
                          new Parameter( 
                            $paramterName,
                            $reflectionProperty->getType()->getName()
                          )
                        );
                      }
                    }
                  }
                }
              }
            }
          }
        }
      );
    }
  }

  private function structureFileHidrateJoins(
  ): void {
    $this->tokens->mapper( 
      fn( string $token, int $index ) => (
        $this->isStructureJoins( $token, $index )
      )
    );
  }
  
  private function structureClear(
  ): void {
    unset( 
      $this->rows,
      $this->body
    );
  }
}