<?php

namespace Websyspro\Entity\Enums;

/**
 * Enumeration defining database column data types and their encoding/decoding operations.
 * Handles conversion between PHP types and database types for all supported column types.
 * Provides bidirectional transformation (Encode for DB, Decode for PHP) with locale support.
 */
enum ColumnType: string
{
  case number = "number";
  case text = "text";
  case longtext = "longtext";
  case enum = "enum";
  case decimal = "decimal";
  case time = "time";
  case date = "date";
  case datetime = "datetime";
  case flag = "flag";
  case mapper = "mapper";

  /**
   * Removes surrounding single quotes from string values.
   * 
   * @param string $string Input string potentially wrapped in quotes
   * @return string String without surrounding quotes
   */
  private function stringFilterQuotes(
    string $string
  ): string {
    /* Check if string has quotes at start or end */
    if(preg_match("#(^')|('$)#", $string) === 1){
      /* Remove quotes from both ends */
      return preg_replace("#(^')|('$)#", "", $string);
    }

    return $string;
  }

  /**
   * Converts Brazilian datetime format (dd/mm/yyyy) to database format (yyyy-mm-dd).
   * 
   * @param string $datetime Datetime in Brazilian format
   * @return string Datetime in database format or NULL
   */
  private function datetimeEncode(
    string $datetime
  ): string {
    /* Return NULL as-is for null values */
    if( $datetime === "NULL" ){
      return $datetime;
    }

    
    /* Convert date part from dd/mm/yyyy to yyyy-mm-dd */
    if( preg_match("#(\d{2})\/(\d{2})\/(\d{4})#", $this->stringFilterQuotes( $datetime ))){
      $datetime = preg_replace("#(\d{2})\/(\d{2})\/(\d{4})#", "$3-$2-$1", $this->stringFilterQuotes($datetime));
    }

    /* Convert full datetime from dd/mm/yyyy HH:ii:ss to yyyy-mm-dd HH:ii:ss */
    if(preg_match("#(\d{2})\/(\d{2})\/(\d{4}) (\d{2}:\d{2}:\d{2})#", $this->stringFilterQuotes($datetime))){
      $datetime = preg_replace("#(\d{2})\/(\d{2})\/(\d{4}) (\d{2}:\d{2}:\d{2})#", "$3-$2-$1 $4", $this->stringFilterQuotes($datetime));
    }

    return $datetime;   
  }

  /**
   * Converts database datetime format to Brazilian format (dd/mm/yyyy HH:ii:ss).
   * 
   * @param string $datetime Datetime in database format
   * @return string Datetime in Brazilian format
   */
  private function datetimeDecode(
    string $datetime
  ): string {
    /* Convert to Brazilian datetime format */
    return date( "d/m/Y H:i:s", strtotime( $datetime ));
  }

  /**
   * Converts Brazilian date format (dd/mm/yyyy) to database format (yyyy-mm-dd).
   * 
   * @param string $date Date in Brazilian format
   * @return string Date in database format or NULL
   */
  public function dateEncode(
    string $date
  ): string {
    /* Return NULL as-is for null values */
    if($date === "NULL"){
      return $date;
    }

    /* Convert from dd/mm/yyyy to yyyy-mm-dd */
    if( preg_match( "#(\d{2})\/(\d{2})\/(\d{4})#", $this->stringFilterQuotes( $date ))){
      $date = preg_replace("#(\d{2})\/(\d{2})\/(\d{4})#", "$3-$2-$1", $this->stringFilterQuotes($date));
    }

    return $date;
  }

  /**
   * Converts database date format to Brazilian format (dd/mm/yyyy).
   * 
   * @param string $date Date in database format
   * @return string Date in Brazilian format
   */
  private function dateDecode(
    string $date
  ): string {
    /* Convert to Brazilian date format */
    return date( "d/m/Y", strtotime( $date ));
  }  
  
  /**
   * Converts Brazilian decimal format (1.234,56) to database format (1234.56).
   * 
   * @param string|null $decimal Decimal in Brazilian format
   * @return string|null Decimal in database format
   */
  public function decimalEncode(
    string | null $decimal
  ): string | null {
    /* Check if decimal uses comma as separator */
    if(preg_match("#,#", $decimal) === 1){
      /* Remove thousand separators and replace comma with dot */
      return preg_replace(
        [ "#\.#", "#,#" ], [ "", "." ], $this->stringFilterQuotes($decimal)
      );
    } else {
      return $decimal;
    };
  }

