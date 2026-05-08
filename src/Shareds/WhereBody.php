<?php

namespace Websyspro\Entity\Shareds;

use ReflectionFunction;
use UnitEnum;
use Websyspro\Entity\Decorations\Columns\Flag;
use Websyspro\Commons\Collection;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Entity\Enums\CompareType;
use Websyspro\Entity\Enums\LogicalType;
use Websyspro\Entity\Enums\Type;
use Websyspro\Commons\Util;

class WhereBody
{
  public AbstractRepository $abstractRepository;
  public ReflectionFunction $reflectionFunction;
  public Collection $tokens;
  public UseItem $useItem;
  public Collection $statics;
  public Collection $params;

  public function __construct(
    AbstractRepository $abstractRepository,
    ReflectionFunction $reflectionFunction,
    UseItem $useItem,
    Collection $statics,
    string $whereBody,
  ){
    $this->startup( $abstractRepository, $reflectionFunction, $useItem, $statics, $whereBody );
    $this->startupAdjustSubQuerysTokens();
    $this->startupAdjustSimplesTokens();
    $this->startupAdjustGroupsTokens();
    $this->startupAdjustReverseTokens();
    $this->startupAdjustEntityTokens();
    $this->startupAdjustFieldsTokens();
    // $this->startupAdjustParsesTokens();
    // $this->startupAdjustBetweensTokens();
    $this->startupEndTokens();
  }

  public function startup(
    AbstractRepository $abstractRepository,
    ReflectionFunction $reflectionFunction,
    UseItem $useItem,
    Collection $statics,
    string $whereBody    
  ): void {
    $this->abstractRepository = $abstractRepository;
    $this->reflectionFunction = $reflectionFunction;
    $this->useItem = $useItem;
    $this->statics = $statics;
    $this->tokens = $abstractRepository
      ->normalizedScrpitWhereBody( $whereBody );
  }

