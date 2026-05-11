<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;

class ExpressionNode
{
  public function __construct(
    public Collection $tokens,
    public Collection $scopes
  ){
    $this->startups();
    $this->startupsAnalyzedParameters();
    $this->startupsAnalyzedContents();
    $this->startupsAnalyzedParser();
  }

  private function startups(
  ): void {}

  private function startupsAnalyzedParameters(
  ): void {
    $scopesNew = $this->tokens
      ->slice( ExpressionUtil::find( $this->tokens, T_FN ) + 2, ExpressionUtil::find( $this->tokens, T_DOUBLE_ARROW ) - 3 )
      ->where( fn( Token $token ) => $token->value !== Token::T_COMMA )->chunk(2)
      ->mapper( fn( Array $tokens ) => new Scope( $tokens ));

    if( $scopesNew->exist()){
      [ $scope ] = $scopesNew->toArray();
      
      if( $scope instanceof Scope ){
        $this->scopes = $this->scopes->where( 
          fn( Scope $scopeItem ) => (
            $scopeItem->isEquals( $scope ) === false
          )
        );

        $this->scopes->merge( $scopesNew );
      }
    }
  }

  private function startupsAnalyzedContents(
  ): void {
    $this->tokens = $this->tokens
      ->slice( ExpressionUtil::find( $this->tokens, T_DOUBLE_ARROW ) + 1 );
  }

  private function startupsAnalyzedParser(
  ): void {
    $this->tokens = ExpressionUtil::expressionStructureValid(
      $this->tokens, $this->scopes
    );
  } 
}