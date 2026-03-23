<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\CompareType;
use Websyspro\Entity\Enums\LogicalType;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Entity\Enums\TokenType;
use Websyspro\Entity\Enums\EntityRoot;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionFunction;
use BackedEnum;
use UnitEnum;

class Structure
{
  public Collection $entitys;
  public Collection $parametersList;
  public Collection $parameters;
  public Collection $statics;
  public Collection $joins;
  public Collection $uses;
  public Collection $tokens;
  public Collection $params;

  public function __construct(
    public ReflectionFunction $reflectionFunction
  ){
    $this->setCreateList();
    $this->setEntitysList();
    $this->setParametersList();
    $this->setStaticsList();
    $this->setJoinsList();
    $this->setUsesList();
    $this->setTokensList();
    $this->setTokensListGroups();
    $this->setTokensListParses();
    $this->setTokensListResume();
  }

  private function setCreateList(
  ): void {
    $this->joins = new Collection();
    $this->tokens = new Collection();
    $this->params = new Collection();
    $this->statics = new Collection();
    $this->entitys = new Collection();
    $this->parameters = new Collection();
    $this->parametersList = new Collection();
  }

  private function setEntitysList(
  ): void {
    foreach( $this->reflectionFunction->getParameters() as $parameter ){
      if( $parameter->getType() instanceof ReflectionNamedType  ){
        $this->entitys->add( new Entity( 
            $parameter->getType()->getName() 
          ), $parameter->getType()->getName()
        );
      }
    }
  }

  private function getTypeName(
    ReflectionParameter $reflectionParameter
  ): string|null {
    if( $reflectionParameter->getType() instanceof ReflectionNamedType ){
      return $reflectionParameter->getType()->getName();
    }

    return null;
  }
  
  private function setParametersList(
  ): void {
    foreach( $this->reflectionFunction->getParameters() as $parameter ){
      if( $parameter instanceof ReflectionParameter ){
        $this->parametersList->add( 
          $this->getTypeName( $parameter ), $parameter->getName()
        );
        
        $this->parameters->add(
          new Parameter( $parameter->getName(), Util::callUserClassFN( 
            $this->getTypeName( $parameter ), "getAttributes", []
          )), $this->getTypeName( $parameter )
        );
      }
    }
  }

  private function setStaticsList(
  ): void {
    $this->statics = new Collection(
      $this->reflectionFunction->getStaticVariables()
    );
  }

  private function setJoinsList(
  ): void {
    $this->setJoinsRecursiveList(
      $this->entitys->first()
    );
  }

  private function setJoinsRecursiveList(
    Entity $entity,
    Entity|null $entityReference = null,
    array|null $entityHistory = [],
    AttributeType $attributeType = AttributeType::oneToOne
  ): void {
    if( $this->parameters->getOneOrFail( $entity->class ) !== null ){
      if( $this->joins->getOneOrFail( $entity->class ) === null ){
        $parameter = $this->parameters->getOneOrFail( $entity->class );
        
        if( $parameter !== null ){
          if( $parameter instanceof Parameter ){
            $entityHistory = array_merge( 
              $entityHistory, [ 
                $attributeType 
              ]
            );
              
            $tokenRoot = Util::sizeArray(
              Util::where( $entityHistory, 
              fn( AttributeType $attributeType ) => $attributeType === AttributeType::oneToMany )
            ) === 0 ? EntityRoot::Yes : EntityRoot::No;

            if( $entityReference !== null && $entityReference instanceof Entity ){
              $entityFromParameter = $this->parameters->getOneOrFail( $parameter->entityStructure->entity->class );
              $entityParentFromParameter = $this->parameters->getOneOrFail( $entityReference->class );

              if( $attributeType === AttributeType::oneToOne ){
                if( $entityParentFromParameter instanceof Parameter ){
                  $foreigns = $entityParentFromParameter->entityStructure->foreigns->where( fn( ForeignKey $foreignKey ) =>  (
                    $foreignKey->entityReference->entity->class === $parameter->entityStructure->entity->class && 
                    $foreignKey->entity->class === $entityReference->class
                  ));
                }
              } else
              if( $attributeType === AttributeType::oneToMany ) {
                if( $entityFromParameter instanceof Parameter ){
                  $foreigns = $entityFromParameter->entityStructure->foreigns->where( fn(ForeignKey $foreignKey ) =>  (
                    $foreignKey->entityReference->entity->class === $entityReference->class && 
                    $foreignKey->entity->class === $parameter->entityStructure->entity->class
                  ));
                }
              }

              $this->joins->add(
                new HierarchyJoin(
                  $parameter->entityStructure->entity,
                  $entityReference, $entityHistory, $tokenRoot, $foreigns->first()
                ), $parameter->entityStructure->entity->class
              );              
            } else {
              $this->joins->add(
                new HierarchyJoin(
                  $parameter->entityStructure->entity,
                  $entityReference, $entityHistory, $tokenRoot, null
                ), $parameter->entityStructure->entity->class
              );
            }



            $parameter->entityStructure->oneToOne->mapper(
              fn(Entity $entity) => $this->setJoinsRecursiveList( 
                $entity, $parameter->entityStructure->entity, $entityHistory, AttributeType::oneToOne
              )
            );    

            $parameter->entityStructure->oneToMany->mapper(
              fn(Entity $entity) => $this->setJoinsRecursiveList( 
                $entity, $parameter->entityStructure->entity, $entityHistory, AttributeType::oneToMany
              )
            );      
          }
        }
      }
    }
  }

