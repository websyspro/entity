<?php

namespace Websyspro\Entity\Shareds;

use BackedEnum;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\EntityRoot;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionFunction;
use UnitEnum;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Entity\Enums\LogicalType;
use Websyspro\Entity\Enums\TokenType;
use Websyspro\Entity\Enums\CompareType;

class StructureFromFn
{
  public Collection $parameters;
  public Collection $statics;
  public Collection $joins;
  public Collection $uses;
  public Collection $tokens;

  public function __construct(
    public ReflectionFunction $reflectionFunction
  ){
    $this->setParametersList();
    $this->setStaticsList();
    $this->setJoinsList();
    $this->setUsesList();
    $this->setTokensList();
    $this->setTokensOrgs(); 
  }
  
  private function setParametersList(
  ): void {
    foreach( $this->reflectionFunction->getParameters() as $parameter ){
      if( $parameter instanceof ReflectionParameter ){
        if( isset( $this->parameters ) === false ){
          $this->parameters = new Collection();
        }

        $this->parameters->add(
          new Parameter( 
            $parameter->getName(), 
            $parameter->getType() instanceof ReflectionNamedType
              ? Util::callUserClassFN( 
                $parameter->getType()->getName(), "getAttributes", []
              ) : null
          ), $parameter->getType()
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

  private function setJoinsListByItem(
    Entity|null $entity = null,
    Entity|null $entityReference = null,
    array $entityHistory = [],
    AttributeType $attributeType = AttributeType::oneToOne
  ): void {
    $joinAlreadyAdded = $this->joins->where(
      fn( HierarchyJoin $hierarchyJoin ) => (
        $hierarchyJoin->entity->class === $entity->class
      )
    );

    if( $joinAlreadyAdded->exist() === false ){
      $parameter = $entity !== null 
        ? $this->parameters->get( $entity->class ) 
        : $this->parameters->first();

      if( $parameter !== null ){
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
          $entityFromParameter = $this->parameters->get( $parameter->entityStructure->entity->class );
          $entityParentFromParameter = $this->parameters->get( $entityReference->class );

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
        }

        $this->joins->add(
          new HierarchyJoin(
            $parameter->entityStructure->entity,
            $entityReference,
            $entityHistory,
            $entityRoot,
            isset( $foreigns ) ? $foreigns->first() : null
          ), $parameter->entityStructure->entity->class
        );

        $parameter->entityStructure->oneToOne->mapper(
          fn(Entity $entity) => $this->setJoinsListByItem( 
            $entity, 
            $parameter->entityStructure->entity, 
            $entityHistory, 
            AttributeType::oneToOne
          )
        );    

        $parameter->entityStructure->oneToMany->mapper(
          fn(Entity $entity) => $this->setJoinsListByItem( 
            $entity, 
            $parameter->entityStructure->entity, 
            $entityHistory,
            AttributeType::oneToMany
          )
        );        
      }
    }
  }

  private function setJoinsList(
  ): void {
    if( isset( $this->joins ) === false ){
      $this->joins = new Collection();
    }

    $this->setJoinsListByItem();
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
      return TokenType::Compare;
    } else
    if( Util::match( "#^\\\$.*->.*$#", $value )){
      return TokenType::FieldEntity;
    } else
    if( Util::match( "#\\\$(\{[a-zA-Z_][a-zA-Z0-9_]*\}|[a-zA-Z_][a-zA-Z0-9_]*)#", $value )){
      return TokenType::FieldStatic;
    } else
    if( Util::match( "#^(\\\"|').*(\\\"|')$#", $value )){
      return TokenType::FieldValue;
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
      return TokenType::StartParent;
    } else 
    if( Util::match( "#^\)$#", $value )){
      return TokenType::EndParent;
    } else return TokenType::FieldValue;
  }

  private function setParseToken(
    string $sourceString,
    bool $depth = false
  ): Collection {
    $pattern = $depth === true
      ? $pattern = "#'[^']*'|\"[^\"]*\"|\\{\\$[\\w-]+\\}|\\$?[\\w\\\\-]+(?:->|::)[\\w\\\\-]+|\\d{2}/\\d{2}/\\d{4}|>=|<=|<>|[<>=!]+|\\(|\\)|,|([a-zA-ZÀ-ÿ\d/:$]+(?:\s+[a-zA-ZÀ-ÿ\d/:$]+)*\s*)#u"
      : "#'[^']*'|\"[^\"]*\"|\\S+#";

    preg_match_all(
      $pattern,
      preg_replace( 
        "#^'|'$#", 
        "", $sourceString
      ), $tokensListMatchAll
    );

    if( Util::sizeArray( $tokensListMatchAll ) !== 0 ){
      [ $tokensList ] = $tokensListMatchAll;
      return new Collection(
        Util::mapper( $tokensList, function( string $tokenValue ) use ( $depth ) {
          $tokenType = $this->getTokenByValue( $tokenValue );

          return new Token( 
            $tokenType === TokenType::FieldStatic ? TokenType::FieldValue : $tokenType, 
            $depth === false && Util::inArray( $tokenType, [ TokenType::FieldValue, TokenType::FieldStatic ]) 
              ? $this->setParseToken( $tokenValue, true ) : new Collection([ $tokenValue ]) 
          );
        })
      );
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
    $this->setTokensOrgsCompares();
    $this->setTokensOrgsParses();
  }

  private function getToken(
    int $tokenIndex    
  ): Token|null {
    $token = $this->tokens->get( $tokenIndex );
    return $token instanceof Token ? $token : null;
  }

  private function getParameterByName(
    string $paramName
  ): Parameter|null {
    return $this->parameters->where(
      fn( Parameter $parameter ) => (
        strtolower( $parameter->name ) === strtolower( $paramName )
      )
    )->first();
  }

  private function getParamAndField(
    string $tokenValue    
  ): array {
    if( str_contains( $tokenValue, "->" )){
      [ $param, $field ] = explode( 
        "->", $tokenValue, 2
      );

      return [ substr($param, 1), $field  ];
    }

    return [ null, null ];
  }

  private function setTokensOrgsPriority(
  ): void {
    for( $i = 0; $i < $this->tokens->count(); $i++ ){
      $currToken = $this->tokens->get( $i );
      $compToken = $this->tokens->get( $i + 1 );
      if( $currToken instanceof Token && $compToken instanceof Token ){
        $hasTokenValueOrEnum = Util::inArray( $currToken->takenType, [
          TokenType::FieldValue, TokenType::FieldEnum 
        ]) && $compToken->takenType === TokenType::Compare;

        if( $hasTokenValueOrEnum === true ){
          $compToken = $this->getToken( $i + 1 );
          $nextToken = $this->getToken( $i + 2 );

          if( $nextToken instanceof Token && $nextToken->takenType === TokenType::FieldEntity ){
            if( $compToken instanceof Token && $compToken->takenType === TokenType::Compare ){
              $compToken->tokenValue = $compToken->tokenValue->mapper(
                fn( string $value ) => match( $value ){
                  CompareType::GreaterEqual->value => CompareType::LessEqual->value, 
                  CompareType::LessEqual->value => CompareType::GreaterEqual->value, 
                  CompareType::Greater->value => CompareType::Less->value, 
                  CompareType::Less->value => CompareType::Greater->value, 
                    default => $value
                } 
              );
            }

            $this->tokens->setValue( $i, $nextToken );
            $this->tokens->setValue( $i + 1, $compToken );
            $this->tokens->setValue( $i + 2, $currToken );

            $i += 3;
          }
        }
      }
    }
  }  

  private function setTokensOrgsEntitys(
  ): void {
    for( $i = 0; $i < $this->tokens->count(); $i++ ){
      $fieldEntity = $this->getToken( $i );
      if( $fieldEntity instanceof Token ){
        if( $fieldEntity->takenType === TokenType::FieldEntity ){
          [ $paramName, $fieldName ] = $this->getParamAndField( 
            $fieldEntity->tokenValue->first()
          );

          $parameter = $this->getParameterByName( $paramName );

          if( $parameter instanceof Parameter ){
            $fieldEntity->setEntity( $parameter, $fieldName );

            $nextToken = $this->getToken( $i + 2 );
            if( $nextToken !== null && $nextToken->takenType !== TokenType::FieldEntity ){
              $nextToken->setEntity( $parameter, $fieldName );
            }
          }
        }
      }
    }
  }
  
  private function setTokensOrgsGroups(
    int $startParent = 0
  ): void {
    for( $i = 0; $i < $this->tokens->count(); $i++ ){
      $currToken = $this->getToken( $i );

      if( $currToken instanceof Token && $currToken->takenType === TokenType::FieldEntity ) {
        for( $j = $i + 3; $j < $this->tokens->count(); $j++ ){
          $nextToken = $this->getToken( $j );

          if( $nextToken instanceof Token ){
            if( $nextToken->takenType === TokenType::StartParent ){
              $startParent++;
            }
            if( $nextToken->takenType === TokenType::EndParent ){
              $startParent--;
            }

            if( $startParent === 0 ){
              if( $nextToken->takenType === TokenType::FieldEntity ){
                $hasTokenGroup = $currToken->entity->table === $nextToken->entity->table 
                              && $currToken->fieldName === $nextToken->fieldName;

                if( $hasTokenGroup ){
                  $prevToken = $this->getToken( $j - 1 );

                  $hasPrevToken = $prevToken instanceof Token 
                                && $prevToken->takenType === TokenType::Logical;

                  $this->tokens->spliceIn( 
                    $i + 3, 0, $this->tokens->spliceOut(
                      $hasPrevToken ? $j - 1 : $j, $hasPrevToken ? 4 : 3
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

  private function hasBetween(
    int $index
  ): bool {
    $tokens = $this->tokens->slice(
      $index, 7
    );

    if( $tokens->exist() && $tokens->count() === 7 ){
      [ $field1, $compare1, $value1, $logical, 
        $field2, $compare2, $value2
      ] = $tokens->all();

      $allTokenIsValids = $field1 instanceof Token && $compare1 instanceof Token && $value1 instanceof Token && $logical instanceof Token 
                       && $field2 instanceof Token && $compare2 instanceof Token && $value2 instanceof Token;

      if( $allTokenIsValids ){
        $hasFieldEntitys = $field1->takenType === TokenType::FieldEntity && $field2->takenType === TokenType::FieldEntity
                        && $value1->takenType === TokenType::FieldValue && $value2->takenType === TokenType::FieldValue;
        
        if( $hasFieldEntitys ){
          $hasFieldEntityEqual = $field1->entity->table === $field2->entity->table 
                              && $field1->fieldName === $field2->fieldName;

          if( $hasFieldEntityEqual ){
            $param1 = $this->getParameterByName( $field1->entity->table );
            $param2 = $this->getParameterByName( $field2->entity->table );

            if( $param1 instanceof Parameter && $param2 instanceof Parameter ){
              $fieldType1 = $param1->entityStructure->types->get( $field1->fieldName );
              $fieldType2 = $param2->entityStructure->types->get( $field2->fieldName );

              if( $fieldType1 instanceof Column && $fieldType2 instanceof Column ){
                $hasColumnType1 = Util::inArray( $fieldType1->instance->columnType, [ 
                  ColumnType::date, ColumnType::datetime, ColumnType::number
                ]);
                
                $hasColumnType2 = Util::inArray( $fieldType2->instance->columnType, [ 
                  ColumnType::date, ColumnType::datetime, ColumnType::number 
                ]);

                $hasComparesInverted = $compare1 instanceof Token && $compare1->tokenValue->first() === CompareType::GreaterEqual->value
                                    && $compare2 instanceof Token && $compare2->tokenValue->first() === CompareType::LessEqual->value;

                $hasLogicalAnd = $logical instanceof Token 
                  ? strtolower( $logical->tokenValue->first() ) === strtolower( LogicalType::And->value )
                  : false; 
                
                return $hasComparesInverted
                    && $hasLogicalAnd
                    && $hasColumnType1 
                    && $hasColumnType2;
              }
            }            
          }
        } 
      }
    }

    return false;
  }

  private function setTokensOrgsCompares(
  ): void {
    for( $i = 0; $i < $this->tokens->count(); $i++ ){
      $currToken = $this->getToken( $i );
      if( $currToken instanceof Token && $currToken->takenType === TokenType::FieldEntity ){
        if( $this->hasBetween( $i )){
          $tokensMoveds = $this->tokens->spliceOut( $i, 7 );

          [ $value1, $value2 ] = [
            ...$tokensMoveds->slice( 2, 1 )->all(),
            ...$tokensMoveds->slice( 6, 1 )->all()
          ];

          if( $value1 instanceof Token && $value2 instanceof Token ){
            $this->tokens->spliceIn( $i, 0, [
              $currToken, new Token( TokenType::Logical, new Collection([ LogicalType::Between->value ])),
              $value1, new Token( TokenType::Logical, new Collection([ LogicalType::And->value ])),
              $value2      
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
    $isNotEnum = !preg_match( 
      "#^([\w\\\]+)::(\w+)(?:->(\w+))?$#", $enumValue, $enumPaths 
    );

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
    $value = $this->statics->get( $value );
    return $value !== null ? $value : $staticValue;
  }

  private function setTokensOrgsParses(
  ): void {
    for( $i = 0; $i < $this->tokens->count(); $i++ ){
      $currToken = $this->getToken( $i );
      if( $currToken instanceof Token && $currToken->takenType === TokenType::FieldValue ){
        $currToken->tokenValue = $currToken->tokenValue->mapper(
          function( Token $token ){
            if( $token->takenType === TokenType::FieldEnum ){
              $token->tokenValue = new Collection([
                $this->parseEnum( $token->tokenValue->first())
              ]);
            } else if( $token->takenType === TokenType::FieldValue ){
              $token->tokenValue = new Collection([
                $this->parseStatic( $token->tokenValue->first())
              ]);
            }

            return $token;
          }
        );
      }
    }
  }
}