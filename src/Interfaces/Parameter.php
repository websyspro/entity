<?php

namespace Websyspro\Entity\Interfaces;

use Websyspro\Entity\Shareds\EntityStructure;
use Websyspro\Entity\Consts\Patterns;
use Websyspro\Entity\Enums\MetaType;
use Websyspro\Entity\Enums\MultiLine;

class Parameter
{
  public string $name;
  public MultiLine $multiLine;
  public EntityStructure $entityStructure;

  public function __construct(
    string $name,
    string $entity,
    MultiLine $multiLine = MultiLine::No
  ){
    $this->parameterParsed(
      $name, $entity, $multiLine
    );
  }


  private function parameterParsed(
    string $name,
    string $entity,
    MultiLine $multiLine
  ): void {
    $this->name = preg_replace( Patterns::PATTERN_REMOVE_DEFINED_VAR_KEY, "", $name );
    $this->multiLine = $multiLine;
    
    if( class_exists( $entity )){
      $this->entityStructure = call_user_func_array(
        [ $entity, "meta" ], [ MetaType::Query ]
      );      
    }
  }
}