  /**
   * Converts database decimal to Brazilian format (1.234,56) or float.
   * 
   * @param string|null $decimal Decimal in database format
   * @param bool|null $isResult If true, returns formatted string; if false, returns float
   * @param int|null $numberDigitsAfterTheComma Decimal precision for formatting
   * @return float|string Formatted decimal string or float value
   */
  public static function decimalDecode(
    string | null $decimal,
    bool|null $isResult = null,
    int|null $numberDigitsAfterTheComma = null
  ): float|string {
    /* Format as Brazilian decimal string if isResult is true */
    if($isResult === true){
      /* Extract decimal places to determine precision */
      [ $_, $floatingPoints ] = (
        explode(".", (string)$decimal)
      );

      /* Calculate precision based on existing decimal places */
      if(is_null($floatingPoints) === false){
        $precision = strlen($floatingPoints) < 2 
          ? 2 : strlen($floatingPoints);
      } else {
        $precision = 2;
      }

      /* Format with thousand separator (.) and decimal separator (,) */
      return number_format(
        $decimal, (
          $numberDigitsAfterTheComma !== null
            ? $numberDigitsAfterTheComma 
            : $precision
        ), ",", "."
      );
    }

    /* Return as float for calculations */
    return (float)$decimal;
  } 

  /**
   * Escapes text for safe database insertion.
   * 
   * @param string $string Text to escape
   * @return string Escaped text
   */
  public function textEncode(
    string $string
  ): string|array {
    $hasListToInOrNotIn = "#^\(([A-Za-z0-9_]+)(,[A-Za-z0-9_]+)*\)$#";
    if( preg_match( $hasListToInOrNotIn, $string ) === 1 ){
      return explode(",", str_replace([ "(", ")" ], "", $string));
    }

    /* Add slashes to escape special characters */
    return addslashes(
      $this->stringFilterQuotes(
        $string
      )
    );
  }
  
  /**
   * Converts boolean/string flag to integer (1 or 0) for database storage.
   * 
   * @param bool|int|string $flag Flag value to convert
   * @return int 1 for true, 0 for false
   */
  public function flagEncode(
    bool | int | string $flag
  ): int {
    /* Convert boolean to integer */
    if( is_bool( $flag )){
      return $flag ? 1 : 0;
    } else
    /* Convert string to boolean then integer */
    if( is_string( $flag )){
      return filter_var( $flag, FILTER_VALIDATE_BOOLEAN );
    }

    /* Return integer as-is */
    return $flag;
  }

  /**
   * Converts database integer flag (1 or 0) to boolean.
   * 
   * @param string|null $value Flag value from database
   * @return bool True if 1, false otherwise
   */
  public static function flagDecode(
    string | null $value,
  ): bool {
    /* Convert 1 to true, anything else to false */
    return (int)$value === 1 
      ? true : false;
  }

  /**
   * Encodes PHP value to database format based on column type.
   * 
   * @param mixed $mixed Value to encode
   * @return mixed Encoded value ready for database insertion
   */
  public function Encode(
    mixed $mixed
  ): mixed {
    /* Convert null to SQL NULL string */
    if( is_null( $mixed ) || strtoupper(( string ) $mixed ) === "NULL" ){
      return "NULL";
    }
    
    /* Apply type-specific encoding */
    return match( $this ){
      ColumnType::date => $this->dateEncode($mixed),
      ColumnType::datetime => $this->datetimeEncode($mixed),
      ColumnType::decimal => $this->decimalEncode($mixed),
      ColumnType::text => $this->textEncode($mixed),
      ColumnType::enum => $this->textEncode($mixed),
      ColumnType::longtext => $this->textEncode($mixed),
      ColumnType::flag => $this->flagEncode($mixed),
        default => $mixed
    };
  }

  /**
   * Decodes database value to PHP format based on column type.
   * 
   * @param mixed $mixed Value from database
   * @param bool|null $isResult If true, returns formatted string for display
   * @param int|null $numberDigitsAfterTheComma Decimal precision for formatting
   * @return mixed Decoded value in PHP format
   */
  public function Decode(
    mixed $mixed,
    bool|null $isResult = null,
    int|null $numberDigitsAfterTheComma = null
  ): mixed {
    /* Return null as-is */
    if(is_null($mixed)){
      return null;
    }

    /* Apply type-specific decoding */
    return match( $this ){
      ColumnType::date => $this->dateDecode($mixed),
      ColumnType::datetime => $this->datetimeDecode($mixed),
      ColumnType::decimal => $this->decimalDecode($mixed, $isResult, $numberDigitsAfterTheComma ),
      ColumnType::flag => $this->flagDecode($mixed),
        default => $mixed
    };
  }
}