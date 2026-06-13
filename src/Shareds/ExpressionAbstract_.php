<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Commons\Utils;
use function ord, in_array, count, array_slice, sprintf, is_string;
use ReflectionFunction;
use Closure;

/* defined consts to tokens */
define( 'T_START_PARENTESES', 40 );
define( 'T_END_PARENTESES', 41 );
define( 'T_START_BRACKET', 91 );
define( 'T_END_BRACKET', 93 );
define( 'T_START_BRACE', 123 );
define( 'T_END_BRACE', 125 );
define( 'T_DOT', 46 );
define( 'T_COMMA', 44 );
define( 'T_SEMICOLON', 59 );
define( 'T_COLON', 58 );
define( 'T_QUESTION', 63 );
define( 'T_PLUS', 43 );
define( 'T_MINUS', 45 );
define( 'T_MULTIPLY', 42 );
define( 'T_DIVIDE', 47 );
define( 'T_EQUAL', 61 );
define( 'T_GREATER_THAN', 62 );
define( 'T_LESS_THAN', 60 );
define( 'T_NOT', 33 );

/* defined consts to objects */
define( 'T_EXP_INITIAL', 'ExpIntial' );
define( 'T_EXP_DENYING', 'ExpDenying' );
define( 'T_EXP_GROUP', 'ExpGroup' );
define( 'T_EXP_LOGICAL', 'ExpLogical' );
define( 'T_EXP_COMPARE', 'ExpCompare' );
define( 'T_EXP_BETWEEN', 'ExpBetween' );
define( 'T_EXP_UNARY', 'ExpUnary' );
define( 'T_EXP_SUBQUERY', 'ExpSubQuery' );
define( 'T_EXP_FIELD', 'ExpField' );
define( 'T_EXP_EQUAL', 'ExpEqual' );
define( 'T_EXP_VALUE', 'ExpValue' );

class ExpressionAbstract_ extends Utils
{
  public ExpressionType $expressionType;  
  public ReflectionFunction $reflectionFunction;
  public string $cacheClass;
  public string $cacheMethod;
  public array $entitys = [];
  public array $scopes = [];
  public array $statics = [];
  public array $params = [];  
  public array $contexts = [];
  public array $tokens = [];
  public array $uses = [];

  public function __construct(
    public Closure $closure
  ){
    calcTimer( "Construtor da class ExpressionWHere" );
    $this->analysisLexical();
  }

  public function groupByTypes(
    array $type,
    array $tokens,
     bool $showKey = false,
    array $curr = [],
    array $accu = [],
      int $depth = 0
  ): array {
    foreach( $tokens as $token ){
      if( in_array( $token[0], $type ) && $depth === 0){
        if( $curr ){
          $accu[] = $curr;
          $curr = [];
        } 
        
        if( $showKey ){
          $accu[] = [ $token ];
        }

        continue;
      }

      $curr[] = $token;

      if($token[0] === T_START_PARENTESES) $depth++;
      if($token[0] === T_END_PARENTESES) $depth--;
    }

    if( $curr ){
      $accu[] = $curr;
    }

    return $accu;
  }

  public function contextsNotEnds(
    array $contexts,
    int $parenteses = 0
  ): array {
    for($i=0; $i<count($contexts); $i++){
      if($contexts[$i][0] === T_START_PARENTESES){
        $parenteses++;
      }

      if($contexts[$i][0] === T_END_PARENTESES){
        $parenteses--;

        if($parenteses < 0){
          $contexts = array_slice(
            $contexts, 0, $i
          ); break;
        }          
      }

      if($parenteses < 1){
        if($contexts[$i][0] === T_SEMICOLON){
          $contexts = array_slice(
            $contexts, 0, $i
          ); break;
        }
      }
    };

    return $contexts;
  }

  public function fieldPropByEntity(
    string $entity
  ): array {
    if( isset( $this->entitys[ $entity ])){
      return [
        $this->entitys[ $entity ]['entity'],
        $this->entitys[ $entity ]['types']
      ];
    }
    
    $this->entitys[ $entity ] = new EntityStructure( $entity );
    $this->entitys[ $entity ] = $this->entitys[ $entity ]->get()->contexts;
    
    return [
      $this->entitys[ $entity ]['entity'],
      $this->entitys[ $entity ]['types']
    ];
  }  
  
  public function scopeByField(
    array $scopes,
    string $scopeVariable 
  ): string|null {
    if( empty( $scopes )){
      return null;
    }

    $scopes = $this->where(
      $scopes, fn( array $scope ) => $scope[0] === $scopeVariable 
    );

    if( empty( $scopes )){
      return null;
    }

    return $scopes[0][1];
  }

