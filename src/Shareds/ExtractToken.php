<?php

namespace Websyspro\Entity\Shareds;

class ExtractToken
{ 
  public array $tokens;

  public function __construct(
    public string $script
  ){
    $this->startups();
    $this->startupsAnalyzeds();
  }

  private function startups(
  ): void {
    $this->tokens = array_slice(
      token_get_all( "<?php {$this->script}" ), 1
    );
  }

  private function startupsAnalyzeds(
  ): void {
    $this->tokens = array_filter(
      $this->tokens, fn( array|string $token ) => (
        is_string( $token ) || is_array( $token ) && $token[0] !== T_WHITESPACE 
      )
    );
  }

  public static function get(
    string $script
  ): ExtractToken {
    return new static( $script );
  }
}