  private function setUsesList(
  ): void {
    $fileLines = new Collection(
      file( $this->reflectionFunction->getFileName())
    );

    $fileUses = $fileLines->where(
      fn(string $text) => preg_match(
        "#^use\s*#", $text
      ) === 1
    );    

    $this->uses = $fileUses->mapper( 
      fn( string $useClass ) => (
        new UseClass( $useClass )
      )
    );
  }

  private function setParseToken(
    string $sourceString,
    array $groupEntityRoots = [],
    int $groupStartPos = -1,
    int $group = 1
  ): Collection {
    $tokensFromPattern = StructureUtil::getBreakTokens( $sourceString );
    
    if( Util::sizeArray( $tokensFromPattern ) !== 0 ){
      [ $tokens ] = $tokensFromPattern;

      /** @var array<int, string|Token> $tokens */
      for( $i = 0; $i < Util::sizeArray( $tokens ); $i++ ){
        $currType = StructureUtil::getTokenByValue( $tokens[ $i + 0 ]);
        
        $hasEntityOrString = Util::inArray( 
          $currType, [ TokenType::Entity, TokenType::String ]
        ); 

        if( $hasEntityOrString === false ){
          if( $currType === TokenType::StartGroup ){
            $groupEntityRoots = [];
            $groupStartPos = $i;
            $group++;
          }
          
          /* Append Tokens Arr */
          $tokens[ $i ] = StructureUtil::createToken( 
            $currType, $tokens[ $i ], $group, $this
          );

          if( $tokens[ $i ]->type === TokenType::EndGroup ){
            if( $groupStartPos !== -1 ){
              if( $tokens[ $groupStartPos ] instanceof Token ){
                if( $tokens[ $groupStartPos ]->type === TokenType::StartGroup ){
                  $tokens[ $groupStartPos ]->setRoot(
                    Util::inArray( EntityRoot::Yes, $groupEntityRoots[ $group ] ) 
                      ? EntityRoot::Yes
                      : EntityRoot::No
                  );

                  if( $tokens[ $groupStartPos - 1 ] instanceof Token ){
                    if( $tokens[ $groupStartPos - 1 ]->type === TokenType::Logical ){
                      $tokens[ $groupStartPos - 1 ]->setRoot( $tokens[ $groupStartPos ]->root );
                    }
                  }
                  
                  if( $tokens[ $i ] instanceof Token ){
                    $tokens[ $i ]->setRoot( $tokens[ $groupStartPos ]->root );
                  }
                }
              }
            }

            $groupEntityRoots = [];
            $group--;
          }
        } else {
          $compType = StructureUtil::getTokenByValue( $tokens[ $i + 1 ]);
          $nextType = StructureUtil::getTokenByValue( $tokens[ $i + 2 ]);

          $currToken = StructureUtil::createToken( $currType, $tokens[ $i + 0 ], $group, $this );
          $compToken = StructureUtil::createToken( $compType, $tokens[ $i + 1 ], $group, $this );
          $nextToken = StructureUtil::createToken( $nextType, $tokens[ $i + 2 ], $group, $this );
          
          if( $currToken->type === TokenType::Entity && $nextToken->type === TokenType::String ){
            $tokens[ $i + 0 ] = $currToken;
            $tokens[ $i + 1 ] = $compToken->setRoot( $currToken->root )->setParseCompare( $nextToken );
            $tokens[ $i + 2 ] = $nextToken->setRoot( $currToken->root )->setEntity( $currToken->entity ); 
          } else 
          if( $currToken->type === TokenType::String && $nextToken->type === TokenType::Entity ){
            $tokens[ $i + 0 ] = $nextToken;
            $tokens[ $i + 1 ] = $compToken->setRoot( $nextToken->root )->setInvertCompare()->setParseCompare( $currToken );
            $tokens[ $i + 2 ] = $currToken->setRoot( $nextToken->root )->setEntity( $nextToken->entity );   
          } else {
            $tokens[ $i + 0 ] = $currToken;
            $tokens[ $i + 1 ] = $compToken->setRoot( $currToken->root );
            $tokens[ $i + 2 ] = $nextToken;
          }

          $entityRootRefence = $currToken->root !== $nextToken->root
            ? EntityRoot::No : ( 
                $currToken->root === EntityRoot::Yes && 
                $nextToken->root === EntityRoot::Yes
                  ? EntityRoot::Yes
                  : EntityRoot::No
              );

          $currToken->setRoot( $entityRootRefence );
          $compToken->setRoot( $entityRootRefence );
          $nextToken->setRoot( $entityRootRefence );

          if( $i >= 1 ){
            if( $tokens[ $i - 1 ] instanceof Token ){
              if( $tokens[ $i - 1 ]->type === TokenType::Logical ){
                $tokens[ $i - 1 ]->setRoot( $entityRootRefence );
              }
            }
          }          
          
          $groupEntityRoots[$group][] = $entityRootRefence;
          $i += 2;
        }
      }
    }

    return new Collection( $tokens );
  }