  public function fieldProps(
    array $contexts = []
  ): array {
    [ $scopeVariable, $_, $fieldVariable ] = $contexts;
    return [ $scopeVariable[1], $fieldVariable[1] ];
  }  

  public function analysisLexicalTokens(
  ): void {
    $this->reflectionFunction = new ReflectionFunction( $this->closure );
    if( $this->reflectionFunction instanceof ReflectionFunction ){
      $this->tokens = file( $this->reflectionFunction->getFileName());

      // if( count( $this->tokens ) !== 0 ){
      //   $this->tokens = $this->mapper(
      //     $this->tokens, function(string $token){
      //       $strPos = strpos($token, '//');
      //       return $strPos ? substr($token, 0, $strPos) : $token;
      //     }
      //   );
      // }
    }
  }

  public function analysisLexicalUses(
  ): void {
    $this->uses = $this->where(
      $this->tokens, fn(string $row) => str_starts_with( 
        trim( $row), 'use'
      )
    );

    $this->uses = $this->mapper(
      $this->uses, function(string $row){
        $use = str_replace(
          [ 'use',';' ], '', $row
        );

        if( strpos($use, 'as') !== false ){
          [ $use, $key ] = explode( 'as', $use );
          return [ trim($use), trim($key)];
        } else {
          $useImplits = explode('\\', $use);
          return [
            trim( implode( '\\', array_slice( $useImplits, -1 ))),
            trim( implode( '\\', array_slice( $useImplits, 0 )))
          ];
        }        
      }
    );
  }

  public function analysisLexicalOrigins(
  ): void {
    for( $i=count( $this->tokens ) - 1; $i>=0; $i-- ){
      if( strpos( $this->tokens[$i], 'function' ) !== false ){
        if( isset( $this->cacheMethod ) === false ){
          $this->cacheMethod = preg_replace([
            "#^.*function\s*#", "#\s*\(.*$#"
          ], "", trim( $this->tokens[ $i ]));
        }
      }
      
      if( strpos( $this->tokens[$i], 'class' ) !== false ){
        if(isset( $this->cacheClass ) === false ){
          $this->cacheClass = preg_replace([
            "#^.*class\s*#", "#\s*\{.*$#"
          ], "", trim( $this->tokens[ $i ]));
        }
      }      
    }
  }

  public function analysisLexicalStatics(
  ): void {
    $this->statics = $this->reflectionFunction
      ->getStaticVariables();    
  }

  public function namberToken(
    int $namberToken
  ): string {
    return match( $namberToken ){
       34 => 'T_ASP',
       40 => 'T_START_PARENTESES',
       41 => 'T_END_PARENTESES',
       91 => 'T_START_BRACKET',
       93 => 'T_END_BRACKET',
       46 => 'T_DOT',
       44 => 'T_COMMA',
       59 => 'T_SEMICOLON',
       58 => 'T_COLON',
       63 => 'T_QUESTION',
       43 => 'T_PLUS',
       45 => 'T_MINUS',
       42 => 'T_MULTIPLY',
       47 => 'T_DIVIDE',
       61 => 'T_EQUAL',
       62 => 'T_GREATER_THAN',
       60 => 'T_LESS_THAN',
       33 => 'T_NOT',
      123 => 'T_START_BRACE',
      125 => 'T_END_BRACE',
        default => token_name( $namberToken )
    };
  }  

  public function createToken(
    string|array $tokenArgs
  ): array {
    [ $number, $value ] = is_string( $tokenArgs ) 
      ? [ ord( $tokenArgs ), $tokenArgs ] : $tokenArgs;

    if( in_array( $number, [ T_CONSTANT_ENCAPSED_STRING ])){
      $value = trim( $value, '"\'' );
    }  

    return [ $number, $value, $this->namberToken($number)];
  }  

  public function analysisLexicalContexts(
  ): void {
    $this->contexts = array_slice(
      token_get_all(
        sprintf( '<?php %s', implode(
          '', array_slice(
            $this->tokens, 
            $this->reflectionFunction->getStartLine() - 1,
            $this->reflectionFunction->getEndLine() - 
            $this->reflectionFunction->getStartLine() + 1
          )
        ))
      ), 1
    ); 
    
    $this->contexts = $this->mapper(
      $this->contexts, fn(array|string $token) => (
        $this->createToken($token)
      ) 
    );

    $this->contexts = $this->where(
      $this->contexts, fn(array $token) => (
        $token[0] !== T_WHITESPACE
      ) 
    );
  }

