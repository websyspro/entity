<?php

namespace Websyspro\Entity\Types;

class Varchar
extends ColumnAbstract
{
    private string $data = [];

    public function __set(
      string $name, 
      string $value
    ): void {
      $this->data[ $name ] = (string)$value;
    }

    public function __get(
      string $name
    ): string|null {
      if( empty( $this->data )){
        return null;
      }

      return $this->data[ $name ] ?? null;
    }
}