  private function setTokensList(
  ): void {
    $this->tokens = $this->setParseToken(
      StructureUtil::getSourceFile( $this->reflectionFunction )
    );
  }

  public function isValidTokens(
    array $tokens    
  ): bool {
    if( Util::isArray( $tokens ) === false ){
      return false;
    }

    $tokens = new Collection( $tokens );
    $tokens = $tokens->mapper( fn( mixed $token ) => $token instanceof Token );
    $tokens = $tokens->where( fn( bool $token ) => $token === false );
    
    return $tokens->count() === 0;
  }  

  public function isFieldsEquals(
    Token $currToken,
    Token $nextToken     
  ): bool {
    return $currToken->entity->table === $nextToken->entity->table 
        && $currToken->entity->field === $nextToken->entity->field;
  }

  public function isValidGroupToken(
    Token $currToken,
    Token $compToken,
    Token $nextToken  
  ): bool {
    return ( $currToken->type === TokenType::Entity  || $currToken->type === TokenType::String ) 
        && ( $compToken->type === TokenType::Compare || $compToken->type === TokenType::Range ) 
        && ( $nextToken->type === TokenType::Entity  || $nextToken->type === TokenType::String );
  }

  private function setTokensListGroups(
  ): void {
    for( $i = 0; $i < $this->tokens->count(); $i++ ){
      $currToken = $this->tokens->getOneOrFail( $i + 0 );

      if( $currToken instanceof Token && $currToken->type === TokenType::Entity ) {
        for( $j = $i + 3; $j < $this->tokens->count(); $j++ ){
          $nextToken = $this->tokens->getOneOrFail( $j );

          if( $nextToken instanceof Token ){
            if( $currToken->group === $nextToken->group ){
              if( $nextToken->type === TokenType::Entity ){
                $isTokenGroup = $this->isFieldsEquals(
                  $currToken, $nextToken
                );

                if( $isTokenGroup ){
                  $logicalToken = $this->tokens->getOneOrFail( $j - 1 );
                  $isPrevToken = $logicalToken instanceof Token && $logicalToken->type === TokenType::Logical;

                  $this->tokens->spliceIn( 
                    $i + 3, 0, $this->tokens->spliceOut(
                      $isPrevToken ? $j - 1 : $j, $isPrevToken ? 4 : 3
                    )
                  );

                  $i += 2;
                }
              }
            }
          }
        }
      }
    }
  }

  private function getGroupCount(
    int $groupCount = 0
  ): int {
    for( $i = 0; $i < $this->tokens->count(); $i++ ){
      $currToken = $this->tokens->getOneOrFail( $i + 0 );
      if( $currToken instanceof Token && $currToken->type === TokenType::StartGroup ){
        $groupCount++;
      }
    }

    return $groupCount;
  }

  private function getEntityRootsInGroup(
    int $group
  ): Collection {
    $tokens = $this->tokens->where(
      fn( Token $token ) => $token->group === $group && Util::inArray(
        $token->type, [ TokenType::Entity, TokenType::String ]
      )
    );

    return $tokens->mapper( 
      fn( Token $token ) => $token->root
    );
  }

