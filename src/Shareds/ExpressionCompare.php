<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use Closure;

class ExpressionCompare
{
  public CompareField|CompareValue|CompareUnary $left;
  public CompareEqual $equal;
  public CompareField|CompareValue $right;

  public function __construct(
    public Collection $tokens,
    public Collection $scopes,
    public Closure $closure
  ){
    $this->startups();
    $this->startupsAnalyzed();
    $this->startupsAnalyzedClear();
  }

  public function get(
  ): string {
    return "";
  }

  private function startups(
  ): void {}

  private function startupsAnalyzed(
  ): void { 
    if( $this->comparePos() !== -1 ){
      if( $this->isFieldAndField() && $this->isFieldAndValue()){
        $this->left  = $this->createCompareLeft();
        $this->equal = $this->createCompareEqual();
        $this->right = $this->createCompareRight();
      } else if( $this->isValueAndField()){
        $this->left  = $this->createCompareRight();
        $this->equal = $this->createCompareEqual();
        $this->right = $this->createCompareLeft();        
      }
    } else {
      $this->left = $this->createCompareUnare();
    }
  }

  private function isField(
    Collection $tokens
  ): bool {
    return ExpressionUtil::isField( $tokens );
  } 

  private function isFieldAndField(
  ): bool {
    return $this->isField( $this->compareLeft()) 
        && $this->isField( $this->compareRight());
  }

  private function isFieldAndValue(
  ): bool {
    return $this->isField( $this->compareLeft()) 
        && $this->isField( $this->compareRight()) === false;
  }

  private function isValueAndField(
  ): bool {
    return $this->isField( $this->compareLeft()) === false
        && $this->isField( $this->compareRight());
  }   

  private function comparePos(
  ): int {
    return ExpressionUtil::findCompare( $this->tokens );
  }

  private function compareLeft(
  ): Collection {
    return $this->comparePos() !== -1 
      ? $this->tokens->slice( 0, $this->comparePos() ) 
      : $this->tokens->slice( 0 );
  }

  private function compareEqual(
  ): Collection {
    return $this->tokens->slice( $this->comparePos(), 1 );
  }  
  
  private function compareRight(
  ): Collection {
    return $this->tokens->slice( $this->comparePos() + 1 );
  }

  private function createCompareLeft(
  ): CompareField|CompareValue {
    return $this->isField( $this->compareLeft())
      ? new CompareField( $this, $this->compareLeft())
      : new CompareValue( $this, $this->compareLeft()); 
  }

  private function createCompareEqual(
  ): CompareEqual {
    return new CompareEqual( $this->compareEqual()); 
  }  

  private function createCompareRight(
  ): CompareField|CompareValue {
    return $this->isField( $this->compareRight())
      ? new CompareField( $this, $this->compareRight() )
      : new CompareValue( $this, $this->compareRight()); 
  }  

  private function createCompareUnare(
  ): CompareUnary {
    return new CompareUnary( $this, $this->compareLeft()); 
  } 
  
  private function startupsAnalyzedClear(
  ): void {
    unset( $this->tokens );
  }
}