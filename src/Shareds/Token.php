<?php

namespace Websyspro\Entity\Shareds;

define( "T_START_PARENTESES", 40 );
define( "T_END_PARENTESES", 41 );
define( "T_START_BRACKET", 91 );
define( "T_END_BRACKET", 93 );
define( "T_START_BRACE", 123 );
define( "T_END_BRACE", 125 );
define( "T_DOT", 46 );
define( "T_COMMA", 44 );
define( "T_SEMICOLON", 59 );
define( "T_COLON", 58 );
define( "T_QUESTION", 63 );
define( "T_PLUS", 43 );
define( "T_MINUS", 45 );
define( "T_MULTIPLY", 42 );
define( "T_DIVIDE", 47 );
define( "T_EQUAL", 61 );
define( "T_GREATER_THAN", 62 );
define( "T_LESS_THAN", 60 );
define( "T_NOT", 33 );

class Token
{
  public int $id;
  public string $type;
  public string $value;

  public function __construct(
    array|string $tokenAll
  ){
    $this->startupsAnalyzed( $tokenAll );
  }

  private function tokenNameById(
    int $tokenId
  ): string {
    return match( $tokenId ){
      40 => "T_START_PARENTESES",
      41 => "T_END_PARENTESES",
      91 => "T_START_BRACKET",
      93 => "T_END_BRACKET",
      46 => "T_DOT",
      44 => "T_COMMA",
      59 => "T_SEMICOLON",
      58 => "T_COLON",
      63 => "T_QUESTION",
      43 => "T_PLUS",
      45 => "T_MINUS",
      42 => "T_MULTIPLY",
      47 => "T_DIVIDE",
      61 => "T_EQUAL",
      62 => "T_GREATER_THAN",
      60 => "T_LESS_THAN",
      33 => "T_NOT",
      123 => "T_START_BRACE",
      125 => "T_END_BRACE",
        default => token_name( $tokenId )
    };
  }

  private function startupsAnalyzed(
    array|string $tokenAll
  ): void {
    [ $this->id, $this->value ] = is_countable( $tokenAll )
      ? $tokenAll : [ ord( $tokenAll ), $tokenAll ];

    $this->type = $this->tokenNameById( $this->id );
  }
}