<?php

namespace Websyspro\Entity\Shareds;

use Closure;

class ExpressionColumns
extends ExpressionAbstract
{
  private function getCache(
  ): string {
    $cacheClassKey = md5($this->cacheClassKey);
    $cacheMethodKey = md5($this->cacheMethodKey);
    return "orm-select-$cacheClassKey-$cacheMethodKey";
  }

  private function getBuildsDirect(
  ): void {
    $this->scopes = $this->getScopesByContext($this->contexts);
    $this->tokens = $this->getTokensByContext($this->contexts);

    Cache::save(
      $this->getCache(), [
        'hash' => md5( serialize( $this->contexts)),
        'context' => [
          'scopes' => $this->scopes,
          'tokens' => $this->tokens
        ]
      ]
    );

    $this->getBuildClear();
  }  

  private function getBuilds(
  ): void {
    if($this->isPossibleToCache()){
      if(Cache::exist($this->getCache())){
        [ 'hash' => $hash, 'context' => $context 
        ] = Cache::load( $this->getCache());
        if( $hash === md5( serialize( $this->contexts ))){
          $this->scopes = $context['scopes'];
          $this->tokens = $context['tokens'];
          print_r( $this );
          $this->getBuildClear();
        } else $this->getBuildsDirect();
      } else $this->getBuildsDirect();
    } else $this->getBuildsDirect();
  }  

  private function startups(
  ): void {
    $this->getFileRows();
    $this->getCacheKey();
    $this->getUsesRows();
    $this->getContexts();
    $this->getBuilds();
  }

  public function get(
  ): array {
    $this->startups();
    return [];
  }
}