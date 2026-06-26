<?php

namespace Websyspro\Entity\Shareds;

use function count;

class ExpressionSelect
extends Utils
{
  public array $contexts = [];
  public array $params = [];

  public function __construct(
    public string $signary,
    public string $hash,
    public int $signaryId,
    public array $statements,
    public array $statics,
    public array $scopes = [],
    public array $tokens = [],
  ){}

  public function analysisLexicalSave(
  ): void {
    Cache::save( "orm-select-{$this->signary}", [
      T_HASH => $this->hash,
      T_CONTEXTS => $this->contexts
    ]);
  }  

  public function analysisLexicalFromCache(
  ): array {
    return Cache::load( "orm-select-{$this->signary}" );
  }  

  public function analysisLexicalExistCache(
  ): string {
    if( Cache::exist( "orm-select-{$this->signary}" )){
      [ T_HASH => $hash ] = $this->analysisLexicalFromCache();
      return $this->hash === $hash;
    } 

    return false;
  }

  public function analysisLexicalSemanticsSeparator(
    array $tokens
  ): array {
    return [
      T_OBJECT => T_SEL_SEPARETOR,
      T_VALUES => $tokens[0][T_TOKEN_VALUE]
    ];
  }
  
  public function analysisLexicalSemanticsField(
    array $scopes,
    array $tokens
  ): array {
    return [
      T_OBJECT => T_SEL_FIELD,
      T_VALUES => $this->createField(
        $scopes, $tokens
      )
    ];
  }  

  public function analysisLexicalSemanticsApply(
    array $scopes,
    array $contexts
  ): array {
    return $this->mapper( 
      $this->groupByTypesComma( $contexts ), fn( array $tokens ) => (
        match( $this->getSelType( $tokens )){
          T_SEL_SEPARETOR => $this->analysisLexicalSemanticsSeparator( $tokens ),
          T_SEL_FIELD => $this->analysisLexicalSemanticsField( $scopes, $tokens ),
            default => $tokens
        }
      )
    );
  }
  
  public function analysisLexicalSemantics(
  ): void {
    $this->contexts = $this->analysisLexicalSemanticsApply(
      $this->scopes, $this->tokens
    );
  }

  public function analysisLexical(
  ): array {
    $this->analysisLexicalSemantics();
    $this->analysisLexicalSave();
    return [ T_CONTEXTS => $this->contexts ]; 
  } 
  
  public function analysisLexicalInitial(
  ): array {
    [ T_CONTEXTS => $this->contexts ] = $this->analysisLexicalExistCache() 
      ? $this->analysisLexicalFromCache()
      : $this->analysisLexical();


    return [ $this->contexts, []];
  }
}