<?php

namespace Websyspro\Entity\Interfaces;

class IEntity
{
  public string $entity;
  public function __construct(
    public string $class
  ){
    $entity = preg_split( "#\\\#", $class );
    $this->entity = preg_replace( "#Entity$#", "", end( $entity ) );
  }
}