  private function getColumnType(
    TokenEntity $tokenEntity
  ): ColumnType|null {
    $parameter = $this->parameters->getOneOrFail( $tokenEntity->class );
    if( $parameter instanceof Parameter ){
      $column = $parameter->entityStructure->types->getOneOrFail( $tokenEntity->field );
      if( $column instanceof Column ){
        return $column->instance->columnType;
      }
    }

    return null;
  } 

  private function hasBetweenIsValidsTokens(
    mixed $currToken, 
    mixed $currCompToken,
    mixed $currValToken,
    mixed $logicalToken, 
    mixed $nextToken,
    mixed $nextCompToken,
    mixed $nextValToken    
  ): bool {
    return $currToken instanceof Token
        && $currCompToken instanceof Token && Util::inArray(
          CompareType::tryFrom( $currCompToken->value ), [
            CompareType::GreaterEqual, CompareType::LessEqual,
            CompareType::Greater, CompareType::Less
          ]
        )
        && $currValToken instanceof Token
        && $logicalToken instanceof Token && $logicalToken->type === TokenType::Logical
        && $nextToken instanceof Token
        && $nextCompToken instanceof Token && Util::inArray(
          CompareType::tryFrom( $nextCompToken->value ), [
            CompareType::GreaterEqual, CompareType::LessEqual,
            CompareType::Greater, CompareType::Less
          ]
        )
        && $nextValToken instanceof Token
        && $currCompToken->value !== $nextCompToken->value
        && ( CompareType::tryFrom( $currCompToken->value ) === CompareType::GreaterEqual
          && CompareType::tryFrom( $nextCompToken->value ) === CompareType::LessEqual 
          || CompareType::tryFrom( $currCompToken->value ) === CompareType::Greater
          && CompareType::tryFrom( $nextCompToken->value ) === CompareType::Less
        );
  }

  private function hasBetweenIsEqualEntity(
    Token $currToken, 
    Token $nextToken 
  ): bool {
    $isEntitysValids = $currToken->entity->table === $nextToken->entity->table;
    $isFieldsValids = $currToken->entity->field === $nextToken->entity->field;
    
    
    if( $isEntitysValids === false || $isFieldsValids === false ){
      return false;
    }

    $columnType = $this->getColumnType( $currToken->entity ) ;
    if( $columnType instanceof ColumnType ){
      $isColumnTypeValid = Util::inArray( $columnType, [ 
        ColumnType::date, ColumnType::datetime, ColumnType::number, ColumnType::decimal
      ]);

      return $isColumnTypeValid;
    }

    return false;
  }

  private function hasBetween(
    int $index
  ): bool {
    if( $this->tokens->count() - $index >= 7 ){
      [ $currToken, $currCompToken, $currValToken, $logicalToken, 
        $nextToken, $nextCompToken, $nextValToken
      ] = $this->tokens->slice( $index, 7 )->toArray();

      $hasBetweenIsValidsTokens = $this->hasBetweenIsValidsTokens(
        $currToken, $currCompToken, $currValToken, $logicalToken, 
        $nextToken, $nextCompToken, $nextValToken
      );

      if( $hasBetweenIsValidsTokens ){
        return $this->hasBetweenIsEqualEntity( 
          $currToken, $nextToken
        );
      }
    }

    return false;
  }

  private function setTokensListResume(
  ): void {
    for( $i = 0; $i < $this->tokens->count(); $i++ ){
      $currToken = $this->tokens->getOneOrFail( $i + 0 );
      $nextToken = $this->tokens->getOneOrFail( $i + 2 );

      if( $currToken instanceof Token && $nextToken instanceof Token ){
        if( $this->hasBetween( $i )){

          $tokensMoveds = $this->tokens->spliceOut( $i, 7 );
          
          [ $valCurrToken, $valNextToken ] = [
            ...$tokensMoveds->slice( 2, 1 )->toArray(),
            ...$tokensMoveds->slice( 6, 1 )->toArray()
          ];

          $this->tokens->spliceIn( $i, 0, [
            $currToken, 
            new Token( 
              TokenType::Range, 
              LogicalType::Between->value, 
              $currToken->group, 
              $currToken->entity,
              $currToken->root
            ),
            new Token( 
              TokenType::String,
              Util::sprintFormat(
                "%s And %s", [ 
                  $valCurrToken->value,
                  $valNextToken->value
                ]
              ),
              $currToken->group, 
              $currToken->entity, 
              $currToken->root
            )
          ]); 
          
          $i += 6;
        }
      }
    }
  }