  public function analysisLexicalScopesExtracts(
    array $contexts = []
  ): array {
    $this->scopes = $this->groupByTypes(
      [ T_COMMA ], $this->slice(
        $this->slice( $contexts, 
          $this->inc( $this->indexOf( $contexts, T_FN )),
          $this->dec( $this->indexOf( $contexts, T_DOUBLE_ARROW ))
        ), 1, -1 
      )
    );

    return $this->mapper(
      $this->scopes, fn( array $scope ) => [
        $scope[1][1], $this->where(
          $this->uses, fn( array $use ) => $use[0] === $scope[0][1]
        )[0][1]
      ]
    );      
  }

  public function analysisLexicalScopes(
  ): void {
    $this->scopes = $this->analysisLexicalScopesExtracts( $this->contexts );
    $this->contexts = $this->contextsNotEnds(
      $this->slice( $this->contexts, $this->inc( $this->indexOf(
        $this->contexts, T_DOUBLE_ARROW
      )))
    );
  }

  public function isDenying(
    array $tokens = []
  ): bool {
    return $tokens[0][0] === T_NOT;
  }
  
  public function isGroup(
    array $tokens = []
  ): bool {
    return $tokens[0][0] === T_START_PARENTESES;
  }
  
  public function getSubQueryMethod(
    array $tokens = []    
  ): array|null {
    $subQueryMethod = array_slice(
      $tokens, $this->dec(
        $this->indexOf(
          $tokens, T_FN), 1
      ), 1
    );

    return $subQueryMethod ?? null;
  }  

  public function isSubQuery(
    array $contexts = []    
  ): bool {
    [ $subQueryMethod ] = $this->getSubQueryMethod($contexts);
    return in_array( $subQueryMethod[1], [ 'any' ]);
  }
  
  public function isLogical(
    array $tokens = []
  ): bool {
    [ $tokens ] = $tokens;
    [ $log ] = $tokens;
    return in_array( $log, [
      T_LOGICAL_AND, T_LOGICAL_OR, T_BOOLEAN_AND, T_BOOLEAN_OR 
    ]);
  }
  
  public function isCompare(
    array $tokens = []
  ): bool {
    return empty(
      array_filter( $tokens, fn(array $token) => in_array( $token[0], [
        T_IS_NOT_IDENTICAL, T_IS_GREATER_OR_EQUAL, T_IS_SMALLER_OR_EQUAL,
        T_EQUAL, T_IS_EQUAL, T_IS_IDENTICAL, T_IS_NOT_EQUAL,
        T_GREATER_THAN, T_LESS_THAN
      ]))
    ) ? false : true;
  }

  public function isUnary(
    array $tokens = []
  ): bool {
    return $this->isCompare($tokens) === false;
  }  

  public function getExpType(
    array $contexts = []
  ): string|null {
    if( $this->isDenying( $contexts )){
      return T_EXP_DENYING;
    } else if( $this->isGroup( $contexts )){
      return T_EXP_GROUP;
    } else if( $this->isSubQuery( $contexts )){
      return T_EXP_SUBQUERY;
    } else if( $this->isLogical( $contexts )){
      return T_EXP_LOGICAL;
    } else if( $this->isCompare( $contexts )){
      return T_EXP_COMPARE;
    } else if( $this->isUnary( $contexts )){
      return T_EXP_UNARY;
    }
      
    return null;
  }

  public function isField(
    array $contexts = []
  ): bool {
    if( count( $contexts ) < 3 ){
      return false;
    }

    return $contexts[0][0] === T_VARIABLE
        && $contexts[1][0] === T_OBJECT_OPERATOR
        && $contexts[2][0] === T_STRING;
  }
  
  public function analysisLexicalInit(
  ): void {}

  public function analysisLexicalClear(
  ): void {
    unset( $this->tokens );
    unset( $this->entitys );
    unset( $this->closure );
    unset( $this->reflectionFunction );
  }
  
  public function analysisLexical(
  ): void {
    $this->analysisLexicalTokens();
    calcTimer( "Criar Listagem de ExpressionWhere::Tokens" );
    $this->analysisLexicalUses();
    calcTimer( "Criar Listagem de ExpressionWhere::Uses" );
    $this->analysisLexicalOrigins();
    calcTimer( "Criar Listagem de ExpressionWhere::Origens" );
    $this->analysisLexicalStatics();
    calcTimer( "Criar Listagem de ExpressionWhere::Statics" );
    $this->analysisLexicalContexts();
    calcTimer( "Criar Listagem de ExpressionWhere::Contexts" );
    $this->analysisLexicalScopes();
    calcTimer( "Criar Listagem de ExpressionWhere::Scopes" );
    $this->analysisLexicalInit();
    calcTimer( "Criar Listagem de ExpressionWhere::Scopes" );
    $this->analysisLexicalClear();
    calcTimer( "Criar Listagem de ExpressionWhere::Limpar" );
  }
}