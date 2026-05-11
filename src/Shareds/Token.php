<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Util;

class Token
{
  public int $id;
  public string $type;
  public string $value;

  public const int T_UNKNOWN = -1;

  public const string T_PARENTHESES_OPEN = "(";
  public const string T_PARENTHESES_CLOSE = ")";
  public const string T_SEMICOLON = ";";
  public const string T_COMMA = ",";

  public function __construct(
    array|string $tokenArr
  ){
    $this->startups(
      $tokenArr
    );
  }

  public function isWhiteSpace(): bool {
    return $this->id === T_WHITESPACE;
  }

  private function startups(
    array|string $tokenArr
  ): void {
    Util::isArray( $tokenArr )
      ? $this->defineToken( $tokenArr )
      : $this->defineTokenStr( $tokenArr );
  }

  private function defineTokenName(
    int $id
  ): string {
    return $id !== static::T_UNKNOWN
      ? token_name( $this->id ) : "T_UNKNOWN";
  }

  private function defineToken(
    array|string $tokenArr
  ): void {
    [ $this->id, $this->value ] = $tokenArr;
    $this->type = $this->defineTokenName( $this->id );
  }

  private function defineTokenStr(
    string $tokenArr
  ): void {
    [ $this->id, $this->value ] = [ static::T_UNKNOWN, $tokenArr ];
    $this->type = $this->defineTokenName( $this->id );
  }
}