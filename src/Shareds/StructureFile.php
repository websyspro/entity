<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Interfaces\Parameter;
use Websyspro\Entity\Interfaces\UsePath;
use Websyspro\Entity\Consts\Patterns;
use ReflectionParameter;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionProperty;

class StructureFile
{
  private array $rows = [];
  private array $namespace = [];
  private array $parameters = [];
  private array $statics = [];
  private array $usePaths = [];
  private array $body = [];
  private array $joins = [];
  private array $tokens = [];

  public function __construct(
    public ReflectionFunction $reflectionFunction
  ){
    $this->structureFile();
    $this->structureClear();
  }

  private function structureFile(
  ): void {
    $this->rows = file( 
      $this->reflectionFunction->getFileName()
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
    $this->namespace = array_filter( $this->rows, 
      fn( string $row ) => preg_match( "#^.*namespace#", $row )
    );
  }

  private function structureFileParameters(
  ): void {
    $this->parameters = array_map(
      fn( ReflectionParameter $reflectionParameters ) => new Parameter( 
        StructureUtil::getParameterName( $reflectionParameters ), 
        StructureUtil::getParameterTypeName( $reflectionParameters )         
      ), $this->reflectionFunction->getParameters()
    );
  }

  private function structureFileStatics(
  ): void {
    $this->statics = $this->reflectionFunction->getStaticVariables();    
  }  

  private function structureFileUsePaths(
  ): void {
    $this->usePaths = array_filter( 
      $this->rows, fn( string $row ) => (
        preg_match( Patterns::PATTERN_NAMESPACE_WHERES, $row )
      )
    );

    $this->usePaths = array_map(
      fn( string $use ) => new UsePath( 
        preg_replace( Patterns::PATTERN_NAMESPACE_HYDRATE, "", $use )
      ), $this->usePaths
    );
  } 
  
  private function structureFileBody(
  ): void {
    $this->body = array_slice( 
      $this->rows,
      $this->reflectionFunction->getStartLine() - 1, 
      $this->reflectionFunction->getEndLine() - 
      $this->reflectionFunction->getStartLine() + 1
    );
  }

  private function structureFileHidrate(
  ): void {
    $this->body = array_filter(
      $this->body, fn( string $row ) => (
        !preg_match( Patterns::PATTERN_REMOVE_COMMENT_LINE, $row )
      )
    );

    [ $hydrateBodyFrom, $hydrateBodyTos 
    ] = Patterns::PATTERN_HYDRATE_BODY;

    preg_match_all( Patterns::PATTERN_TOKEN, preg_replace(
      $hydrateBodyFrom, $hydrateBodyTos, implode( "", $this->body ),
    ), $matchTokens );

    if( empty( $matchTokens ) === false ){
      [ $this->tokens ] = $matchTokens;
    };
  }

  private function isStructureJoins(
    string $token,
    int $index
  ): void {
    if( preg_match( Patterns::PATTERN_IS_HIERARCHY_JOINS, $token )){
      $strutureJoin = preg_replace( Patterns::PATTERN_REMOVE_END_HIERARCHY_JOINS, "", $token );
      $strutureJoinList = preg_split( Patterns::PATTERN_HIERARCHY_JOINS_SEPARETOR, $strutureJoin );

      for( $i=0; $i < sizeof( $strutureJoinList ); $i++ ){
        if( (int)$i !== 0 ){
          $paramterName = $strutureJoinList[ $i ];
          $parentEntity = $strutureJoinList[ $i - 1];

          if( $parentEntity ){
            $parameterParentEntityList = array_values( array_filter(
              $this->parameters, fn( Parameter $parameter ) => $parameter->name === preg_replace( 
                Patterns::PATTERN_REMOVE_DEFINED_VAR_KEY, "", $parentEntity
              )
            ));
            
            if( empty( $parameterParentEntityList ) === false ){
              [ $parameterParentEntity ] = $parameterParentEntityList;
            
              if( $parameterParentEntity instanceof Parameter ){     
                if ( property_exists( $parameterParentEntity->usePath->path, $paramterName )) {
                  $reflectionProperty = new ReflectionProperty(
                    $parameterParentEntity->usePath->path, $paramterName
                  );

                  if( $reflectionProperty->getType() instanceof ReflectionNamedType ){
                    if( $reflectionProperty->getType()->getName() === EntityList::class ){
                      $entity = $this->tokens[ $index + 2 ];
                      $parameter = $this->tokens[ $index + 3 ]; 

                      if( $entity && $parameter ){
                        $usePaths = array_values( array_filter( 
                          $this->usePaths, fn( UsePath $usePath ) => $usePath->entity === $entity
                        ));

                        if( empty( $usePaths ) === false ){
                          [ $usePath ] = $usePaths;
                          if( $usePath instanceof UsePath ){
                            $this->parameters[] = new Parameter( 
                              $parameter, $usePath->path
                            );
                          } 
                        }
                      }
                    } else {
                      $this->parameters[] = new Parameter( 
                        $paramterName, $reflectionProperty->getType()->getName()
                      );
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }

  private function structureFileHidrateJoins(
  ): void {
    if( empty( $this->tokens ) === false ){
      for( $i=0; $i < sizeof( $this->tokens ); $i++ ){
        $this->isStructureJoins( $this->tokens[ $i ], $i );
      }
    }
  }
  
  private function structureClear(
  ): void {
    unset( 
      $this->rows,
      $this->body
    );
  }
}