<?php

namespace Websyspro\Entity\Interfaces;

use Websyspro\Commons\Util;
use Websyspro\Entity\Consts\Patterns;
use Websyspro\Entity\Shareds\EntityStructure;

class Parameter
{
  public string $name;
  public UsePath $usePath;
  public string $namespace;
  public string $entity;
  public string $path;
  public EntityStructure $structure;

  public function __construct(
    string $name,
    string $entity
  ){
    $this->parameterParsed(
      $name, $entity
    );
  }


  private function parameterParsed(
    string $name,
    string $entity
  ): void {
    $this->name = Util::replace( Patterns::PATTERN_REMOVE_DEFINED_VAR_KEY, lcfirst( $name ));
    $this->usePath = new UsePath( $entity );
    $this->structure = Util::callUserClassFN( 
      $this->usePath->path, "getAttributes", []
    );
  }
}