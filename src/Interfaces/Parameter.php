<?php

namespace Websyspro\Entity\Interfaces;

use Websyspro\Entity\Shareds\EntityStructure;
use Websyspro\Entity\Consts\Patterns;
use Websyspro\Entity\Enums\MetaType;

class Parameter
{
  public string $name;
  public UsePath $usePath;
  public string $namespace;
  public string $entity;
  public string $path;
  public EntityStructure $entityStructure;

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
    $this->name = preg_replace(
      Patterns::PATTERN_REMOVE_DEFINED_VAR_KEY, "", $name
    );

    $this->usePath = new UsePath( $entity );

    if( class_exists( $this->usePath->path )){
      $this->entityStructure = call_user_func_array(
        [ $this->usePath->path, "meta" ], [ MetaType::Query ]
      );      
    }
  }
}