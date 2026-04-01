<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Interfaces\Parameter;
use Websyspro\Entity\Interfaces\UsePath;
use Websyspro\Entity\Consts\Patterns;
use Websyspro\Entity\Enums\MultiLine;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionFunction;
use ReflectionProperty;
use Websyspro\Entity\Enums\Type;
use Websyspro\Entity\Interfaces\Join;

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
          : ( $parameterChild->multiLine === MultiLine::No
              ? new Join( MultiLine::No, $parameterChild, $parameterParent )
              : new Join( MultiLine::Yes, $parameterChild, $parameterParent )
            );
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
            $parameterParentEntityList = array_values( array_filter(
              $this->parameters, fn( Parameter $parameter ) => $parameter->name === preg_replace( 
                Patterns::PATTERN_REMOVE_DEFINED_VAR_KEY, "", $parameterParent
              )
            ));
            
            if( empty( $parameterParentEntityList ) === false ){
              [ $parameterParentEntity ] = $parameterParentEntityList;
            
              if( $parameterParentEntity instanceof Parameter ){     
                if ( property_exists( $parameterParentEntity->usePath->entity, $parameterChild )) {
                  $reflectionProperty = new ReflectionProperty(
                    $parameterParentEntity->usePath->entity, $parameterChild
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
  
  private function structureClear(
  ): void {
    unset( $this->rows, $this->body );
  }
}