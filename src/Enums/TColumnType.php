<?php

namespace Websyspro\Entity\Enums;

enum TColumnType: string
{
  case Number = "number";
  case Text = "text";
  case Decimal = "decimal";
  case Time = "time";
  case Date = "date";
  case Datetime = "datetime";
  case Flag = "flag";

  private function datetimeEncode(
    string $datetime
  ): string {
    if( preg_match( "/(\d{2})\/(\d{2})\/(\d{4})/", $datetime )){
      $datetime = preg_replace( "/(\d{2})\/(\d{2})\/(\d{4})/", "$3-$2-$1", $datetime );
    }

    if( preg_match( "/(\d{2})\/(\d{2})\/(\d{4}) (\d{2}:\d{2}:\d{2})/", $datetime )){
      $datetime = preg_replace( "/(\d{2})\/(\d{2})\/(\d{4}) (\d{2}:\d{2}:\d{2})/", "$3-$2-$1 $4", $datetime );
    }

    return sprintf( "'%s'", $datetime );   
  }

  private function datetimeDecode(
    string $datetime
  ): string {
    return date("d/m/Y H:i:s", strtotime( $datetime ));
  }

  public static function dateEncode(
    string $date
  ): string {
    if( preg_match( "/(\d{2})\/(\d{2})\/(\d{4})/", $date )){
      $date = preg_replace( "/(\d{2})\/(\d{2})\/(\d{4})/", "$3-$2-$1", $date );
    }

    return sprintf( "'%s'", $date );
  }

  private function dateDecode(
    string $date
  ): string {
    return date( "d/m/Y", strtotime( $date ));
  }  
  
  public function decimalEncode(
    string | null $decimal
  ): string | null {
    return preg_replace(
      [ "/\./", "/,/" ], [ "", "." ], $decimal
    );
  }

  
  public static function decimalDecode(
    string | null $decimal,
  ): float {
    return (float)$decimal;
  } 

  public function textEncode(
    string $text
  ): string {
    return sprintf(
      "'%s'", addslashes($text)
    );
  }
  
  public function flagEncode(
    bool | int | string $flag
  ): int {
    if( is_bool( $flag )){
      return $flag ? 1 : 0;
    } else
    if( is_string( $flag )){
      return filter_var( $flag, FILTER_VALIDATE_BOOLEAN );
    }

    return $flag;
  }

  public static function flagDecode(
    string | null $value,
  ): bool {
    return (int)$value === 1 
      ? true : false;
  }

  public function Encode(
    mixed $mixed
  ): mixed {
    return match( $this ){
      TColumnType::Date => $this->dateEncode($mixed),
      TColumnType::Datetime => $this->datetimeEncode($mixed),
      TColumnType::Decimal => $this->decimalEncode($mixed),
      TColumnType::Text => $this->textEncode($mixed),
      TColumnType::Flag => $this->flagEncode($mixed),
        default => $mixed
    };
  }

  public function Decode(
    mixed $mixed
  ): mixed {
    return match( $this ){
      TColumnType::Date => $this->dateDecode($mixed),
      TColumnType::Datetime => $this->datetimeDecode($mixed),
      TColumnType::Decimal => $this->decimalDecode($mixed),
      TColumnType::Flag => $this->flagDecode($mixed),
        default => $mixed
    };
  }
}