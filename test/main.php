<?php

use Websyspro\Entity\Enums\MetaType;
use Websyspro\Entity\Shareds\EntityStructure;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use Websyspro\Test\Crm\Entitys\ItemPropostaEntity;
use Websyspro\Test\Crm\Entitys\PropostaEntity;
use Websyspro\Test\Enums\Status;

$start = microtime( true );

$test = "Escola";

$startDate = '01/01/2026';

$fn = fn( PropostaEntity $i ) => (
  !$i->IsActive
  && $i->IsDeleted === false 
  && $i->Status === Status::Aprovada
  && ( $i->PrazoFaturamento === 9098767 )
  && $i->Created >= $startDate
  && $i->NomeProposta === "Teste {$test}"
  && $i->IsActive === true 
  && '01/31/2026' >= $i->Created
  && $i->itemsProposta->any( fn( ItemPropostaEntity $o ) => 
    $o->PropostaId === $i->Id && $o->IsActive === true && $o->IsDeleted === false
  )
  && $i->itemsProposta->any( fn( ItemPropostaEntity $o ) => 
    $o->IsActive && !$o->IsDeleted && $o->PropostaId === $i->Id
  )
);

enum WhereTokensType {
  case Initial;
  case Group;
}

class CacheEntityStructure
{
  public static array $cacheEntityStructure = [];

  public static function getByName(
    string $name
  ): EntityStructure|null {
    if( class_exists( $name )){
      if( isset( CacheEntityStructure::$cacheEntityStructure[ $name ]) === false ){
        $entityStructure = call_user_func_array(
          [ $name, "meta" ], [ MetaType::Query ]
        );

        if( $entityStructure instanceof EntityStructure ){
          CacheEntityStructure::$cacheEntityStructure[ $name ] = $entityStructure;
          return CacheEntityStructure::$cacheEntityStructure[ $name ];
        }
      }

      return CacheEntityStructure::$cacheEntityStructure[ $name ];
    }

    return null;
  }
}

class Imports
{

}

class ParameterList
{
  public function __construct(
    public string $parameter, 
    public string $alias
  ){}
}

class TokenSimple {
  public function __construct(
    public string $text
  ){}

  public function isLogical(
  ): bool {
    return Util::inArray( 
      strtolower( $this->text ), [ 
        "&&", "and", "||", "or"
      ]
    );
  }  
  
  public function isGroup(
  ): bool {
    return $this->text === "(";
  } 
  
  public function isFN(
  ): bool {
    return $this->text === "fn";
  }
  
  public function isNotFN(
  ): bool {
    return $this->text !== "fn";
  }  
}

class Token
{
  public int $type;
  public string $name;
  public string $text;

  public function __construct(
    array $token = []
  ){
    $this->startups($token);
  }

  public function isFN(
  ): bool {
    return $this->type === T_FN;
  }

  public function isNotFN(
  ): bool {
    return $this->type !== T_FN;
  }  

  public function isLogical(
  ): bool {
    return Util::inArray( $this->type, [ 
      T_LOGICAL_AND, T_LOGICAL_OR, T_BOOLEAN_AND, T_BOOLEAN_OR
    ]);
  } 
  
  public function isGroup(
  ): bool {
    return $this->text === "(";
  }  

  private function startups(
    array $token = []
  ): void {
      [ $this->type, $this->text ] = $token;
      
      if( isset( $this->type )){
        $this->name = token_name(
          $this->type
        );
      }
  }  
}

class TokensGroup
{
  public function __construct(
    public array $tokens
  ){}
}

class ExtractScriptFromFN
extends AbstractTokens
{
  public function __construct(
    public mixed $fn
  ){
    $this->startups();
    $this->startupsAnalyzeds();
  }

  private function startups(
  ): void {
    $this->cursorStart = 0;
    $this->cursor = 0;    
    $this->tokens = TokenExtract::get(
      $this->readScripByFunc(
        new ReflectionFunction( $this->fn )
      )
    )->tokens;
  }

  private function getRows(
    ReflectionFunction $reflectionFunction  
  ): Collection {
    return new Collection(
      file( $reflectionFunction->getFileName())
    );
  }   

  private function readScripByFunc(
    ReflectionFunction $reflectionFunction
  ): string {
    return $this->getRows( $reflectionFunction )->slice(
      $reflectionFunction->getStartLine() - 1, 
      $reflectionFunction->getEndLine() - $reflectionFunction->getStartLine() + 1
    )->where( fn( string $row ) => !str_starts_with(trim( $row ), "//" ))->joinWithSpace();
  }

  private function dropWhiteSpace(
    Token|TokenSimple $token
  ): bool {
    return $token instanceof TokenSimple 
        || $token instanceof Token && $token->type !== T_WHITESPACE;
  }

  private function updateTokens(
  ): void {
    $this->tokens = $this->slice(
      $this->cursorStart, $this->cursor
    )->where( fn( Token|TokenSimple $token ) => $this->dropWhiteSpace( $token ));
  }
  
  private function startupsAnalyzeds(
  ): void {
    $this->cursorMove( T_FN );
    $this->cursorStartNext();
    $this->cursorMove( T_DOUBLE_ARROW );
    $this->cursorNext();
    $this->cursorMoveToNextCloseFN();
    $this->updateTokens();
  }

  public static function get(
    callable $script
  ): ExtractScriptFromFN {
    return new static( $script );
  }  
}

