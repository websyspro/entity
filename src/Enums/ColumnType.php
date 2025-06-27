<?php

namespace Websyspro\Entity\Enums;

enum ColumnType: string
{
  case Number = "number";
  case Text = "text";
  case Decimal = "decimal";
  case Time = "time";
  case Date = "date";
  case Datetime = "datetime";
  case Flag = "flag";

  private function stringFilterQuotes(
    string $string
  ): string {
    if(preg_match("/(^')|('$)/", $string) === 1){
      return preg_replace("/(^')|('$)/", "", $string);
    }

    return $string;
  }

  private function datetimeEncode(
    string $datetime
  ): string {
    if($datetime === "NULL"){
      return $datetime;
    }

    if(preg_match("/(\d{2})\/(\d{2})\/(\d{4})/", $this->stringFilterQuotes($datetime))){
      $datetime = preg_replace("/(\d{2})\/(\d{2})\/(\d{4})/", "$3-$2-$1", $this->stringFilterQuotes($datetime));
    }

    if(preg_match("/(\d{2})\/(\d{2})\/(\d{4}) (\d{2}:\d{2}:\d{2})/", $this->stringFilterQuotes($datetime))){
      $datetime = preg_replace("/(\d{2})\/(\d{2})\/(\d{4}) (\d{2}:\d{2}:\d{2})/", "$3-$2-$1 $4", $this->stringFilterQuotes($datetime));
    }

    return sprintf( "'%s'", $datetime );   
  }

  private function datetimeDecode(
    string $datetime
  ): string {
    return date("d/m/Y H:i:s", strtotime( $datetime ));
  }

  public function dateEncode(
    string $date
  ): string {
    if($date === "NULL"){
      return $date;
    }

    if(preg_match("/(\d{2})\/(\d{2})\/(\d{4})/", $this->stringFilterQuotes($date))){
      $date = preg_replace("/(\d{2})\/(\d{2})\/(\d{4})/", "$3-$2-$1", $this->stringFilterQuotes($date));
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
      [ "/\./", "/,/" ], [ "", "." ], $this->stringFilterQuotes($decimal)
    );
  }

  
  public static function decimalDecode(
    string | null $decimal,
  ): float {
    return (float)$decimal;
  } 

  public function textEncode(
    string $string
  ): string {
    return sprintf(
      "'%s'", addslashes(
        $this->stringFilterQuotes($string)
      )
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
    if(is_null($mixed)){
      return "NULL";
    }

    return match( $this ){
      ColumnType::Date => $this->dateEncode($mixed),
      ColumnType::Datetime => $this->datetimeEncode($mixed),
      ColumnType::Decimal => $this->decimalEncode($mixed),
      ColumnType::Text => $this->textEncode($mixed),
      ColumnType::Flag => $this->flagEncode($mixed),
        default => $mixed
    };
  }

  public function Decode(
    mixed $mixed
  ): mixed {
    if(is_null($mixed)){
      return null;
    }

    return match( $this ){
      ColumnType::Date => $this->dateDecode($mixed),
      ColumnType::Datetime => $this->datetimeDecode($mixed),
      ColumnType::Decimal => $this->decimalDecode($mixed),
      ColumnType::Flag => $this->flagDecode($mixed),
        default => $mixed
    };
  }
}