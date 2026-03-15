<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\EntityRoot;
use Websyspro\Entity\Enums\TokenType;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionFunction;

class StructureFromFn
{
  public array $entitys = [];
  public array $parametersList = [];
  public array $parameters = [];
  public array $statics = [];
  public array $joins = [];
  public array $uses = [];
  public array $tokens = [];
  public Collection $params;

  public function __construct(
    public ReflectionFunction $reflectionFunction
  ){
    $this->setEntitysList();
    $this->setParametersList();
    $this->setStaticsList();
    $this->setJoinsList();
    $this->setUsesList();
    $this->setTokensList();

    print_r( $this->tokens );
    // $this->setTokensOrgs(); 
  }

  private function setEntitysList(
  ): void {
    foreach( $this->reflectionFunction->getParameters() as $parameter ){
      if( $parameter->getType() instanceof ReflectionNamedType  ){
        $this->entitys[] = $parameter->getType()->getName();
      }
    }
  }
  
  private function setParametersList(
  ): void {
    foreach( $this->reflectionFunction->getParameters() as $parameter ){
      if( $parameter instanceof ReflectionParameter ){
        if( $parameter->getType() instanceof ReflectionNamedType ){
          $this->parametersList[ $parameter->getName() ] = $parameter->getType()->getName();
          $this->parameters[ 
            $parameter->getType() instanceof ReflectionNamedType 
              ? $parameter->getType()->getName() 
              : null 
          ] = new Parameter( 
            $parameter->getName(), 
            $parameter->getType() instanceof ReflectionNamedType
              ? Util::callUserClassFN( 
                  $parameter->getType()->getName(), "getAttributes", []
                ) 
              : null
          );
        }
      }
    }
  }

  private function setStaticsList(
  ): void {
    $this->statics = $this->reflectionFunction->getStaticVariables();
  }

  private function getEntityFirst(
  ): string {
    return reset( $this->entitys );
  }

  private function setJoinsList(
    Entity|null $entity = null,
    Entity|null $entityReference = null,
     array|null $entityHistory = [],
    AttributeType $attributeType = AttributeType::oneToOne
  ): void {
    if( $entity !== null ){
      if( isset( $this->parameters[ $entity->class ]) === false ){
        return ;
      }
    }

    $joinAllreadyAdded = array_filter(
      $this->joins, fn( HierarchyJoin $hierarchyJoin ) => (
        $hierarchyJoin->entity->class === $entity->class
      )
    );

    if( Util::sizeArray( $joinAllreadyAdded ) === 0 ){
      $parameter = $entity !== null 
        ? $this->parameters[ $entity->class ] ?? null
        : $this->parameters[ $this->getEntityFirst() ];

      if( isset( $parameter ) === true ){
        $entityHistory = array_merge( 
          $entityHistory, [ 
            $attributeType 
          ]
        );  

        $entityRoot = Util::sizeArray(
          Util::where( $entityHistory, 
          fn( AttributeType $attributeType ) => $attributeType === AttributeType::oneToMany )
        ) === 0 ? EntityRoot::Yes : EntityRoot::No;

        if( $entity !== null ){
          $entityFromParameter = $this->parameters[ $parameter->entityStructure->entity->class ];
          $entityParentFromParameter = $this->parameters[ $entityReference->class ];

          if( $attributeType === AttributeType::oneToOne ){
            if( $entityParentFromParameter instanceof Parameter ){
              $foreigns = Util::where( $entityParentFromParameter->entityStructure->foreigns, fn( ForeignKey $foreignKey ) =>  (
                $foreignKey->entityReference->entity->class === $parameter->entityStructure->entity->class && 
                $foreignKey->entity->class === $entityReference->class
              ));
            }
          } else
          if( $attributeType === AttributeType::oneToMany ) {
            if( $entityFromParameter instanceof Parameter ){
              $foreigns = Util::where( $entityFromParameter->entityStructure->foreigns, fn( ForeignKey $foreignKey ) =>  (
                $foreignKey->entityReference->entity->class === $entityReference->class && 
                $foreignKey->entity->class === $parameter->entityStructure->entity->class
              ));
            }
          }
        }

        $this->joins[ $parameter->entityStructure->entity->class ] = new HierarchyJoin(
          $parameter->entityStructure->entity,
          $entityReference,
          $entityHistory,
          $entityRoot,
          $foreigns[ 0 ] ?? null
        );

        array_map( fn( Entity $entity ) => $this->setJoinsList( 
          $entity, $parameter->entityStructure->entity, $entityHistory, AttributeType::oneToOne
          ), $parameter->entityStructure->oneToOne
        );

        array_map( fn(Entity $entity) => $this->setJoinsList( 
          $entity, $parameter->entityStructure->entity, $entityHistory, AttributeType::oneToMany
          ), $parameter->entityStructure->oneToMany
        );     
      }
    }
  }

