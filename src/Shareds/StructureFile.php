<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use ReflectionFunction;
use Websyspro\Commons\Util;

class StructureFile
{
  private Collection $namespace;
  private Collection $uses;
  private Collection $body;

  public function __construct(
    public ReflectionFunction $reflectionFunction
  ){
    $this->structureInit();
    $this->structureFile();
  }

  private function structureInit(
  ): void {
    $this->namespace = new Collection();
    $this->uses = new Collection();
  }

  private function structureFile(
  ): void {
    $rowsFromFile = new Collection(
      file( $this->reflectionFunction->getFileName())
    );

    $this->structureFileNamespace( $rowsFromFile );
    $this->structureFileUses( $rowsFromFile );
    $this->structureFileBody( $rowsFromFile );
  }

  private function structureFileNamespace(
    Collection $rowsFromFile
  ): void {
    $this->namespace = $rowsFromFile->where(
      fn( string $row ) => Util::match( "#^.*namespace#", $row )
    );
  }

  private function structureFileUses(
    Collection $rowsFromFile
  ): void {
    $this->namespace = $rowsFromFile->where(
      fn( string $row ) => Util::match( "#^.*use\s*#", $row )
    );
  } 
  
  private function structureFileBody(
    Collection $rowsFromFile
  ): void {
    $this->body = $rowsFromFile->slice(
      $this->reflectionFunction->getStartLine() - 1, 
      $this->reflectionFunction->getEndLine() - 
      $this->reflectionFunction->getStartLine() + 1
    );
  }  
}