  private function parseEnum(
    string $enumValue
  ): string {
    $isNotEnum = preg_match( 
      "#^([\w\\\]+)::(\w+)(?:->(\w+))?$#", $enumValue, $enumPaths 
    ) === 0;

    if( $isNotEnum ){
      return $enumValue;
    }

    if( Util::sizeArray( $enumPaths ) === 4 ){
      [ $class, $case, $property ] = Util::slice( $enumPaths, 1 );
    } else [ $class, $case ] = Util::slice( $enumPaths, 1 );

    if( preg_match( "#^\\\#", $class ) === 0 ){
      $classPath = $this->uses->where(
        fn( UseClass $useClass ) => (
          $useClass->class === $class
        )
      );

      $classPath = Util::sprintFormat( 
        "\\%s\\%s", [
          $classPath->first()->path,
          $classPath->first()->class
        ]
      );
    }

    if( enum_exists($classPath) === false && class_exists($classPath) === false) {
      return $enumValue;
    }
    
    $enumCase = constant( 
      "{$classPath}::{$case}"
    );

    if( $enumCase instanceof UnitEnum ){
      if( isset( $property ) && $property === "name" ){
        return $enumCase->name;
      } else
      if( isset( $property ) && $property === "value" && $enumCase instanceof BackedEnum ){
        return $enumCase->value;
      } else
      if( isset( $property ) === false ){
        return ( $enumCase instanceof BackedEnum ) 
          ? $enumCase->value 
          : $enumCase->name;
      }
    } else {
      return $enumCase;
    }

    return $enumValue;
  }

  private function parseStatic(
    string $staticValue
  ): string {
    if( preg_match( "#^(\{\\$|\\$)|\\}$#", $staticValue ) === 0 ){
      return $staticValue;
    }

    $value = preg_replace( "#^(\{\\$|\\$)|\\}$#", "", $staticValue );
    $value = $this->statics->getOneOrFail( $value );

    return $value !== null ? $value : $staticValue;
  }

  private function getExplodeValue(
    string $value
  ): array {
    $pattern ="#'[^']*'|\"[^\"]*\"|\\{\\$[\\w-]+\\}|\\$?[\\w\\\\-]+(?:->|::)[\\w\\\\-]+|\\d{2}/\\d{2}/\\d{4}|>=|<=|<>|[<>=!]+|\\(|\\)|,|([a-zA-ZÀ-ÿ\d/:$%\\\\]+(?:\s+[a-zA-ZÀ-ÿ\d/:$%\\\\]+)*\s*)#u";

    preg_match_all( $pattern, 
      preg_replace( "#^'|'$#", "", $value ),
        $tokensFromPattern 
    );

    return array_map( 
      fn( string $value ) => (
        preg_replace( [
          "#^'|'$#", "#^\\{\\$#", "#\\}$#" 
        ], [ "", "$", "" ], $value )
      ), array_shift( $tokensFromPattern )
    );
  }  

  private function setTokensListParses(
  ): void {
    for( $i = 0; $i < $this->tokens->count(); $i++ ){
      $currToken = $this->tokens->getOneOrFail( $i + 0 );

      if( $currToken instanceof Token && $currToken->type === TokenType::String ){
        $tokens = $this->getExplodeValue( $currToken->value );

        if( Util::isArray( $tokens ) && Util::sizeArray( $tokens ) !== 0 ){
          $columnType = $this->getColumnType( $currToken->entity );
          
          if( $columnType instanceof ColumnType ){
            for( $j = 0; $j < Util::sizeArray( $tokens ); $j++ ){
              if( StructureUtil::isEnumValue( $tokens[ $j ] )){
                $tokens[ $j ] = $this->parseEnum( $tokens[ $j ] );
              } else if( StructureUtil::isStaticValue( $tokens[ $j ] )){
                $tokens[ $j ] = $this->parseStatic( $tokens[ $j ] );
              }

            }

            $parseEncodeValue = $columnType->Encode( 
              Util::join( "", $tokens )
            );

            if( Util::isArray( $parseEncodeValue )){
              for( $p=0; $p < Util::sizeArray( $parseEncodeValue ); $p++ ){
                $parseEncodeValue[ $p ] = $this->addParam( $parseEncodeValue[ $p ]);
              }

              $currToken->value = Util::sprintFormat(
                "(%s)", [ Util::join( ",", $parseEncodeValue )]
              );
            } else {
              $parseEncodeValue = $this->addParam( $parseEncodeValue );
              $currToken->value = $parseEncodeValue;
            }
          }
        }
      }
    }
  }

  private function addParam(
    string $value,
    string|null $key = null
  ): string {
    $key = ":param_{$this->params->count()}";
    $this->params->add( new Param( $value, $key ), $key );
    return $key;
  }
}