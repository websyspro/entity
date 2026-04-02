<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Decorations\Columns\Datetime;
use Websyspro\Entity\Decorations\Columns\LongText;
use Websyspro\Entity\Decorations\Columns\Decimal;
use Websyspro\Entity\Decorations\Columns\Number;
use Websyspro\Entity\Decorations\Columns\Date;
use Websyspro\Entity\Decorations\Columns\Enum;
use Websyspro\Entity\Decorations\Columns\Flag;
use Websyspro\Entity\Decorations\Columns\Time;
use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Interfaces\Parameter;
use Websyspro\Entity\Interfaces\UsePath;
use Websyspro\Entity\Enums\CompareType;
use Websyspro\Entity\Enums\LogicalType;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Entity\Interfaces\Join;
use Websyspro\Entity\Consts\Patterns;
use Websyspro\Entity\Enums\MultiLine;
use Websyspro\Entity\Enums\Type;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionFunction;
use ReflectionProperty;
use BackedEnum;
use UnitEnum;

class StructureFile
{
  public array $rows = [];
  public array $namespace = [];
  public array $parameters = [];
  public array $statics = [];
  public array $usePaths = [];
  public array $body = [];
  public array $joins = [];
  public array $tokens = [];
  public array $params = [];

  public function __construct(
    public ReflectionFunction $reflectionFunction
  ){
    $this->structureFile();
    $this->structureClear();
  }

  private function structureFile(
  ): void {
    $this->rows = file( 
      $this->reflectionFunction->getFileName()
    );

    $this->structureFileNamespace();
    $this->structureFileParameters();    
    $this->structureFileStatics();
    $this->structureFileUsePaths();
    $this->structureFileBody();
    $this->structureFileHidrate();
    $this->structureFileJoins();
    $this->structureFileTokens();
    $this->structureFileBuild();
    $this->structureFileGroups();
    $this->structureFileParses();
    $this->structureFileResume();
  }

  private function structureFileNamespace(
  ): void {
    $this->namespace = array_filter( $this->rows, 
      fn( string $row ) => preg_match( 
        Patterns::PATTERN_IS_NAMESPACE, $row 
      )
    );
  }

  private function structureFileParameters(
  ): void {
    foreach( $this->reflectionFunction->getParameters() as $reflectionParameter ){
      if( $reflectionParameter instanceof $reflectionParameter ){
        $parameterName = StructureUtil::getParameterName( $reflectionParameter );
        $parameterTypeName = StructureUtil::getParameterTypeName( $reflectionParameter );

        $this->parameters[ $parameterName ] = new Parameter( $parameterName, $parameterTypeName );
      }
    }
  }

  private function structureFileStatics(
  ): void {
    $this->statics = $this->reflectionFunction->getStaticVariables();    
  }  

  private function structureFileUsePaths(
  ): void {
    foreach( $this->rows as $row ){
      if( preg_match( Patterns::PATTERN_NAMESPACE_WHERES, $row )){
        $usePathNew = new UsePath( preg_replace( Patterns::PATTERN_NAMESPACE_HYDRATE, "", $row ));

        if( $usePathNew instanceof UsePath ){
          $this->usePaths[ $usePathNew->entity ] = $usePathNew;
        }
      }
    }
  } 
  
  private function structureFileBody(
  ): void {
    $this->body = array_slice( 
      $this->rows,
      $this->reflectionFunction->getStartLine() - 1, 
      $this->reflectionFunction->getEndLine() - 
      $this->reflectionFunction->getStartLine() + 1
    );
  }

  private function structureFileHidrate(
  ): void {
    $this->body = array_filter(
      $this->body, fn( string $row ) => (
        !preg_match( Patterns::PATTERN_REMOVE_COMMENT_LINE, $row )
      )
    );

    [ $hydrateBodyFrom, $hydrateBodyTos 
    ] = Patterns::PATTERN_HYDRATE_BODY;

    preg_match_all( Patterns::PATTERN_TOKEN, preg_replace(
      $hydrateBodyFrom, $hydrateBodyTos, implode( "", $this->body ),
    ), $matchTokens );

    if( empty( $matchTokens ) === false ){
      [ $this->tokens ] = $matchTokens;
    };
  }

