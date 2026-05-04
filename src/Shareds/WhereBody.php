<?php

namespace Websyspro\Entity\Shareds;

use Dom\TokenList;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use Websyspro\Entity\Enums\Type;
use Websyspro\Entity\Interfaces\Entity;

class WhereBody
{
  private AbstractRepository $abstractRepository;
  private Collection $tokens;
  private UseItem $useItem;

  public function __construct(
    AbstractRepository $abstractRepository,
    UseItem $useItem,
    string $whereBody
  ){
    $this->startup( $abstractRepository, $useItem, $whereBody );
    $this->startupCompleteTokens();
    $this->startupInvetersTokens();
    $this->startupEntitysTokens();
    $this->startupGroupTokens();
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

  private function hasCompareTokenValid(
    Token|null $token
  ): bool {
    return $token instanceof Token && Util::inArray( 
      $token->type, [ Type::Compare ]
    );
  }  

  private function hasTokenValid(
    Token|null $token
  ): bool {
    return $token instanceof Token && Util::inArray( 
      $token->type, [ Type::Entity, Type::String ]
    );
  }

  private function hasGroupCompare(
    Token|null $currtoken,
    Token|null $logitoken,
    Token|null $nextToken
  ): bool {
    return $this->hasTokenValid( $currtoken )
        && $this->hasTokenValid( $nextToken )
        && $this->hasCompareTokenValid( $logitoken );
  }

  public function isFieldsEquals(
    Token $currTokenA,
    Token $nextTokenA,
    Token $currTokenB,
    Token $nextTokenB     
  ): bool {
    // print_r( $currTokenB );
    // print_r( $nextTokenB );
    return $currTokenA->entity->table === $currTokenB->entity->table && $currTokenA->field === $currTokenB->field
        && $nextTokenA->entity->table === $nextTokenB->entity->table && $nextTokenA->field === $nextTokenB->field;
  }  

  private function startupCompleteTokens(
    int $loop = 0,
    int $group = 1
  ): void {
    while( $loop < $this->tokens->count() ){
      $currtoken = $this->tokens->getOneOrFail( $loop );
      $nextToken = $this->tokens->getOneOrFail( $loop + 1 );

      if( $currtoken instanceof Token ){
        if( $currtoken->type === Type::StartGroup ){
          $group++;
        }

        $currtoken->group = $group;
        if( $currtoken->type === Type::EndGroup ){
          $group--;
        }
      } 

      if( $currtoken instanceof Token && $nextToken instanceof Token ){
        if( $currtoken->type === Type::Entity && $nextToken->type === Type::Logical ){
          $hasSingleCompared = !Util::match( "#(=|<>|>=|<=)#", $currtoken->value );

          if( $hasSingleCompared === true ){
            $this->tokens->spliceIn( ++$loop, 0, [
              new Token( "=" ), new Token(
                Util::match( "#^!#", $currtoken->value )
                  ? "0" : "1"
              )
            ]);

            $currtoken->value = Util::replace( "#^!#", $currtoken->value );
            $loop++;
          }
        }
      }

      $loop++;
    }
  }

  private function startupInvetersTokens(
    int $loop = 0
  ): void {
    while( $loop < $this->tokens->count() ){
      $currToken = $this->tokens->getOneOrFail( $loop + 0 );
      $compToken = $this->tokens->getOneOrFail( $loop + 1 );
      $nextToken = $this->tokens->getOneOrFail( $loop + 2 );

      if( $this->hasGroupCompare( $currToken, $compToken, $nextToken ) ){
       if( $currToken->type === Type::String && $nextToken->type === Type::Entity ){
          $this->tokens->setValue( $loop + 0, $nextToken );
          $this->tokens->setValue( $loop + 2, $currToken );
          
          if( $compToken instanceof Token ){
            $compToken->invertCompare();
          }
        }

        $loop += 3;
      }

      $loop++;
    }
  }

  private function startupEntitysTokens(
    int $loop = 0
  ): void {
    $entityStructure = $this->abstractRepository->entityStructure(
      $this->useItem->getPath()
    );

    while( $loop < $this->tokens->count() ){
      $currToken = $this->tokens->getOneOrFail( $loop + 0 );
      $nextToken = $this->tokens->getOneOrFail( $loop + 2 );

      if( $currToken instanceof Token ){
        if( $currToken->type === Type::Entity ){
          if( $entityStructure->entity instanceof Entity ){
            if( Util::match("#^\\$.*->#", $currToken->value) ){
              $currToken->entity = $entityStructure->entity;
              $currToken->field = Util::replace("#^\\$.*->#", $currToken->value );
              $currToken->value = Util::sprintFormat( "%s.%s", [ $currToken->entity->table, $currToken->field ]);

              $nextToken->field = $currToken->field;
              $nextToken->entity = $currToken->entity;
            }
          }
        }
      }

      $loop++;
    }
  }

  private function startupSubGroupTokens(
    Token|null $currTokenA,
    Token|null $nextTokenA,
    int $loop = 0
  ): void {
    while( $loop < $this->tokens->count() ){
      $currTokenB = $this->tokens->getOneOrFail( $loop + 0 );
      $nextTokenB = $this->tokens->getOneOrFail( $loop + 2 );

      if( $this->hasTokenValid($currTokenB) && $this->hasTokenValid($nextTokenB)){
        $isTokenGroup = $this->isFieldsEquals($currTokenA, $nextTokenA, $currTokenB, $nextTokenB);

        if( $isTokenGroup ){
          print_r( "....." );
        }
      }      

      $loop++;
    }
  }

  private function startupGroupTokens(
    int $loop = 0
  ): void {
    while( $loop < $this->tokens->count() ){
      $currTokenA = $this->tokens->getOneOrFail( $loop + 0 );
      $nextTokenA = $this->tokens->getOneOrFail( $loop + 2 );

      if( $this->hasTokenValid( $currTokenA ) && $this->hasTokenValid( $nextTokenA ) ){
        $this->startupSubGroupTokens( $currTokenA, $nextTokenA, $loop );
      }

      $loop++;
    }
  }

  private function startupEndTokens(
  ): void {
    unset( $this->abstractRepository, $this->useItem );
  }
}