  private function getEntityStructure(
    Token|null $token = null
  ): EntityStructure {
    if( $token instanceof Token ){
      [ $parameter ] = explode( "->", $token->value );
      
      $useLists = $this->abstractRepository->useList->where(
        fn(UseItem $u) => isset($u->parameter) && $u->parameter === $parameter
      );

      if( $useLists->count() !== 0 ){
        [ $useItem ] = $useLists->toArray();

        return $this->abstractRepository
          ->entityStructure( $useItem->getPath() );
      }
    }

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

  private function hasSubQuery(
    Token $token
  ): bool {
    return Util::match( 
      "#^(!?)(\\$[a-zA-Z_]\\w*(?:->[a-zA-Z_]\\w*)*)->(any|sum|count|min|max|avg|all|first)\\s*\\(#", $token->value
    );
  }

  private function tokensToString(
    Collection $tokens
  ): string {
    return $tokens->mapper(fn(Token $t) => $t->value)->joinWithSpace();
  }

  private function startupSubTokens(
    int $startTokenSubQuery,
    int $endTokenSubQuery
  ): int {
    $tokensOut = $this->tokens->spliceOut(
      $startTokenSubQuery, $endTokenSubQuery
    );

    $logiToken = $tokensOut->getOneOrFail(0);
    if( $logiToken instanceof Token && $logiToken->type === Type::Logical ){
      $tokensOutSubQuery = $tokensOut->slice( 1, -1 );
    } 

    $subQueryEvent = $tokensOutSubQuery->first();
    $subQueryTokens = $tokensOutSubQuery
      ->slice( 1 );

    $whereList = new WhereList( 
      $this->abstractRepository,
      $this->reflectionFunction,
      $this->tokensToString( $subQueryTokens ),
    );

    $this->tokens->spliceIn(
      $startTokenSubQuery, 0, $whereList->whereBody->tokens
    );

    return $whereList->whereBody->tokens->count();
  }

  private function startupAdjustSubQuerysTokens(
    int $startTokenSubQuery = 0,
    int $group = 0
  ): void {
    for( $loop = 0; $loop < $this->tokens->count(); $loop++ ){
      $prevToken = $this->tokens->getOneOrFail( $loop - 1 );
      $token = $this->tokens->getOneOrFail( $loop );

      if( $this->hasSubQuery( $token ) === false ){
        continue;
      }
      
      $startTokenSubQuery = $loop;
      if( $prevToken instanceof Token ){
        if( $prevToken->type === Type::Logical ){
          $startTokenSubQuery--;
        }
      }

      for( $subLoopA = ++$loop; $subLoopA < $this->tokens->count(); $subLoopA++ ){
        $subTokenA = $this->tokens->getOneOrFail( $subLoopA );

        if( $subTokenA->type !== Type::EndGroup ){
          continue;
        }

        for( $subLoopB = ++$subLoopA; $subLoopB < $this->tokens->count(); $subLoopB++ ){
          $subTokenB = $this->tokens->getOneOrFail( $subLoopB );

          if( $subTokenB instanceof Token ){
            if( $subTokenB->type === Type::StartGroup ){
              $group++;
            }
            
            if( $group === -1 ){
              $loop = $this->startupSubTokens( 
                $startTokenSubQuery, $subLoopB - $startTokenSubQuery
              );

              break;
            }

            if( $subTokenB->type === Type::EndGroup ){
              $group--;
            }
          }
        }

        $group = 0;
        break;
      }
    }
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

      if( $currToken instanceof Token && $nextToken instanceof Token ){
        if( $currToken->isEntity() ){
          $currToken->setEntity( $this->getEntityStructure( $currToken )->entity );

          if( $nextToken->isString()){
            $nextToken->setEntityForToken(
              $currToken
            );
          } else {
            $nextToken->setEntity( 
              $this->getEntityStructure( $nextToken )->entity
            );
          }
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

  private function enumValue(
    mixed $enumCaseConstant,
    string $enumValue
  ): string {
    if( $enumCaseConstant instanceof UnitEnum ){
      if( isset( $property ) && $property === "name" ){
        return $enumCaseConstant->name;
      } else
      if( isset( $property ) && $property === "value" && $enumCaseConstant instanceof BackedEnum ){
        return $enumCaseConstant->value;
      } else
      if( isset( $property ) === false ){
        return ( $enumCaseConstant instanceof BackedEnum ) 
          ? $enumCaseConstant->value 
          : $enumCaseConstant->name;
      }
    }

    return $enumValue;
  }

  private function parseEnum(
    string $enumValue
  ): string {
    $hasEnumValue = $this->hasEnumValue($enumValue);

    if( $hasEnumValue === false ){
      return $enumValue;
    }

    preg_match( 
      "#^([\w\\\]+)::(\w+)(?:->(\w+))?$#", 
      $enumValue, $enumPathsAlls
    );

    $enumPaths = new Collection( Util::slice( $enumPathsAlls, 1 ));
    $enumPaths->mapper(fn(string $path) => !empty($path));

    if( $enumPaths->count() === 3 ){
      [ $entity, $case, $property ] = $enumPaths->toArray();
    } else [ $entity, $case ] = $enumPaths->toArray();

    foreach( $this->abstractRepository->useList as $useItem ){
      if( $useItem instanceof UseItem ){
        if( $useItem->entity === $entity ){
          $entity = $useItem->getPath();
        }
      }
    }

    $constantName = "{$entity}::{$case}";
    if( defined( $constantName )){
      $enumCase = constant($constantName);
 
      if( $enumCase instanceof UnitEnum ){
        if( isset( $property )){
          if( $property === "name" ){
            return $enumCase->name;
          }

          if( $property === 'value' && $enumCase instanceof BackedEnum ){
            return $enumCase->value;
          }          
        }
      }

      if( isset( $enumCase->value )){
        return $enumCase->value;
      }
    }

    return $enumValue;
  }
  
  private function parseStatic(
    string $staticValue
  ): string {
    if( $this->statics->count() !== 0 ){
      $staticVar = Util::replace( 
        "#^(\{\\$|\\$)|\\}$#", $staticValue
      );

      $statics = $this->statics->toArray();
      if( isset( $statics[ $staticVar ])){
        return $statics[ $staticVar ];
      }
    }

    return $staticValue;
  }   

  private function isContainsStatic(
    string $value
  ): bool {    
    return preg_match( "#{\\$|\\$#", $value );
  }   

  private function hasEnumValue(
    string $value
  ): bool {
    return preg_match( "#^[a-zA-Z]{1}.*::.*(->(?:name|value))?$#", $value );
  }

  public function isStaticValue(
    string $value
  ): bool {
    return preg_match( "#^\\$#", $value );
  }   

  private function parseValueExplode(
    string $value 
  ): array {
    if( $this->isContainsStatic( $value ) && $this->hasEnumValue( $value ) ){
      $pattern = "#'[^']*'|\"[^\"]*\"|\\{\\$[\\w-]+\\}|\\$?[\\w\\\\-]+(?:->|::)[\\w\\\\-]+|\\d{2}/\\d{2}/\\d{4}|>=|<=|<>|[<>=!]+|\\(|\\)|,|([a-zA-ZÀ-ÿ\d/:$%\\\\]+(?:\s+[a-zA-ZÀ-ÿ\d/:$%\\\\]+)*\s*)#u";

      $tokensFromPattern = Util::matchAll( 
        $pattern, preg_replace( "#^'|'$#", "", $value )
      );

      return Util::mapper( 
        array_shift( $tokensFromPattern ), fn( string $value ) => (
          preg_replace([ "#^'|'$#", "#^\\{\\$#", "#\\}$#" ], [ "", "$", "" ], $value )
        ), 
      );      
    }

    return [ $value ];
  }

  private function parseValue(
    Token $token
  ): Token {
    $tokens = $this->parseValueExplode( $token->value );

    if( Util::isArray( $tokens ) && Util::sizeArray( $tokens ) !== 0 ){
      $columnType = $this->getEntityStructure( $token )
        ->types[ $token->getField() ]->columnType;
        
      if( $columnType instanceof ColumnType ){
        for( $loop = 0; $loop < Util::sizeArray( $tokens ); $loop++ ){
          if( $this->hasEnumValue( $tokens[ $loop ] )){
            $tokens[ $loop ] = $this->parseEnum( $tokens[ $loop ] );
          } else if( $this->isStaticValue( $tokens[ $loop ] )){
            $tokens[ $loop ] = $this->parseStatic( $tokens[ $loop ] );
          }
        }

        $encodeValue = $columnType->Encode(
          implode( "", $tokens )
        );

        if( is_array( $encodeValue )){
          for( $loop = 0; $loop < Util::sizeArray( $encodeValue ); $loop++ ){
            $encodeValue[ $loop ] = $this->createParamItem( $encodeValue[ $loop ]);
          }

          $token->value = sprintf(
            "(%s)", implode( ",", $encodeValue )
          );
        } else {
          $token->value = $this->createParamItem( $encodeValue );
        }          
      }  
    }

    return $token;
  }

  private function startupAdjustParsesTokens(
  ): void {
    for( $loop=0; $loop < $this->tokens->count(); $loop++ ){
      $token = $this->tokens->getOneOrFail( $loop + 0 );

      if( $token instanceof Token ){
        if( Util::inArray( $token->type, [ Type::String, Type::Static, Type::Enum ])){
          $this->parseValue( $token );
        }
      }
    }
  }

  private function createParamItem(
    string $encodeValue,
    string|null $encodeValueKey = null
  ): string {
    if( isset( $this->params ) === false ){
      $this->params = new Collection();
    }

    $this->params->add( 
      $encodeValue, $encodeValueKey = Util::sprintFormat(
        ":param_%s", [ $this->params->count() ]
      ) 
    );

    return $encodeValueKey;
  }   

  private function hasBetweenIsValids(
    Token|null $currTokenA,
    Token|null $compTokenA,
    Token|null $nextTokenA,
    Token|null $logiToken,
    Token|null $currTokenB,
    Token|null $compTokenB,
    Token|null $nextTokenB,
  ): bool {
    return $currTokenA instanceof Token
        && $compTokenA instanceof Token && in_array(
          CompareType::tryFrom( $compTokenA->value ), [
            CompareType::GreaterEqual, CompareType::LessEqual,
            CompareType::Greater, CompareType::Less
          ]
        )
        && $nextTokenA instanceof Token
        && $logiToken instanceof Token && $logiToken->type === Type::Logical
        && $currTokenB instanceof Token
        && $compTokenB instanceof Token && in_array(
          CompareType::tryFrom( $compTokenB->value ), [
            CompareType::GreaterEqual, CompareType::LessEqual,
            CompareType::Greater, CompareType::Less
          ]
        )
        && $nextTokenB instanceof Token
        && $compTokenA->value !== $compTokenB->value
        && ( CompareType::tryFrom( $compTokenA->value ) === CompareType::GreaterEqual
          && CompareType::tryFrom( $compTokenB->value ) === CompareType::LessEqual 
          || CompareType::tryFrom( $compTokenA->value ) === CompareType::Greater
          && CompareType::tryFrom( $compTokenB->value ) === CompareType::Less
        );
  }

  private function hasBetweenIsEqualEntity(
    Token|null $currTokenA,
    Token|null $currTokenB    
  ): bool {
    $hasEntityEquals = $currTokenA->entity->table === $currTokenB->entity->table;
    $hasFieldsEquals = $currTokenA->field === $currTokenB->field;

    if( $hasEntityEquals === false && $hasFieldsEquals === false ){
      return false;
    }

    $columnType = $this->getEntityStructure()
      ->types[ $currTokenA->getField() ]->columnType;

    if( $columnType instanceof ColumnType ){
      return Util::inArray( $columnType, [
        ColumnType::date,
        ColumnType::datetime,
        ColumnType::number, 
        ColumnType::decimal
      ]);
    }

    return false;
  }

  private function hasPossibleBetween(
    int $loop
  ): bool {
    if($this->tokens->count() - $loop >= 7){
      [ $currTokenA, $compTokenA, $nextTokenA, $logiToken,
        $currTokenB, $compTokenB, $nextTokenB 
      ] = $this->tokens->slice( $loop, 7 )->toArray();

      $hasBetweenIsValids = $this->hasBetweenIsValids(
        $currTokenA, $compTokenA, $nextTokenA, $logiToken,
        $currTokenB, $compTokenB, $nextTokenB
      );

      if( $hasBetweenIsValids ){
        return $this->hasBetweenIsEqualEntity( 
          $currTokenA, $currTokenB
        );
      }
    }

    return false;
  }

  private function startupAdjustBetweensTokens(
  ): void {
    for( $loop=0; $loop < $this->tokens->count(); $loop++ ){
      $currToken = $this->tokens->getOneOrFail( $loop + 0 );
      $nextToken = $this->tokens->getOneOrFail( $loop + 2 );

      if( $currToken instanceof Token && $nextToken instanceof Token ){
        if( $this->hasPossibleBetween( $loop )){
          $tokensPossibleBetweens = $this->tokens->spliceOut($loop, 7);

          [ $nextTokenA, $nextTokenB ] = [
            ...$tokensPossibleBetweens->slice(2, 1)->toArray(),
            ...$tokensPossibleBetweens->slice(6, 1)->toArray()
          ];

          if( $nextTokenA instanceof Token && $nextTokenB instanceof Token ){
            $betWeenToken = new Token( LogicalType::Between->value );
            $betWeenItemToken = new Token( Util::sprintFormat( "%s %s %s", [
              $nextTokenA->value, LogicalType::And->value, $nextTokenB->value
            ]));

            $this->tokens->spliceIn( $loop, 0, [
              $currToken, $betWeenToken, $betWeenItemToken->setEntityForToken( $currToken )
            ]); $loop += 6;
          }
        }
      }
    }
  }

  private function startupEndTokens(
  ): void {
    // var_dump( $this->tokens->mapper(fn(Token $t) => $t->value)->joinWithSpace() );
    unset( $this->abstractRepository, $this->useItem );
  }
}