  /**
   * Safely extracts the type name from a ReflectionProperty.
   *
   * Handles:
   * - null types
   * - named types
   * - union types (PHP 8+)
   *
   * @param ReflectionProperty $property
   * @return string|null
   */
  private static function getPropertyTypeName(
    ReflectionProperty $property
  ): string|null {
    $type = $property->getType();

    if ($type === null) {
        return null;
    }

    if( $type instanceof ReflectionNamedType ){
        return $type->getName();
    }

    if ( $type instanceof ReflectionUnionType ){
      return implode('|', array_map(
        fn($t) => $t->getName(),
          $type->getTypes()
      ));
    }

    return null;
  }

  private function getParameterByName(
    string $name
  ): Parameter|null {
    if( empty( $this->parameters )){
      return null;
    }

    return $this->parameters[ $name ] ?? null;
  }

  private function addJoin(
    Parameter $parameterBase,
    Parameter $parameterParent,
    Parameter $parameterChild
  ): Parameter {
    if( $parameterBase instanceof Parameter ){
      if( $parameterParent instanceof Parameter && $parameterChild instanceof Parameter ){
        $this->joins[] = $parameterBase === $parameterParent && $parameterChild->multiLine === MultiLine::No
          ? new Join( MultiLine::No, $parameterChild, $parameterParent )
          : new Join( MultiLine::Yes, $parameterChild, $parameterParent );
      }
    }

    return $parameterChild;
  }

  private function isStructureJoins(
    Parameter $parameterBase,
    string $token,
    int $index
  ): void {
    if( preg_match( Patterns::PATTERN_IS_HIERARCHY_JOINS, $token )){
      $strutureJoin = preg_replace( Patterns::PATTERN_REMOVE_END_HIERARCHY_JOINS, "", $token );
      $strutureJoinList = preg_split( Patterns::PATTERN_HIERARCHY_JOINS_SEPARETOR, $strutureJoin );

      for( $i=0; $i < sizeof( $strutureJoinList ); $i++ ){
        if( (int)$i !== 0 ){
          $parameterParent = $strutureJoinList[ $i - 1];
          $parameterChild = $strutureJoinList[ $i ];

          if( $parameterParent ){
            foreach( $this->parameters as $parameter ){
              if( $parameter instanceof Parameter ){
                if( $parameter->name === preg_replace( Patterns::PATTERN_REMOVE_DEFINED_VAR_KEY, "", $parameterParent )){
                  if ( property_exists( $parameter->usePath->entity, $parameterChild )) {
                    $reflectionProperty = new ReflectionProperty(
                      $parameter->usePath->entity, $parameterChild
                    );

                    if( $reflectionProperty->getType() instanceof ReflectionNamedType ){
                      if( self::getPropertyTypeName( $reflectionProperty ) === EntityList::class ){
                        $name = $this->tokens[ $index + 2 ];
                        $parameterChild = $this->tokens[ $index + 3 ];
                        
                        if( $name && $parameterChild ){
                          foreach( $this->usePaths as $usePath ){
                            if( $usePath->name === $name ){
                              $paramterNew = $this->addJoin(
                                $parameterBase, $this->getParameterByName( $parameterParent ), new Parameter( 
                                  $parameterChild, $usePath->entity, MultiLine::Yes
                                )
                              );

                              $this->parameters[ $paramterNew->name ] = $paramterNew;
                            }
                          }
                        }
                      } else {
                        $paramterNew = $this->addJoin(
                          $parameterBase, $this->getParameterByName( $parameterParent ), new Parameter( 
                            $parameterChild, self::getPropertyTypeName( $reflectionProperty ), MultiLine::No
                          )
                        );

                        $this->parameters[ $paramterNew->name ] = $paramterNew;
                      }
                    }
                  }
                }                
              }
            }
          }
        }
      }
    }
  }

  private function structureFileJoins(
  ): void {
    $parameterBase = reset( 
      $this->parameters
    );

    if( empty( $this->tokens ) === false ){
      foreach( $this->tokens as $key => $token ){
        $this->isStructureJoins( $parameterBase, $token, $key );
      }
    }
  }

