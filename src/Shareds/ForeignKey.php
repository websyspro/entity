<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Util;

class ForeignKey
{
  public string $key;
  public string $fullname;
  public Reference $reference;

  public function __construct(
    public Column $column,
    public Entity $entity
  ){
    $this->defineFullName();
    $this->defineClears();
  }

  private function defineFullName(
  ): void {
    $reference = call_user_func_array(
      [ $this->column->instance->referenceClass, "getAttributes" ], []
    );

    if( $reference instanceof EntityStructure ){
      if( $reference->primaryKey->exist() ){
        $this->reference = new Reference(
          $reference
        );

        $this->key = $this->column->name;
        $this->fullname = Util::sprintFormat(
          "FOREIGNKEY_%s_%s_In_%s_%s", [
            $this->entity->table, $this->column->name,
            $this->reference->table, $this->reference->key
          ]
        );
      }
    }
  }

  private function defineClears(
  ): void {
    unset( 
      $this->column
    );
  }   
}