  private function setUsesList(
  ): void {
    $fileLines = file( $this->reflectionFunction->getFileName());

    $fileUses = Util::where(
      $fileLines, fn(string $text) => preg_match(
        "#^use\s*#", $text
      ) === 1
    );    

    $this->uses = Util::mapper( 
      $fileUses, fn( string $useClass ) => (
        new UseClass( $useClass )
      )
    );
  }

  private function getTokenByValue(
    string $value
  ): TokenType {
    if( Util::match( "#(=|==|===|<>|!=|!==|>=|<=)#", $value )){
      return TokenType::Compare;
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
      return TokenType::Logical;
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
    if( isset( $this->parametersList[ $name ] ) === false){
      return null;
    }

    $paramrter = $this->parameters[ $this->parametersList[ $name ]];
    return $paramrter;
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
  ): array {
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
          $join = $this->joins[ $tokenEntity->class ];
          $joinOrder = $join instanceof HierarchyJoin 
            ? Util::sizeArray( $join->entityHistory ) 
            : 0;
        } else {
          $joinOrder = 0;
        }

        $tokens[ $i ] = new Token(
          $depth === false ? ( $this->hasTypeStaticOrEnum( $type ) ? TokenType::FieldString : $type ) : $type,
          $depth === false && $this->hasTypeStaticOrEnumOrValue( $type ) ? $this->setParseToken( $tokens[ $i ], true, $group, $order ) : [ $tokens[ $i ] ],
          $depth === false ? $tokenEntity : null,
          $group,
          $order++,
          $joinOrder
        );  
      }