class TokenExtract
{ 
  public Collection $tokens;

  public function __construct(
    public string $script
  ){
    $this->startups();
    $this->startupsAnalyzeds();
  }

  private function startups(
  ): void {
    $this->tokens = Collection::create( 
      token_get_all( "<?php {$this->script}" )
    )->slice(1);
  }

  private function startupsAnalyzeds(
  ): void {
    $this->tokens = $this->tokens
      ->mapper(fn( array|string $token ) => $this->createToken( $token ))
      ->where(fn( Token|TokenSimple $token ) => $this->dropWhiteSpace( $token ));
  }

  private function createToken(
    array|string $token
  ): Token|TokenSimple {
    return Util::isString( $token )
      ? new TokenSimple( $token )
      : new Token( $token );
  }

  private function dropWhiteSpace(
    Token|TokenSimple $token
  ): bool {
    return $token instanceof TokenSimple 
        || $token instanceof Token && $token->type !== T_WHITESPACE;
  }

  public static function get(
    string $script
  ): TokenExtract {
    return new static( $script );
  }
}

enum LogicalExpressionType
{
  case Group;
  case Compare;
  case SubQuery;
}

class LogicalExpressionCompare
{
  public function __construct(
    public Collection $tokens
  ){}
}

class LogicalExpressionGrupo
{
  public WhereTokens $whereTokens;

  public function __construct(
    Collection $tokens
  ){
    $this->startups( $tokens);
  }

  private function startups(
    Collection $tokens
  ): void {
    $this->whereTokens = new WhereTokens(
      $this->extractParenteses( $tokens )
    );    
  }  

  private function extractParenteses(
    Collection $tokens
  ): Collection {
    if( $tokens->getOneOrFail()->isLogical()){
      $tokens->spliceOut( 1, 1 );
      $tokens->spliceOut(-1, 1 );
      return $tokens;
    } else return $tokens->slice( 1, -1 );
  }
}

class LogicalExpressionSubQuery
{
  public function __construct(
    public Collection $tokens
  ){}
}

class AbstractTokens
{
  public int $cursor;
  public int $cursorStart;

  public function __construct(
    public Collection $tokens
  ){}  

  public function slice(
    int|null $cursorStart = null,
    int|null $cursorEnd = null
  ): Collection {
    $cursorStart = $cursorStart !== null 
      ? $cursorStart : $this->cursorStart;

    return $this->tokens->slice( 
      $cursorStart, $cursorEnd !== null 
        ? $cursorEnd - $cursorStart 
        : $cursorEnd
    );
  }

  public function getToken(
  ): Token|TokenSimple {
    return $this->tokens->getOneOrFail( $this->cursor );
  }
  
  public function getLeftToken(
  ): Token|TokenSimple {
    return $this->tokens->getOneOrFail( $this->cursor - 1 );
  }

  public function getRightToken(
  ): Token|TokenSimple {
    return $this->tokens->getOneOrFail( $this->cursor + 1 );
  }  

  public function isEof(
  ): bool {
    return $this->cursor < $this->tokens->count();
  }

  public function cursorPrev(
    int $step = 1
  ): void {
    $this->cursor -= $step;
  }  

  public function cursorNext(
    int $step = 1
  ): void {
    $this->cursor += $step;
  } 

  public function cursorStartNext(
  ): void {
    $this->cursorStart = $this->cursor;
  }
  
  public function indexOf(
    int|string $find
  ): int {
    return $this->tokens->indexOf( $find );
  }

  public function cursorMove(
    int|string|array $find
  ): int {
    while( $this->isEof() ){
      if( $this->getToken() instanceof Token ){
        if( !Util::isArray( $find )){
          if( $this->getToken()->type === $find ){
            break;
          }
        } else if( Util::inArray( $this->getToken()->type, $find )){
          break;
        }
      } else if( $this->getToken() instanceof TokenSimple ){
        if( $this->getToken()->text === $find ){
          break;
        }
      }
      
      $this->cursorNext();
    }
    
    return $this->cursor;
  }