  private function structureFileTokens(
  ): void {
    for( $i=0; $i < sizeof( $this->tokens ); $i++ ){
      if( preg_match( Patterns::PATTERN_IS_HIERARCHY_JOINS_FROM_LIST, $this->tokens[ $i ])){
        $this->tokens[ $i ] = "(";
        array_splice( $this->tokens, $i + 1, 5 ); 
      }

      if( substr_count( $this->tokens[ $i ], "->" ) >= 2 ){
        $pathTokens = preg_split( 
          Patterns::PATTERN_HIERARCHY_JOINS_SEPARETOR, 
          $this->tokens[ $i ]
        );

        $this->tokens[ $i ] = sprintf( "$%s->%s", ...array_merge(
          array_slice( $pathTokens, -2 ), array_slice( $pathTokens, -1 )
        ));
      }
      
      $this->tokens[ $i ] = preg_replace(
        Patterns::PATTERS_SIMPLE_QUOTATION_MARKS, "", $this->tokens[ $i ]
      );
    }
  }

  private function structureFileBuild(
    array $groupMultiLine = [],
    int $groupStartPos = -1,
    int $group = 1   
  ): void {
    /** @var array<int, string|Token> $tokens */
    for( $i = 0; $i < sizeof( $this->tokens ); $i++ ){
      $currType = StructureUtil::getTokenByValue( 
        $this->tokens[ $i + 0 ]
      );
      
      $hasEntityOrString = in_array( 
        $currType, [ Type::Entity, Type::String ]
      ); 

      if( $hasEntityOrString === false ){
        if( $currType === Type::StartGroup ){
          $groupMultiLine[ $group ] = [];
          $groupStartPos = $i;
          $group++;
        }
        
        /* Append Tokens Arr */
        $this->tokens[ $i ] = StructureUtil::createToken( 
          $this->tokens[ $i ], $group, $currType, $this
        );

        if( $this->tokens[ $i ]->type === Type::EndGroup ){
          if( $groupStartPos !== -1 ){
            if( $this->tokens[ $groupStartPos ] instanceof Token ){
              if( $this->tokens[ $groupStartPos ]->type === Type::StartGroup ){
                $this->tokens[ $groupStartPos ]->setMultiLine(
                  in_array( MultiLine::Yes, $groupMultiLine[ $group ] ) 
                    ? MultiLine::Yes
                    : MultiLine::No
                );

                if( $this->tokens[ $groupStartPos - 1 ] instanceof Token ){
                  if( $this->tokens[ $groupStartPos - 1 ]->type === Type::Logical ){
                    $this->tokens[ $groupStartPos - 1 ]->setMultiLine( 
                      $this->tokens[ $groupStartPos ]->multiLine
                    );
                  }
                }
                
                if( $this->tokens[ $i ] instanceof Token ){
                  $this->tokens[ $i ]->setMultiLine(
                    $this->tokens[ $groupStartPos ]->multiLine
                  );
                }
              }
            }
          }

          $groupMultiLine[ $group ] = [];
          $group--;
        }
      } else {
        $compType = StructureUtil::getTokenByValue( $this->tokens[ $i + 1 ]);
        $nextType = StructureUtil::getTokenByValue( $this->tokens[ $i + 2 ]);

        $currToken = StructureUtil::createToken( $this->tokens[ $i + 0 ], $group, $currType, $this );
        $compToken = StructureUtil::createToken( $this->tokens[ $i + 1 ], $group, $compType, $this );
        $nextToken = StructureUtil::createToken( $this->tokens[ $i + 2 ], $group, $nextType, $this );
        
        if( $currToken->type === Type::Entity && $nextToken->type === Type::String ){
          $this->tokens[ $i + 0 ] = $currToken;
          $this->tokens[ $i + 1 ] = $compToken->setMultiLine( $currToken->multiLine )->setParseCompare( $nextToken );
          $this->tokens[ $i + 2 ] = $nextToken->setMultiLine( $currToken->multiLine )->setEntity( $currToken->entity )->setField( $currToken->field ); 
        } else 
        if( $currToken->type === Type::String && $nextToken->type === Type::Entity ){
          $this->tokens[ $i + 0 ] = $nextToken;
          $this->tokens[ $i + 1 ] = $compToken->setMultiLine( $nextToken->multiLine )->setInvertCompare()->setParseCompare( $currToken );
          $this->tokens[ $i + 2 ] = $currToken->setMultiLine( $nextToken->multiLine )->setEntity( $nextToken->entity )->setField( $nextToken->field );   
        } else {
          $this->tokens[ $i + 0 ] = $currToken;
          $this->tokens[ $i + 1 ] = $compToken->setMultiLine( $currToken->multiLine );
          $this->tokens[ $i + 2 ] = $nextToken;
        }

        $entityRootRefence = $currToken->multiLine !== $nextToken->multiLine
          ? MultiLine::No : ( 
              $currToken->multiLine === MultiLine::Yes && 
              $nextToken->multiLine === MultiLine::Yes
                ? MultiLine::Yes
                : MultiLine::No
            );

        $currToken->setMultiLine( $entityRootRefence );
        $compToken->setMultiLine( $entityRootRefence );
        $nextToken->setMultiLine( $entityRootRefence );

        if( $i >= 1 ){
          if( $this->tokens[ $i - 1 ] instanceof Token ){
            if( $this->tokens[ $i - 1 ]->type === Type::Logical ){
              $this->tokens[ $i - 1 ]->setMultiLine( $entityRootRefence );
            }
          }
        }          
        
        $groupMultiLine[$group][] = $entityRootRefence;
        $i += 2;
      }
    }
  }