      return $tokens;
    }    
    
    return [];
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
    // $this->setTokensOrgsPriority();
    // $this->setTokensOrgsEntitys();
    // $this->setTokensOrgsGroups();
    // $this->setTokensOrgsResume();
    // $this->setTokensOrgsParses();
    // $this->setTokensOrgsCompare();
    // $this->setTokensOrgsJoinsText();
    // $this->setTokensOrgsParsesText();
    // $this->setTokensOrgsParams();
  }

  // public function getToken(
  //   int $tokenIndex    
  // ): Token|null {
  //   $token = $this->tokens->get( $tokenIndex );
  //   return $token instanceof Token ? $token : null;
  // }

  // private function setTokensOrgsPriority(
  // ): void {
  //   for( $i = 0; $i < $this->tokens->count(); $i++ ){
  //     $currToken = $this->tokens->get( $i );
  //     $compToken = $this->tokens->get( $i + 1 );
  //     if( $currToken instanceof Token && $compToken instanceof Token ){
  //       $hasTokenValueOrEnum = Util::inArray( $currToken->takenType, [
  //         TokenType::FieldValue, TokenType::FieldEnum 
  //       ]) && $compToken->takenType === TokenType::Compare;

  //       if( $hasTokenValueOrEnum === true ){
  //         $compToken = $this->getToken( $i + 1 );
  //         $nextToken = $this->getToken( $i + 2 );

  //         if( $nextToken instanceof Token && $nextToken->takenType === TokenType::FieldEntity ){
  //           if( $compToken instanceof Token && $compToken->takenType === TokenType::Compare ){
  //             $compToken->tokenValue = $compToken->tokenValue->mapper(
  //               fn( string $value ) => match( $value ){
  //                 CompareType::GreaterEqual->value => CompareType::LessEqual->value, 
  //                 CompareType::LessEqual->value => CompareType::GreaterEqual->value, 
  //                 CompareType::Greater->value => CompareType::Less->value, 
  //                 CompareType::Less->value => CompareType::Greater->value, 
  //                   default => $value
  //               } 
  //             );
  //           }

  //           $this->tokens->setValue( $i, $nextToken );
  //           $this->tokens->setValue( $i + 1, $compToken );
  //           $this->tokens->setValue( $i + 2, $currToken );

  //           $i += 3;
  //         }
  //       }
  //     }
  //   }
  // }  

  // private function setTokensOrgsEntitys(
  // ): void {
  //   for( $i = 0; $i < $this->tokens->count(); $i++ ){
  //     $fieldEntity = $this->getToken( $i );
  //     if( $fieldEntity instanceof Token ){
  //       if( $fieldEntity->takenType === TokenType::FieldEntity ){
  //         [ $paramName, $fieldName ] = $this->getParamAndField( 
  //           $fieldEntity->tokenValue->first()
  //         );

  //         $parameter = $this->getParameterByName( $paramName );

  //         if( $parameter instanceof Parameter ){
  //           // $fieldEntity->setEntity( $parameter, $fieldName );

  //           $nextToken = $this->getToken( $i + 2 );
  //           if( $nextToken !== null && $nextToken->takenType !== TokenType::FieldEntity ){
  //             // $nextToken->setEntity( $parameter, $fieldName );
  //           }
  //         }
  //       }
  //     }
  //   }
  // }
  
  // private function setTokensOrgsGroups(
  //   int $startParent = 0
  // ): void {
  //   for( $i = 0; $i < $this->tokens->count(); $i++ ){
  //     $currToken = $this->getToken( $i );

  //     if( $currToken instanceof Token && $currToken->takenType === TokenType::FieldEntity ) {
  //       for( $j = $i + 3; $j < $this->tokens->count(); $j++ ){
  //         $nextToken = $this->getToken( $j );

  //         if( $nextToken instanceof Token ){
  //           if( $nextToken->takenType === TokenType::StartParent ){
  //             $startParent++;
  //           }
  //           if( $nextToken->takenType === TokenType::EndParent ){
  //             $startParent--;
  //           }

  //           if( $startParent === 0 ){
  //             if( $nextToken->takenType === TokenType::FieldEntity ){
  //               $hasTokenGroup = $currToken->entity->table === $nextToken->entity->table 
  //                             && $currToken->fieldName === $nextToken->fieldName;

  //               if( $hasTokenGroup ){
  //                 $prevToken = $this->getToken( $j - 1 );

  //                 $hasPrevToken = $prevToken instanceof Token 
  //                               && $prevToken->takenType === TokenType::Logical;

  //                 $this->tokens->spliceIn( 
  //                   $i + 3, 0, $this->tokens->spliceOut(
  //                     $hasPrevToken ? $j - 1 : $j, $hasPrevToken ? 4 : 3
  //                   )
  //                 );

  //                 $i += 2;
  //               }
  //             }
  //           }
  //         }
  //       }

  //       $startParent = 0;
  //     }
  //   }
  // }

  // private function hasBetween(
  //   int $index
  // ): bool {
  //   $tokens = $this->tokens->slice(
  //     $index, 7
  //   );

  //   if( $tokens->exist() && $tokens->count() === 7 ){
  //     [ $field1, $compare1, $value1, $logical, 
  //       $field2, $compare2, $value2
  //     ] = $tokens->all();

  //     $allTokenIsValids = $field1 instanceof Token && $compare1 instanceof Token && $value1 instanceof Token && $logical instanceof Token 
  //                      && $field2 instanceof Token && $compare2 instanceof Token && $value2 instanceof Token;

  //     if( $allTokenIsValids ){
  //       $hasFieldEntitys = $field1->takenType === TokenType::FieldEntity && $field2->takenType === TokenType::FieldEntity
  //                       && $value1->takenType === TokenType::FieldValue && $value2->takenType === TokenType::FieldValue;
        
  //       if( $hasFieldEntitys ){
  //         $hasFieldEntityEqual = $field1->entity->table === $field2->entity->table 
  //                             && $field1->fieldName === $field2->fieldName;

  //         if( $hasFieldEntityEqual ){
  //           $param1 = $this->getParameterByName( $field1->entity->table );
  //           $param2 = $this->getParameterByName( $field2->entity->table );

  //           if( $param1 instanceof Parameter && $param2 instanceof Parameter ){
  //             $fieldType1 = $param1->entityStructure->types->get( $field1->fieldName );
  //             $fieldType2 = $param2->entityStructure->types->get( $field2->fieldName );

  //             if( $fieldType1 instanceof Column && $fieldType2 instanceof Column ){
  //               $hasColumnType1 = Util::inArray( $fieldType1->instance->columnType, [ 
  //                 ColumnType::date, ColumnType::datetime, ColumnType::number
  //               ]);
                
  //               $hasColumnType2 = Util::inArray( $fieldType2->instance->columnType, [ 
  //                 ColumnType::date, ColumnType::datetime, ColumnType::number 
  //               ]);

  //               $hasComparesInverted = $compare1 instanceof Token && $compare1->tokenValue->first() === CompareType::GreaterEqual->value
  //                                   && $compare2 instanceof Token && $compare2->tokenValue->first() === CompareType::LessEqual->value;

  //               $hasLogicalAnd = $logical instanceof Token 
  //                 ? strtolower( $logical->tokenValue->first() ) === strtolower( LogicalType::And->value )
  //                 : false; 
                
  //               return $hasComparesInverted
  //                   && $hasLogicalAnd
  //                   && $hasColumnType1 
  //                   && $hasColumnType2;
  //             }
  //           }            
  //         }
  //       } 
  //     }
  //   }

  //   return false;
  // }

  // private function setTokensOrgsResume(
  // ): void {
  //   for( $i = 0; $i < $this->tokens->count(); $i++ ){
  //     $currToken = $this->getToken( $i );
  //     if( $currToken instanceof Token && $currToken->takenType === TokenType::FieldEntity ){
  //       if( $this->hasBetween( $i )){
  //         $tokensMoveds = $this->tokens->spliceOut( $i, 7 );

  //         [ $value1, $value2 ] = [
  //           ...$tokensMoveds->slice( 2, 1 )->all(),
  //           ...$tokensMoveds->slice( 6, 1 )->all()
  //         ];

  //         if( $value1 instanceof Token && $value2 instanceof Token ){
  //           $this->tokens->spliceIn( $i, 0, [
  //             $currToken, new Token( TokenType::FieldRange, new Collection([ LogicalType::Between->value ])),
  //             $value1, new Token( TokenType::Logical, new Collection([ LogicalType::And->value ])),
  //             $value2      
  //           ]);

  //           $i += 6;
  //         }
  //       }
  //     }
  //   }
  // }

  // private function parseEnum(
  //   string $enumValue
  // ): string {
  //   $isNotEnum = !preg_match( 
  //     "#^([\w\\\]+)::(\w+)(?:->(\w+))?$#", $enumValue, $enumPaths 
  //   );

  //   if( $isNotEnum ){
  //     return $enumValue;
  //   }

  //   if( Util::sizeArray( $enumPaths ) === 4 ){
  //     [ $class, $case, $property ] = Util::slice( $enumPaths, 1 );
  //   } else [ $class, $case ] = Util::slice( $enumPaths, 1 );

  //   if( preg_match( "#^\\\#", $class ) === 0 ){
  //     $classPath = $this->uses->where(
  //       fn( UseClass $useClass ) => (
  //         $useClass->class === $class
  //       )
  //     );

  //     $classPath = Util::sprintFormat( 
  //       "\\%s\\%s", [
  //         $classPath->first()->path,
  //         $classPath->first()->class
  //       ]
  //     );
  //   }

  //   if( enum_exists($classPath) === false && class_exists($classPath) === false) {
  //     return $enumValue;
  //   }
    
  //   $enumCase = constant( 
  //     "{$classPath}::{$case}"
  //   );

  //   if( $enumCase instanceof UnitEnum ){
  //     if( isset( $property ) && $property === "name" ){
  //       return $enumCase->name;
  //     } else
  //     if( isset( $property ) && $property === "value" && $enumCase instanceof BackedEnum ){
  //       return $enumCase->value;
  //     } else
  //     if( isset( $property ) === false ){
  //       return ( $enumCase instanceof BackedEnum ) 
  //         ? $enumCase->value 
  //         : $enumCase->name;
  //     }
  //   } else {
  //     return $enumCase;
  //   }

  //   return $enumValue;
  // }

  // private function parseStatic(
  //   string $staticValue
  // ): string {
  //   if( preg_match( "#^(\{\\$|\\$)|\\}$#", $staticValue ) === 0 ){
  //     return $staticValue;
  //   }

  //   $value = preg_replace( "#^(\{\\$|\\$)|\\}$#", "", $staticValue );
  //   $value = $this->statics->get( $value );
  //   return $value !== null 
  //     ? $value 
  //     : $staticValue;
  // }

  // private function setTokensOrgsParses(
  // ): void {
  //   for( $i = 0; $i < $this->tokens->count(); $i++ ){
  //     $currToken = $this->getToken( $i );
  //     if( $currToken instanceof Token && $currToken->takenType === TokenType::FieldValue ){
  //       $currToken->tokenValue = $currToken->tokenValue->mapper(
  //         function( Token $token ){
  //           if( $token->takenType === TokenType::FieldEnum ){
  //             $token->tokenValue = new Collection([
  //               $this->parseEnum( $token->tokenValue->first())
  //             ]);
  //           } else if( $token->takenType === TokenType::FieldStatic ){
  //             $token->tokenValue = new Collection([
  //               $this->parseStatic( $token->tokenValue->first())
  //             ]);
  //           }

  //           return $token;
  //         }
  //       );
  //     }
  //   }
  // }

  // private function setTokensOrgsCompare(
  // ): void {
  //   for( $i = 0; $i < $this->tokens->count(); $i++ ){
  //     $currToken = $this->getToken( $i );
  //     $nextToken = $this->getToken( $i + 1 );

  //     $hasTokensValid = $currToken instanceof Token && $currToken->takenType === TokenType::Compare
  //                    && $nextToken instanceof Token && $nextToken->takenType === TokenType::FieldValue;

  //     if( $hasTokensValid ){
  //       $compareValue = $currToken->tokenValue->first();
  //       $value = $nextToken->tokenValue->mapper( 
  //         fn( Token $token ) => $token->tokenValue->first()
  //       )->joinNotSpace();

  //       $value = preg_replace( "#\\\%#", "", $value );

  //       $hasLike = preg_match( "#%#", $value );
  //       $hasList = preg_match( "#(^\(.*\)$)#", $value );
  //       $hasNull = strtoupper( $value ) === "NULL";

  //       if( $compareValue === CompareType::Equals->value && $hasLike ){
  //         $currToken->tokenValue = new Collection([ CompareType::Like->value ]);
  //       } else if( $compareValue === CompareType::NotEqual->value && $hasLike ){
  //         $currToken->tokenValue = new Collection([ CompareType::NotLike->value  ]);
  //       } else if( $compareValue === CompareType::Equals->value && $hasList ){
  //         $currToken->tokenValue = new Collection([ CompareType::In->value  ]);
  //         $currToken->takenType = TokenType::FieldRange;
  //       } else if( $compareValue === CompareType::NotEqual->value && $hasList ){
  //         $currToken->tokenValue = new Collection([ CompareType::NotIn->value  ]);
  //         $currToken->takenType = TokenType::FieldRange;
  //       } else if( $compareValue === CompareType::Equals->value && $hasNull ){
  //         $currToken->tokenValue = new Collection([ CompareType::Is->value  ]);
  //       } else if( $compareValue === CompareType::NotEqual->value && $hasNull ){
  //         $currToken->tokenValue = new Collection([ CompareType::Not->value  ]);
  //       }

  //       $this->tokens->setValue( $i, $currToken );
  //     }
  //   }
  // }

  // private function setTokensOrgsJoinsText(
  // ): void {
  //   $this->tokens = $this->tokens->mapper(
  //     function( Token $token ){
  //       if( $token->takenType === TokenType::FieldValue ){
  //         $token->tokenValue = $token->tokenValue->mapper( 
  //           fn( mixed $subToken ) => $subToken->tokenValue->joinNotSpace()
  //         );

  //         $token->tokenValue = new Collection([
  //           $token->tokenValue->joinNotSpace()
  //         ]);
  //       }

  //       return $token;
  //     }
  //   );
  // }

  // private function setTokensOrgsParsesText(
  // ): void {
  //   $this->tokens = $this->tokens->mapper(
  //     function( Token $token ){
  //       if( $token->takenType === TokenType::FieldValue ){
  //         $parameter = $this->getParameterByName( $token->entity->table );
  //         if( $parameter instanceof Parameter ){
  //           $column = $parameter->entityStructure->types->get( $token->fieldName );
  //           if( $column instanceof Column ){
  //             $token->tokenValue = $token->tokenValue->mapper( 
  //               fn( string $value ) => (
  //                 $column->instance->columnType->Encode( $value )
  //               )
  //             );
  //           }
  //         }

  //       } else if( $token->takenType === TokenType::FieldEntity ){
  //         $token->tokenValue->setValue( 0, Util::sprintFormat( "%s.%s", [
  //           $token->entity->table, $token->fieldName
  //         ]));
  //       }

  //       return $token;
  //     }
  //   );
  // }

  // private function setTokensOrgsParams(
  // ): void {
  //   $this->tokens->mapper(
  //     function( Token $token ){
  //       if( $token->takenType === TokenType::FieldValue ){
  //         if( isset( $this->params ) === false ){
  //           $this->params = new Collection();
  //         }

  //         $token->tokenValue = $token->tokenValue->mapper( 
  //           function( string|array $value ){
  //             $key = "$[Param_{$this->params->count()}]";

  //             $this->params->add( 
  //               new Param( $value, $key )
  //             );

  //             return $key;
  //           }
  //         );
  //       }
  //     }
  //   );
  // }
}