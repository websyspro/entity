<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\CompareType;
use Websyspro\Entity\Enums\LogicalType;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Entity\Enums\EntityRoot;
use Websyspro\Entity\Enums\TokenType;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionFunction;
use BackedEnum;
use UnitEnum;

class StructureFromFn
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
    $this->setTokensOrgs();
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
              
            $entityRoot = Util::sizeArray(
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
                  $entityReference, $entityHistory, $entityRoot, $foreigns->first()
                ), $parameter->entityStructure->entity->class
              );              
            } else {
              $this->joins->add(
                new HierarchyJoin(
                  $parameter->entityStructure->entity,
                  $entityReference, $entityHistory, $entityRoot, null
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

  private function getTokenByValue(
    string $value
  ): TokenType {
    if( Util::match( "#(=|==|===|<>|!=|!==|>=|<=)#", $value )){
      return TokenType::FieldCompare;
    } else
    if( Util::match( "#^\\\$.*->.*$#", $value )){
      return TokenType::FieldEntity;
    } else
    if( Util::match( "#\\\$(\{[a-zA-Z_][a-zA-Z0-9_]*\}|[a-zA-Z_][a-zA-Z0-9_]*)#", $value )){
      return TokenType::FieldStatic;
    } else
    if( Util::match( "#^(\\\"|').*(\\\"|')$#", $value )){
      return TokenType::FieldString;
    } else
    if( Util::match( "#(&&|\|\||And|Or)#", $value )){
      return TokenType::FieldLogical;
    }else
    if( Util::match( "#^[a-zA-Z]{1}.*::.*(->(?:name|value))?$#", $value )){
      return TokenType::FieldEnum;
    } else
    if(Util::match( "#^,$#", $value )){
      return TokenType::FieldIgnore;
    } else
    if( Util::match( "#^\($#", $value )){
      return TokenType::StartGroup;
    } else 
    if( Util::match( "#^\)$#", $value )){
      return TokenType::EndGroup;
    } else return TokenType::FieldString;
  }

  private function hasTypeStaticOrEnum(
    TokenType $type
  ): bool {
    return Util::inArray( 
      $type, [ TokenType::FieldStatic, TokenType::FieldEnum ]
    );
  }

  private function hasTypeStaticOrEnumOrValue(
    TokenType $type
  ): bool {
    return Util::inArray( 
      $type, [ TokenType::FieldStatic, TokenType::FieldEnum, TokenType::FieldString ]
    );
  }

  private function getParameterByName(
    string $name
  ): Parameter|null {
    $entity = $this->parametersList->getOneOrFail( $name );
    if( $entity === null ){
      return null;
    }

    $parameter = $this->parameters->getOneOrFail( $entity );
    if( $parameter === null ){
      return null;
    }

    return $parameter;
  }  

  private function getTokenEntityByValue(
    string $value    
  ): TokenEntity|null {
    if( str_contains( $value, "->" )){
      [ $parameter, $field ] = explode( 
        "->", trim( $value, "$" ), 2
      );

      $parameter = $this->getParameterByName( $parameter );
      return new TokenEntity(
        $parameter->entityStructure->entity, $field
      );
    }

    return null;
  }  

  private function setParseToken(
    string $sourceString,
    bool $depth = false,
    int $group = 0,
    int $order = 0
  ): Collection {
    $pattern = $depth === true
      ? $pattern = "#'[^']*'|\"[^\"]*\"|\\{\\$[\\w-]+\\}|\\$?[\\w\\\\-]+(?:->|::)[\\w\\\\-]+|\\d{2}/\\d{2}/\\d{4}|>=|<=|<>|[<>=!]+|\\(|\\)|,|([a-zA-ZÀ-ÿ\d/:$%\\\\]+(?:\s+[a-zA-ZÀ-ÿ\d/:$%\\\\]+)*\s*)#u"
      : "#'[^']*'|\"[^\"]*\"|\\S+#";

    preg_match_all(
      $pattern,
      preg_replace( 
        "#^'|'$#", 
        "", $sourceString
      ), $tokensListMatchAll
    );

    if( Util::sizeArray( $tokensListMatchAll ) !== 0 ){
      [ $tokens ] = $tokensListMatchAll;

      for( $i = 0; $i < Util::sizeArray( $tokens ); $i++ ){
        $type = $this->getTokenByValue( $tokens[ $i ]);

        if( $depth === false ){
          if( $type === TokenType::StartGroup ){
            $group++; $order = 1;
          } else if( $type === TokenType::EndGroup ){
            $group--; $order = 1;
          }
        }

        $tokenEntity = $type === TokenType::FieldEntity 
          ? $this->getTokenEntityByValue( $tokens[ $i ] ) 
          : null;

        if( $tokenEntity !== null ){
          $join = $this->joins->getOneOrFail( $tokenEntity->class );
          $entityRoot = $join instanceof HierarchyJoin ? $join->entityRoot : null;
          $weight = $join instanceof HierarchyJoin ? Util::sizeArray( $join->entityHistory ) : 0;
        } else {
          $entityRoot = null;
          $weight = $depth === false && Util::inArray( 
            $type, [ TokenType::FieldEnum, TokenType::FieldStatic, TokenType::FieldString ]
          ) ? 999 : 0;
        }

        $tokens[ $i ] = new Token(
          $depth === false ? ( $this->hasTypeStaticOrEnum( $type ) ? TokenType::FieldString : $type ) : $type,
          $depth === false && $this->hasTypeStaticOrEnumOrValue( $type ) ? $this->setParseToken( $tokens[ $i ], true, $group, $order ) : new Collection([ $tokens[ $i ]]),
          $depth === false ? $tokenEntity : null, 
          $entityRoot,
          $group,
          $order++,
          $weight 
        );
      }      

      return new Collection( $tokens );
    }    
    
    return new Collection();
  }

  private function setTokensList(
  ): void {
    $sourceFileArrowFN = new Collection(
      file( $this->reflectionFunction->getFileName())
    );

    $sourceString = preg_replace(
      [
        "#\r#",
        "#\n\s*#",
        "#^.*\\{.*return\s*#",
        "#\s*;\s*\\}\s*#",
        "#^.*(fn|function)\s*\(#",
        "#\s*\);\s*$#",
        "#^.*?\)\s*=>\s*#s",
        "#\\[\s*#s",
        "#\s*\\]#s",
        "#,\s*#s",
        "#\"#s",
        "#&&#",
        "#\|\|#",
        "#(!==|!=)#",
        "#(===|==|=)#",
        "#true#",
        "#false#"
      ], 
      [
        "",     // Remove carriage return
        " ",    // Remove quebras de linha
        "",     // Remove abertura de função
        "",     // Remove fechamento de função
        "fn(",  // Normaliza declaração de função
        "",     // Remove fechamento de parênteses
        "",     // Remove arrow function
        "(",    // Converte colchetes em parênteses
        ")",    // Converte colchetes em parênteses
        ",",    // Normaliza vírgulas
        "'",    // Converte aspas duplas em simples
        "And",  // Converte && em And
        "Or",   // Converte || em Or
        "<>",   // Normaliza operador diferente
        "=",    // Normaliza operador igual
        "1",    // Converte true em 1
        "0"     // Converte false em 0
      ],  
      $sourceFileArrowFN->slice(
        $this->reflectionFunction->getStartLine() - 1,
        $this->reflectionFunction->getEndLine() - $this->reflectionFunction->getStartLine() + 1
      )->toString()
    );

    $this->tokens = $this->setParseToken( 
      $sourceString, false
    );
  }

  private function setTokensOrgs(
  ): void {
    $this->setTokensOrgsPriority();
    $this->setTokensOrgsEntitys();
    $this->setTokensOrgsGroups();
    $this->setTokensOrgsResume();
    $this->setTokensOrgsRoots();
    $this->setTokensOrgsParses();
    $this->setTokensOrgsCompare();
    $this->setTokensOrgsJoinsText();
    $this->setTokensOrgsParsesText();
    // $this->setTokensOrgsParams();

    print_r( $this->tokens );

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
    return ( $currToken->type === TokenType::FieldEntity  || $currToken->type === TokenType::FieldString ) 
        && ( $compToken->type === TokenType::FieldCompare || $compToken->type === TokenType::FieldRange ) 
        && ( $nextToken->type === TokenType::FieldEntity  || $nextToken->type === TokenType::FieldString );
  }

  private function setTokensOrgsPriority(
  ): void {
    for( $i = 0; $i < $this->tokens->count(); $i++ ){
      $currToken = $this->tokens->getOneOrFail( $i + 0 );
      $compToken = $this->tokens->getOneOrFail( $i + 1 );
      $nextToken = $this->tokens->getOneOrFail( $i + 2 );

      $isTokensInstance = $this->isValidTokens([
        $currToken, $compToken, $nextToken
      ]); 

      if( $isTokensInstance ){
        $isFieldEntityOrStringAndCompared = $this->isValidGroupToken(
          $currToken, $compToken, $nextToken
        );

        if( $isFieldEntityOrStringAndCompared ){
          if( $currToken->weight > $nextToken->weight ){ 
            if( $compToken->value instanceof Collection ){
              $compToken->value = $compToken->value->mapper(
                fn( mixed $value ) => match( $value ){
                  CompareType::GreaterEqual->value => CompareType::LessEqual->value, 
                  CompareType::LessEqual->value => CompareType::GreaterEqual->value, 
                  CompareType::Greater->value => CompareType::Less->value, 
                  CompareType::Less->value => CompareType::Greater->value, 
                    default => $value
                } 
              );
            } 

            $this->tokens->setValue( $i + 0, $nextToken );
            $this->tokens->setValue( $i + 1, $compToken );
            $this->tokens->setValue( $i + 2, $currToken );
          }
        }
      }
    }
  }  

  private function setTokensOrgsEntitys(
  ): void {
    for( $i = 0; $i < $this->tokens->count(); $i++ ){
      $logiToken = $this->tokens->getOneOrFail( $i - 1 );
      $currToken = $this->tokens->getOneOrFail( $i + 0 );
      $compToken = $this->tokens->getOneOrFail( $i + 1 );
      $nextToken = $this->tokens->getOneOrFail( $i + 2 );

      $isTokensInstance = $this->isValidTokens([
        $currToken, $compToken, $nextToken
      ]);

      if( $isTokensInstance ){
        $isFieldEntityOrStringAndCompared = $this->isValidGroupToken(
          $currToken, $compToken, $nextToken
        ); 
        
        if( $isFieldEntityOrStringAndCompared ){
          if( Util::inArray( $currToken->type, [ TokenType::FieldEntity, TokenType::FieldString ])){
            if( $nextToken->entity === null ){
              $nextToken->entity = $currToken->entity;
              $compToken->entity = $currToken->entity;
              $nextToken->entityRoot = $currToken->entityRoot;
              $compToken->entityRoot = $currToken->entityRoot;
            }

            if( $logiToken instanceof Token && $logiToken->type === TokenType::FieldLogical ){
              $logiToken->entity = $currToken->entity;
              $logiToken->entityRoot = $currToken->entityRoot;
            }            
          }
        }
      }
    }
  }  

  private function setTokensOrgsGroups(
  ): void {
    for( $i = 0; $i < $this->tokens->count(); $i++ ){
      $currToken = $this->tokens->getOneOrFail( $i + 0 );

      if( $currToken instanceof Token && $currToken->type === TokenType::FieldEntity ) {
        for( $j = $i + 3; $j < $this->tokens->count(); $j++ ){
          $nextToken = $this->tokens->getOneOrFail( $j );

          if( $nextToken instanceof Token ){
            if( $currToken->group === $nextToken->group ){
              if( $nextToken->type === TokenType::FieldEntity ){
                $isTokenGroup = $this->isFieldsEquals(
                  $currToken, $nextToken
                );

                if( $isTokenGroup ){
                  $logicalToken = $this->tokens->getOneOrFail( $j - 1 );
                  $isPrevToken = $logicalToken instanceof Token && $logicalToken->type === TokenType::FieldLogical;

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
        $token->type, [ TokenType::FieldEntity, TokenType::FieldString ]
      )
    );

    return $tokens->mapper( 
      fn( Token $token ) => $token->entityRoot
    );
  }

  private function setTokensOrgsRoots(
  ): void {
    for( $group = 1; $group <= $this->getGroupCount(); $group++ ){
      $groupWheres = $this->getEntityRootsInGroup( $group );

      $isGroupWheresIsRootEntityYes = $groupWheres->where(
        fn( EntityRoot $entityRoot ) => $entityRoot === EntityRoot::Yes
      );

      for( $i = 0; $i < $this->tokens->count(); $i++ ){
        $groupToken = $this->tokens->getOneOrFail( $i + 0 );

        $isStartOrEndGroup = $groupToken instanceof Token && Util::inArray(
          $groupToken->type, [ TokenType::StartGroup, TokenType::EndGroup ]
        );

        if( $isStartOrEndGroup && $groupToken->group === $group ){
          $groupToken->entityRoot = $isGroupWheresIsRootEntityYes->exist() 
            ? EntityRoot::Yes : EntityRoot::No;
        }
      }
    }
  }

  private function isValidBetWeenToken(
    Token $currToken,
    Token $valCurrToken,
    Token $nextToken,
    Token $valNextToken 
  ): bool {
    return $currToken->type === TokenType::FieldEntity 
        && $nextToken->type === TokenType::FieldEntity
        && $valCurrToken->type === TokenType::FieldString 
        && $valNextToken->type === TokenType::FieldString;
  }

  private function hasBetween(
    int $index
  ): bool {
    if( $this->tokens->count() - $index >= 7 ){
      [ $currToken, $compCurrToken, $valCurrToken, $logicalToken, 
        $nextToken, $compNextToken, $valNextToken
      ] = $this->tokens->slice( $index, 7 )->toArray();
      
      $isAllTokensIsValids = $this->isValidTokens([
        $currToken, $compCurrToken, $valCurrToken, $logicalToken,
        $nextToken, $compNextToken, $valNextToken
      ]);

      if( $isAllTokensIsValids ){
        $isValidBetWeenToken = $this->isValidBetWeenToken(
          $currToken, $valCurrToken, $nextToken, $valNextToken
        );

        if( $isValidBetWeenToken ){
          $isFieldsEquals = $this->isFieldsEquals(
            $currToken, $nextToken
          );
          
          if( $isFieldsEquals ){
            $parameter = $this->parameters->getOneOrFail( 
              $currToken->entity->class
            );

            if( $parameter instanceof Parameter ){
              $field = $parameter->entityStructure->types->getOneOrFail( $currToken->entity->field );

              if( $field instanceof Column ){
                $isColumnType = Util::inArray( $field->instance->columnType, [ 
                  ColumnType::date, ColumnType::datetime, ColumnType::number
                ]);

                [ $compCurrTokenValue, $compNextTokenValue, $logicalTokenValue ] = [
                  ...$compCurrToken->value->toArray(), ...$compNextToken->value->toArray(), ...$logicalToken->value->toArray()
                ];

                $isComparesInverted = $compCurrTokenValue === CompareType::GreaterEqual->value 
                                   && $compNextTokenValue === CompareType::LessEqual->value;

                $isLogicalAnd = strtolower( $logicalTokenValue ) 
                            === strtolower( LogicalType::And->value );

                return $isColumnType && $isComparesInverted && $isLogicalAnd;
              }
            }
          }
        }
      }
    }

    return false;
  }

  private function setTokensOrgsResume(
  ): void {
    for( $i = 0; $i < $this->tokens->count(); $i++ ){
      $currToken = $this->tokens->getOneOrFail( $i + 0 );
      $nextToken = $this->tokens->getOneOrFail( $i + 2 );

      if( $currToken instanceof Token && $nextToken instanceof Token ){
        if( $this->hasBetween( $i ) === true ){
          $tokensMoveds = $this->tokens->spliceOut( $i, 7 );
          
          [ $valCurrToken, $valNextToken ] = [
            ...$tokensMoveds->slice( 2, 1 )->toArray(),
            ...$tokensMoveds->slice( 6, 1 )->toArray()
          ];

          if( $valCurrToken instanceof Token && $valCurrToken instanceof Token ){
            $nextToken->value = new Collection([
              $valCurrToken->value->first(), new Token( 
                TokenType::FieldLogical, new Collection([ 
                  LogicalType::And->value
                ])
              ), $valNextToken->value->first()
            ]);

            $this->tokens->spliceIn( $i, 0, [
              $currToken, new Token(
                TokenType::FieldRange, new Collection([ 
                  LogicalType::Between->value
                ])
              ), $nextToken     
            ]);

            $i += 6;          
          }
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

  private function setTokensOrgsParses(
  ): void {
    for( $i = 0; $i < $this->tokens->count(); $i++ ){
      $currToken = $this->tokens->getOneOrFail( $i + 0 );

      if( $currToken instanceof Token && $currToken->type === TokenType::FieldString ){
        $currToken->value = $currToken->value->mapper(
          function( Token $token ){
            [ $tokenValue ] = $token->value->toArray();

            if( $token->type === TokenType::FieldEnum ){
              $token->value = new Collection([
                $this->parseEnum( $tokenValue )
              ]);
            } else if( $token->type === TokenType::FieldStatic ){
              $token->value = new Collection([
                $this->parseStatic( $tokenValue )
              ]);
            }

            return $token;
          }
        );
      }
    }
  }

  private function setTokensOrgsCompare(
  ): void {
    for( $i = 0; $i < $this->tokens->count(); $i++ ){
      $currToken = $this->tokens->getOneOrFail( $i + 0 );
      $nextToken = $this->tokens->getOneOrFail( $i + 1 );

      $isTokensValid = $currToken instanceof Token && $currToken->type === TokenType::FieldCompare
                    && $nextToken instanceof Token && $nextToken->type === TokenType::FieldString;

      if( $isTokensValid ){
        $compareValue = $currToken->value->first();
        $value = $nextToken->value->mapper( 
          fn( Token $token ) => $token->value->first()
        )->joinNotSpace();

        $value = preg_replace( "#\\\%#", "", $value );

        $hasLike = preg_match( "#%#", $value );
        $hasList = preg_match( "#(^\(.*\)$)#", $value );
        $hasNull = strtoupper( $value ) === "NULL";

        if( $compareValue === CompareType::Equals->value && $hasLike ){
          $currToken->value = new Collection([ CompareType::Like->value ]);
        } else if( $compareValue === CompareType::NotEqual->value && $hasLike ){
          $currToken->value = new Collection([ CompareType::NotLike->value  ]);
        } else if( $compareValue === CompareType::Equals->value && $hasList ){
          $currToken->value = new Collection([ CompareType::In->value  ]);
          $currToken->type = TokenType::FieldRange;
        } else if( $compareValue === CompareType::NotEqual->value && $hasList ){
          $currToken->value = new Collection([ CompareType::NotIn->value  ]);
          $currToken->type = TokenType::FieldRange;
        } else if( $compareValue === CompareType::Equals->value && $hasNull ){
          $currToken->value = new Collection([ CompareType::Is->value  ]);
        } else if( $compareValue === CompareType::NotEqual->value && $hasNull ){
          $currToken->value = new Collection([ CompareType::Not->value  ]);
        }

        $this->tokens->setValue( $i, $currToken );
      }
    }
  }

  private function setTokensOrgsJoinsText(
  ): void {
    for( $i = 0; $i < $this->tokens->count(); $i++ ){
      $currToken = $this->tokens->getOneOrFail( $i + 0 );
      $compToken = $this->tokens->getOneOrFail( $i - 1 );

      if( $compToken instanceof Token && $compToken->type !== TokenType::FieldRange ){
        if( $currToken instanceof Token && $currToken->type === TokenType::FieldString ){
          $currToken->value = $currToken->value->mapper( 
            fn( mixed $subToken ) => $subToken->value->joinNotSpace()
          );
          
          $currToken->value = new Collection([
            new Token(
              TokenType::FieldString, 
                new Collection([ 
                  $currToken->value->joinNotSpace() 
                ]),
              $currToken->entity
            )
          ]);
        }
      }
    }
  }

  private function setTokensOrgsParsesText(
  ): void {
    for( $i = 0; $i < $this->tokens->count(); $i++ ){
      $currToken = $this->tokens->getOneOrFail( $i + 1 );

      if( $currToken instanceof Token && $currToken->type === TokenType::FieldString ){
        if( $currToken->value->exist() === true ){
          $parameter = $this->parameters->getOneOrFail( 
            $currToken->entity->class
          );

          if( $parameter instanceof Parameter ){
            $field = $parameter->entityStructure->types->getOneOrFail(
              $currToken->entity->field
            );

            $currToken->value = $currToken->value->mapper(
              function( Token $token ) use ( $field ){
                $isTokenChange = Util::inArray( 
                  $token->type, [
                    TokenType::FieldEnum,
                    TokenType::FieldStatic,
                    TokenType::FieldString 
                  ]
                );

                if( $isTokenChange && $field instanceof Column ){
                  [ $tokenValue ] = $token->value->toArray();
                  $token->value = new Collection([
                    $field->instance->columnType->Encode( $tokenValue )
                  ]);
                }

                return $token;
              }
            );
          }
        }
      }
    }
  }

  private function addParam(
    string $value,
    string|null $key = null
  ): Collection {
    $key = "$[Param_{$this->params->count()}]";
    $this->params->add( new Param( $value, $key ), $key );
    return new Collection([ $key ]);
  }

  private function setTokensOrgsParams(
  ): void {
    for( $i = 0; $i < $this->tokens->count(); $i++ ){
      $currToken = $this->tokens->getOneOrFail( $i + 1 );

      if( $currToken instanceof Token && $currToken->type === TokenType::FieldString ){
        $currToken->value = $currToken->value->mapper( 
          function( Token $token ){
            $isTokenChange = Util::inArray( 
              $token->type, [
                TokenType::FieldEnum,
                TokenType::FieldStatic,
                TokenType::FieldString 
              ]
            );

            if( $isTokenChange ){
              $token->value = $this->addParam(
                $token->value->first()
              );
            }

            return $token;
          }
        );
      }
    }
  }
}