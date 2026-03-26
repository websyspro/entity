<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use ReflectionFunction;
use ReflectionParameter;
use Websyspro\Commons\Util;
use Websyspro\Entity\Consts\Patterns;
use Websyspro\Entity\Interfaces\Parameter;

class StructureFile
{
  private Collection $rows;
  private Collection $namespace;
  private Collection $uses;
  private Collection $statics;
  private Collection $parameters;
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
    $this->structureFileSatics();
    $this->structureFileUses();
    $this->structureFileBody();
    $this->structureHidrate();
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

  private function structureFileSatics(
  ): void {
    $this->statics = new Collection(
      $this->reflectionFunction->getStaticVariables()
    );    
  }  

  private function structureFileUses(
  ): void {
    $this->uses = $this->rows->where(
      fn( string $row ) => Util::match( "#^.*use\s*#", $row )
    );
  } 
  
  private function structureFileBody(
  ): void {
    $this->body = $this->rows->slice(
      $this->reflectionFunction->getStartLine() - 1, 
      $this->reflectionFunction->getEndLine() - 
      $this->reflectionFunction->getStartLine() + 1
    );
  }

  private function structureHidrate(
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
  
  private function structureClear(
  ): void {
    unset( 
      $this->rows,
      $this->body
    );
  }
}