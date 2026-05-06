<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Decorations\Columns\Flag;
use Websyspro\Entity\Interfaces\Entity;
use Websyspro\Commons\Collection;
use Websyspro\Entity\Enums\Type;
use Websyspro\Commons\Util;

class WhereBody
{
  public AbstractRepository $abstractRepository;
  public Collection $tokens;
  public UseItem $useItem;

  public function __construct(
    AbstractRepository $abstractRepository,
    UseItem $useItem,
    string $whereBody
  ){
    $this->startup( $abstractRepository, $useItem, $whereBody );
    $this->startupAdjustSimplesTokens();
    $this->startupAdjustGroupsTokens();
    $this->startupAdjustReverseTokens();
    $this->startupAdjustEntityTokens();
    $this->startupAdjustFieldsTokens();
    $this->startupAdjustBetweensTokens();
    $this->startupEndTokens();
  }

  public function startup(
    AbstractRepository $abstractRepository,
    UseItem $useItem,
    string $whereBody    
  ): void {
    $this->abstractRepository = $abstractRepository;
    $this->useItem = $useItem;
    $this->tokens = $abstractRepository
      ->normalizedScrpitWhereBody( $whereBody );
  }

  private function getEntityStructure(
  ): EntityStructure {
    return $this->abstractRepository
      ->entityStructure( $this->useItem->getPath() );
  }  

  private function hasTokenValid(
    Token|null $token
  ): bool {
    return $token instanceof Token && Util::inArray( 
      $token->type, [ Type::Entity, Type::String ]
    );
  }

  public function isFieldsEquals(
    Token $currTokenA,
    Token $nextTokenA,
    Token $currTokenB,
    Token $nextTokenB     
  ): bool {
    return $currTokenA->entity->table === $currTokenB->entity->table && $currTokenA->field === $currTokenB->field
        && $nextTokenA->entity->table === $nextTokenB->entity->table && $nextTokenA->field === $nextTokenB->field;
  }  

  private function startupAdjustSimplesTokens(
  ): void {
    for( $loop=0; $loop < $this->tokens->count(); $loop++ ){
      $currToken = $this->tokens->getOneOrFail( $loop + 0 );
      $logiToken = $this->tokens->getOneOrFail( $loop + 1 );

      if( $currToken instanceof Token && $logiToken instanceof Token ){
        if( $currToken->isEntity() && $logiToken->isCompare() === false ){
          if( $this->getEntityStructure()->types[ $currToken->getField() ] instanceof Flag ){
            $this->tokens->spliceIn( ++$loop, 0, [
              new Token( "=" ), new Token(
                Util::match( "#^!#", $currToken->value )
                  ? "0" : "1"
              )
            ]);

            $currToken->value = Util::replace(
              "#^!#", $currToken->value
            );

            $loop += 2;
          }
        }
      }
    }
  }

  private function startupAdjustGroupsTokens(
    int $group = 0
  ): void {
    for( $loop=0; $loop < $this->tokens->count(); $loop++ ){
      $currToken = $this->tokens->getOneOrFail( $loop );

      if( $currToken instanceof Token ){
        if( $currToken->type === Type::StartGroup ){
          $group++;
        }

        $currToken->group = $group;
        if( $currToken->type === Type::EndGroup ){
          $group--;
        }
      }
    }    
  }

  private function startupAdjustReverseTokens(
  ): void {
    for( $loop=0; $loop < $this->tokens->count(); $loop++ ){
      $currToken = $this->tokens->getOneOrFail( $loop + 0 );
      $compToken = $this->tokens->getOneOrFail( $loop + 1 );
      $nextToken = $this->tokens->getOneOrFail( $loop + 2 );

      if( $currToken instanceof Token && $compToken instanceof Token && $nextToken instanceof Token){
        if( $currToken->isString() && $compToken->isCompare() && $nextToken->isEntity()){
          $this->tokens->setValue( $loop + 0, $nextToken );
          $this->tokens->setValue( $loop + 2, $currToken );
          
          if( $compToken instanceof Token ){
            $compToken->defineReverseCompare();
          }

          $loop += 2;
        }
      }
    }
  }

  private function startupAdjustEntityTokens(
  ): void {
    for( $loop=0; $loop < $this->tokens->count(); $loop++ ){
      $currToken = $this->tokens->getOneOrFail( $loop + 0 );
      $nextToken = $this->tokens->getOneOrFail( $loop + 2 );

      if( $currToken instanceof Token ){
        if( $currToken->isEntity() ){
          $nextToken->setEntityForToken(
            $currToken->setEntity( $this->getEntityStructure()->entity )
          );
        }
      }
    }
  }

  private function startupAdjustFieldsTokens(
  ): void {
    for( $loop=0; $loop < $this->tokens->count(); $loop++ ){
      $currTokenA = $this->tokens->getOneOrFail( $loop + 0 );
      $nextTokenA = $this->tokens->getOneOrFail( $loop + 2 );

      if( $this->hasTokenValid( $currTokenA ) && $this->hasTokenValid( $nextTokenA )){
        for( $subLoop = $loop + 3; $subLoop < $this->tokens->count(); $subLoop++ ){
          $currTokenB = $this->tokens->getOneOrFail( $subLoop + 0 );
          $nextTokenB = $this->tokens->getOneOrFail( $subLoop + 2 );

          if( $this->hasTokenValid( $currTokenB ) && $this->hasTokenValid( $nextTokenB )){
            if( $this->isFieldsEquals( $currTokenA, $nextTokenA, $currTokenB, $nextTokenB )){
              if( $currTokenA->group === $currTokenB->group ){
                $logiToken = $this->tokens->getOneOrFail( $subLoop - 1 );

                $this->tokens->spliceIn( $loop + 3, 0, $this->tokens->spliceOut(
                  $logiToken->isLogical() ? $subLoop - 1 : $subLoop, $logiToken->isLogical() ? 4 : 3
                ));

                $loop += 2;
              }
            }
          }
        }
      }
    }
  }

  private function startupAdjustBetweensTokens(
  ): void {
    for( $loop=0; $loop < $this->tokens->count(); $loop++ ){
      $currToken = $this->tokens->getOneOrFail( $loop + 0 );
      $nextToken = $this->tokens->getOneOrFail( $loop + 2 );

      if( $currToken instanceof Token && $nextToken instanceof Token ){
        // TO DO Validate Betweens
      }
    }
  }

  private function startupEndTokens(
  ): void {
    var_dump( $this->tokens->mapper(fn(Token $t) => $t->value)->joinWithSpace() );
    unset( $this->abstractRepository, $this->useItem );
  }
}