  public function isFieldsEquals(
    Token $currToken,
    Token $nextToken     
  ): bool {
    return $currToken->entity->table === $nextToken->entity->table 
        && $currToken->field === $nextToken->field;
  }  

  private function structureFileGroups(
  ): void {
    for( $i = 0; $i < sizeof($this->tokens); $i++ ){
      $currToken = $this->tokens[ $i + 0 ];

      if( $currToken instanceof Token && $currToken->type === Type::Entity ) {
        for( $j = $i + 3; $j < sizeof( $this->tokens ); $j++ ){
          $nextToken = $this->tokens[ $j ];

          if( $nextToken instanceof Token ){
            if( $currToken->group === $nextToken->group ){
              if( $nextToken->type === Type::Entity ){
                $isTokenGroup = $this->isFieldsEquals(
                  $currToken, $nextToken
                );

                if( $isTokenGroup ){
                  $logicalToken = $this->tokens[ $j - 1 ];
                  $isPrevToken = $logicalToken instanceof Token && $logicalToken->type === Type::Logical;

                  array_splice( $this->tokens, $i + 3, 0, array_splice(
                    $this->tokens, $isPrevToken ? $j - 1 : $j, $isPrevToken ? 4 : 3
                  ));

                  $i += 2;
                }
              }
            }
          }
        }
      }
    }
  }

