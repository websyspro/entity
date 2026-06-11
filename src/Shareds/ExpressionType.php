<?php

namespace Websyspro\Entity\Shareds;

use function is_string, is_bool;

class ExpressionType
{
  public function encode(
    mixed $value,
    string $type
  ): string {
    if( $value === null ){
      if( strtoupper(( string )$value ) === 'NULL' ){
        return "NULL";
      }
    }

    if( $type === 'Date' ){
      return $this->encodeDate( $value );
    } else if( $type === 'Datetime' ){
      return $this->encodeDatetime( $value );
    } else if( $type === 'Decimal' ){
      return $this->encodeDecimal( $value );
    } else if( $type === 'Text' ){
      return $this->encodeText( $value );
    } else if( $type === 'Enum' ){
      return $this->encodeText( $value );
    } else if( $type === 'Longtext' ){
      return $this->encodeText( $value );
    } else if( $type === 'Number' ){
      return $this->encodeNumber( $value );
    } else if( $type === 'Flag' ){
      return $this->encodeFlag( $value );
    }

    return $value;
  }
 
  private function encodeDate(
    mixed $value
  ): string {
    if($value === 'NULL'){
      return $value;
    }

    if( preg_match( "#(\d{2})\/(\d{2})\/(\d{4})#", $value )){
      $value = preg_replace( "#(\d{2})\/(\d{2})\/(\d{4})#", "$3-$2-$1", $value );
    }

    return $value;
  }

  private function encodeDatetime(
    string $value
  ): string {
    if($value === 'NULL'){
      return $value;
    }

    if( preg_match( "#(\d{2})\/(\d{2})\/(\d{4})#", $value )){
      $value = preg_replace( "#(\d{2})\/(\d{2})\/(\d{4})#", "$3-$2-$1", $value );
    }

    if( preg_match( "#(\d{2})\/(\d{2})\/(\d{4}) (\d{2}:\d{2}:\d{2})#", $value )){
      $value = preg_replace( "#(\d{2})\/(\d{2})\/(\d{4}) (\d{2}:\d{2}:\d{2})#", "$3-$2-$1 $4", $value );
    }

    return $value;   
  }
  
  public function encodeDecimal(
    string | null $value
  ): string | null {
    if( preg_match( "#,#", $value ) === 1 ){
      return preg_replace([ "#\.#", "#,#" ], [ "", "." ], $value );
    } else return $value;
  }
  
  public function encodeText(
    string $value
  ): string|array {
    return $value;
  }
  
  public function encodeNumber(
    string $value
  ): string|array {
    return $value;
  }
  
  public function encodeFlag(
    bool|int|string $value
  ): int {
    if( is_bool( $value )){
      return $value ? 1 : 0;
    } else
    if( is_string( $value )){
      return filter_var(
        $value, FILTER_VALIDATE_BOOLEAN
      );
    }

    return $value;
  }  
}