  public function cursorMoveToNextCloseFN(
    int $parenteses = 0
  ): int {
    while( $this->isEof() ){
      if( $this->getToken() instanceof TokenSimple ){
        if( $this->getToken()->text === "(" ){
          $parenteses++;
        }

        if( $this->getToken()->text === ")" ){
          $parenteses--;
          if( $parenteses === 0 ){
            $this->cursorNext();
            break;
          }
        }
      }

      $this->cursorNext();
    }

    return $this->cursor;
  }
}


class WhereTokens 
extends AbstractTokens
{
  public Collection $parameterList;
  public Collection $tokensList;

  public function __construct(
    public Collection $tokens,
    public WhereTokensType $whereTokensType = WhereTokensType::Initial
  ){
    $this->startups();
    $this->startupsAnalyzeds();
    $this->startupsClear();
  }

  private function startups(
  ): void {
    $this->parameterList = new Collection();
    $this->tokensList = new Collection();
    $this->cursorStart = 0;
    $this->cursor = 0;
  }

  private function isGroupExist(
  ): bool {
    return $this->getToken()->isGroup() 
        && $this->getRightToken()->isNotFN();
  }

  private function createParameters(
  ): void {
    if( $this->getToken() instanceof Token ){
      if( $this->getToken()->type === T_FN ){
        $this->parameterList = $this->slice(
          $this->cursorMove( "(" ) + 1, 
          $this->cursorMove( ")" )
        );

        $this->parameterList = $this->parameterList
          ->mapper( fn( Token|TokenSimple $tokens ) => $tokens->text )
          ->chunk( 2 )
          ->mapper( fn( array $parameter ) => new ParameterList( ...$parameter ));
      }
    }
  }

  private function gotToWheres(
  ): void {
    if( $this->parameterList->exist() ){
      $this->cursorMove( T_DOUBLE_ARROW );
      $this->cursorNext();
      $this->cursorStartNext();
    }
  }

  private function createTokenList(
    LogicalExpressionType $logicalExpressionType
  ): void {
    $this->tokensList->add(
      match( $logicalExpressionType ){
        LogicalExpressionType::Group => new LogicalExpressionGrupo(
          $this->slice( $this->cursorStart, $this->cursor )
        ),
        LogicalExpressionType::Compare => new LogicalExpressionCompare( 
          $this->slice( $this->cursorStart, $this->cursor )
        ),
        LogicalExpressionType::SubQuery => new LogicalExpressionSubQuery( 
          $this->slice( $this->cursorStart, $this->cursor )
        )
      }
    );  
  }

  private function createTokensGroup(
  ): void {
    $this->createTokenList( 
      LogicalExpressionType::Group
    );
  }

  private function createTokensCompareGroup(
  ): void {
    $this->createTokenList( 
      LogicalExpressionType::Compare
    );
  }

  private function createTokensCompareSubQuery(
  ): void {
    $this->createTokenList(
      LogicalExpressionType::SubQuery
    );
  }

  private function createTokensGroups(
  ): void {
    $this->cursorMoveToNextCloseFN();
    $this->createTokensGroup();
  }   

  private function createTokensCompare(
  ): void {
    if( $this->cursor === $this->cursorStart ){
      $this->cursor = $this->tokens->count();
    }

    $this->createTokensCompareGroup();
    $this->cursorStartNext();
  }

  private function createTokensCompareFN(
  ): void {
    $this->cursorMove( T_DOUBLE_ARROW );
    $this->cursorPrev();
    $this->cursorMoveToNextCloseFN();
    $this->createTokensCompareSubQuery();
    $this->cursorNext();
    $this->cursorStartNext();
  }  

  private function startupsAnalyzeds(
  ): void {
    $this->createParameters();
    $this->gotToWheres();

    while( $this->isEof() ){
      if( $this->getToken() instanceof TokenSimple ){
        if( $this->isGroupExist()){
          $this->createTokensGroups();
        }
      } else
      if( $this->getToken() instanceof Token ){
        if( $this->getToken()->isLogical()){
          $this->isGroupExist() 
            ? $this->createTokensGroups()
            : $this->createTokensCompare();
        } else
        if( $this->getToken()->isFN()){
          $this->createTokensCompareFN();
        }
      }

      $this->cursorNext(); 
    }
  }

  private function startupsClear(
  ): void {
    unset( $this->tokens );
  }
}

$leftTimer = number_format(( microtime( true ) - $start ) * 1000, 6, ",", "." );
echo "Execute timer: {$leftTimer}(ms)" . PHP_EOL . PHP_EOL;


// $WhereTokens = new WhereTokens(
//   ExtractScriptFromFN::get( $fn )->tokens, WhereTokensType::Initial
// );


print_r( ExtractScriptFromFN::get( $fn )->tokens );
// print_r( $WhereTokens->parameterList );
// print_r( $WhereTokens );