  private function getExplodeValue(
    string $value
  ): array {
    if( StructureUtil::isContainsStatic( $value ) || StructureUtil::isEnumValue( $value )){
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

    return [ $value ];
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

    if( sizeof( $enumPaths ) === 4 ){
      [ $class, $case, $property ] = array_slice( $enumPaths, 1 );
    } else [ $class, $case ] = array_slice( $enumPaths, 1 );

    if( preg_match( "#^\\\#", $class ) === 0 ){
      foreach( $this->usePaths as $usePath ){
        if( $usePath instanceof UsePath ){
          if( $usePath->entity === $class ){
            $classPath = $usePath;
          }
        }
      }
    }

    if( enum_exists($classPath->entity) === false && class_exists($classPath->entity) === false) {
      return $enumValue;
    }
    
    $enumCase = constant( 
      "{$classPath->entity}::{$case}"
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

    $staticVar = preg_replace( "#^(\{\\$|\\$)|\\}$#", "", $staticValue );

    foreach( $this->statics as $static => $value ){
      if( $static === $staticVar ){
        return $value;
      }
    }

    return $staticValue;
  }  
  
  private function getColumnType(
    Token $token
  ): ColumnType|null {
    foreach( $this->parameters as $parameter ){
      if( $parameter instanceof Parameter ){
        if( $parameter->entityStructure->entity->class === $token->entity->class ){
          if( $parameter->entityStructure->types[ $token->field ] instanceof Column ){
            return match( $parameter->entityStructure->types[ $token->field ]->columnType ){
              Date::class => ColumnType::date,
              Datetime::class => ColumnType::datetime,
              Decimal::class => ColumnType::decimal,
              Enum::class => ColumnType::enum,
              Flag::class => ColumnType::flag,
              LongText::class => ColumnType::longtext,
              Number::class => ColumnType::number,
              Text::class => ColumnType::text,
              Time::class => ColumnType::time,
                default => ColumnType::text 
            };
          }
        }
      }
    }

    return null;
  }
  
  private function addParam(
    string $value,
    string|null $key = null
  ): string {
    $key = sprintf( ":param_%s", sizeof( $this->params ));
    $this->params[ $key ] = new Param( $value, $key );
    return $key;
  }  
  
  private function structureFileParses(
  ): void {
    for( $i = 0; $i < sizeof( $this->tokens ); $i++ ){
      $currToken = $this->tokens[ $i + 0 ];

      if( $currToken instanceof Token && $currToken->type === Type::String ){
        $tokens = $this->getExplodeValue( $currToken->value );

        if( is_array( $tokens ) && sizeof( $tokens ) !== 0 ){
          $columnType = $this->getColumnType( $currToken );
          
          if( $columnType instanceof ColumnType ){
            for( $j = 0; $j < sizeof( $tokens ); $j++ ){
              if( StructureUtil::isEnumValue( $tokens[ $j ] )){
                $tokens[ $j ] = $this->parseEnum( $tokens[ $j ] );
              } else if( StructureUtil::isStaticValue( $tokens[ $j ] )){
                $tokens[ $j ] = $this->parseStatic( $tokens[ $j ] );
              }

            }

            $parseEncodeValue = $columnType->Encode( 
              implode( "", $tokens )
            );

            if( is_array( $parseEncodeValue )){
              for( $p=0; $p < sizeof( $parseEncodeValue ); $p++ ){
                $parseEncodeValue[ $p ] = $this->addParam( $parseEncodeValue[ $p ]);
              }

              $currToken->value = sprintf(
                "(%s)", implode( ",", $parseEncodeValue )
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
        && $currCompToken instanceof Token && in_array(
          CompareType::tryFrom( $currCompToken->value ), [
            CompareType::GreaterEqual, CompareType::LessEqual,
            CompareType::Greater, CompareType::Less
          ]
        )
        && $currValToken instanceof Token
        && $logicalToken instanceof Token && $logicalToken->type === Type::Logical
        && $nextToken instanceof Token
        && $nextCompToken instanceof Token && in_array(
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
    $isFieldsValids = $currToken->field === $nextToken->field;
    
    
    if( $isEntitysValids === false || $isFieldsValids === false ){
      return false;
    }

    $columnType = $this->getColumnType( $currToken );
    if( $columnType instanceof ColumnType ){
      $isColumnTypeValid = in_array( $columnType, [ 
        ColumnType::date, ColumnType::datetime, ColumnType::number, ColumnType::decimal
      ]);

      return $isColumnTypeValid;
    }

    return false;
  }

  private function hasBetween(
    int $index
  ): bool {
    if( sizeof( $this->tokens ) - $index >= 7 ){
      [ $currToken, $currCompToken, $currValToken, $logicalToken, 
        $nextToken, $nextCompToken, $nextValToken
      ] = array_slice( $this->tokens, $index, 7 );

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
  
  private function structureFileResume(
  ): void {
    for( $i = 0; $i < sizeof( $this->tokens ); $i++ ){
      $currToken = $this->tokens[ $i + 0 ];
      $nextToken = $this->tokens[ $i + 2 ] ?? null;

      if( $currToken instanceof Token && $nextToken instanceof Token ){
        if( $this->hasBetween( $i )){

          $tokensMoveds = array_splice( 
            $this->tokens, $i, 7
          );

          [ $valCurrToken, $valNextToken ] = array_merge(
            array_slice( $tokensMoveds, 2, 1 ),
            array_slice( $tokensMoveds, 6, 1 )
          );

          if( $valCurrToken instanceof Token && $valNextToken instanceof Token ){
            $currTokenBetween = new Token( LogicalType::Between->value, $currToken->group, Type::Range );
            $currTokenBetweenValue = new Token( "{$valCurrToken->value} And {$valNextToken->value}", $currToken->group, Type::String );

            array_splice(
              $this->tokens, $i, 0, [
                $currToken,
                $currTokenBetween
                  ->setEntity( $currToken->entity )
                  ->setField( $currToken->field )
                  ->setMultiLine( $currToken->multiLine ),
                $currTokenBetweenValue
                  ->setEntity( $currToken->entity )
                  ->setField( $currToken->field )
                  ->setMultiLine( $currToken->multiLine )
              ]
            );
            
            $i += 6;
          }
        }
      }
    }
  }  
  
  private function structureClear(
  ): void {
    unset( $this->rows, $this->body );
  }
}