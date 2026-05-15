<?php

namespace Websyspro\Entity\Shareds;

use Dba\Connection;
use Websyspro\Commons\Collection;
use Closure;
use Websyspro\Commons\Util;

class ExpressionCompare
{
  public CompareField|CompareValue|CompareUnary $sideLeft;
  public CompareField|CompareValue $sideRight;
  public CompareEqual $equal;

  public const int T_FIELD_X_FIELD = 1;
  public const int T_FIELD_X_VALUE = 2;
  public const int T_VALUE_X_FIELD = 3;
  public const int T_VALUE_X_VALUE = 4;

  public function __construct(
    public Collection $tokens,
    public Collection $scopes,
    public Closure $closure
  ){
    $this->startups();
    $this->startupsAnalyzed();
    $this->startupsAnalyzedParsers();
    $this->startupsAnalyzedClear();
  }

  public function get(
  ): string {
    if( $this->sideLeft instanceof CompareUnary ){
      return "";
    }

    return match( $this->compareType()){
      ExpressionCompare::T_FIELD_X_FIELD => 
        Util::sprintFormat( "%s.%s = %s.%s", [
          $this->sideLeft->entity->alias, $this->sideLeft->field->alias,
          $this->sideRight->entity->alias, $this->sideRight->field->alias,
        ]),      
      ExpressionCompare::T_FIELD_X_VALUE => 
        Util::sprintFormat( "%s.%s = %s", [
          $this->sideLeft->entity->alias, $this->sideLeft->field->alias,
          $this->sideRight->value
        ])
    };
  }

  private function startups(
  ): void {}

  private function startupsAnalyzed(
  ): void { 
    if( $this->comparePos() !== -1 ){
      $this->createSideLeftAndRight( match( $this->compareType()){
        ExpressionCompare::T_FIELD_X_VALUE => [ $this->addCompareLeft(), $this->addCompareRight()],
        ExpressionCompare::T_VALUE_X_FIELD => [ $this->addCompareRight(), $this->addCompareLeft()],
          default => [ $this->addCompareLeft(), $this->addCompareRight()],
      });

      $this->createEqual( match( $this->compareType()){
        ExpressionCompare::T_FIELD_X_VALUE => [ $this->addCompareEqual( $this->sideRight ) ],
        ExpressionCompare::T_VALUE_X_FIELD => [ $this->addCompareEqual( $this->sideLeft ) ],
          default => [ $this->addCompareEqual( $this->sideRight )]
      });
    } else $this->createCompareUnary();
  }

  private function startupsAnalyzedParsers(
  ): void {
    if( isset( $this->sideLeft ) && isset( $this->sideRight )){
      if( $this->sideLeft instanceof CompareValue ){
        // TO DO
      }
    }
    if( isset( $this->sideRight ) && isset( $this->sideLeft )){
      if( $this->sideRight instanceof CompareValue ){
        $this->sideRight->value = $this->sideRight->valueIsList === false
          ? $this->sideLeft->columnType->Encode( ClosureUtil::createParam( $this->closure, $this->sideRight->value ) )
          : $this->sideLeft->columnType->Encode(
              Util::sprintFormat( "(%s)", [
                Collection::create( explode( ",", trim( $this->sideRight->value, "[]" )))
                  ->mapper( fn( string $value ) => ( $this->sideLeft->columnType->Encode( ClosureUtil::createParam( $this->closure, $value ) ) ))
                    ->joinWithComma()
              ])
          );
      }
    }
  }

  private function isField(
    Collection $tokens
  ): bool {
    return ExpressionUtil::isField( $tokens );
  }
  
  private function isFieldAndField(
  ): bool {
    return $this->isField( $this->compareLeft()) && $this->isField( $this->compareRight());
  }

  private function isFieldAndValue(
  ): bool {
    return $this->isField( $this->compareLeft()) && $this->isField( $this->compareRight()) === false;
  }

  private function isValueAndField(
  ): bool {
    return $this->isField( $this->compareLeft()) === false && $this->isField( $this->compareRight());
  } 
  
  private function compareType(
  ): int {
    if( $this->isFieldAndField()){
      return ExpressionCompare::T_FIELD_X_FIELD;
    } else if( $this->isFieldAndValue()){
      return ExpressionCompare::T_FIELD_X_VALUE;
    } else if( $this->isValueAndField()){
      return ExpressionCompare::T_VALUE_X_FIELD;
    } else return -1;
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

  private function addCompareLeft(
  ): CompareField|CompareValue {
    return $this->isField( $this->compareLeft()) 
      ? new CompareField( $this, $this->compareLeft()) 
      : new CompareValue( $this, $this->compareLeft()); 
  }

  private function addCompareEqual(
    CompareField|CompareValue $compare
  ): CompareEqual {
    return new CompareEqual( $compare, $this->compareEqual(), $this->compareType()); 
  }  

  private function addCompareRight(
  ): CompareField|CompareValue {
    return $this->isField( $this->compareRight())
      ? new CompareField( $this, $this->compareRight() )
      : new CompareValue( $this, $this->compareRight()); 
  }  

  private function createCompareUnary(
    array $unaryArr = []
  ): void {
    [ $this->sideLeft ] = [ new CompareUnary( $this, $this->compareLeft()) ]; 
  } 

  private function createSideLeftAndRight(
    array $sideLeftAndRightArr = []
  ): void {
    [ $this->sideLeft, $this->sideRight ] = $sideLeftAndRightArr;
  }

  private function createEqual(
    array $equalArr = []
  ): void {
    [ $this->equal ] = $equalArr;
  }  
  
  private function startupsAnalyzedClear(
  ): void {
    unset( $this->tokens, $this->scopes, $this->closure );
  }
}