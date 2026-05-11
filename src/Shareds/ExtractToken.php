<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use Websyspro\Entity\Shareds\ExpressionUtil;

class ExtractToken
{ 
  public Collection $tokens;

  public function __construct(
    public string $script
  ){
    $this->startups();
    $this->startupsDropWhiteSpace();
    $this->startupsDropUnnecessaryStartScripts();
    $this->startupsDropUnnecessaryEndScripts();
  }

  private function startups(
  ): void {
    $this->tokens = ExpressionUtil::createTokens(
      Collection::create( ExpressionUtil::getTokenAll( $this->script ))
    );
  }

  private function startupsDropWhiteSpace(
  ): void {
    $this->tokens = ExpressionUtil::dropWhiteSpace( $this->tokens );
  }

  private function startupsDropUnnecessaryStartScripts(
  ): void {
    $this->tokens = ExpressionUtil::dropUnnecessaryStartScript( $this->tokens );
  }

  private function startupsDropUnnecessaryEndScripts(
  ): void {
    $this->tokens = ExpressionUtil::dropUnnecessaryEndScripts( $this->tokens );
  }

  public static function get(
    string $script
  ): ExtractToken {
    return new static( $script );
  }
}