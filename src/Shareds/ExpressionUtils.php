<?php

namespace Websyspro\Entity\Shareds;

use Closure;

class ExpressionUtils
{
  public array $entitys = [];

  public function inc(
    int $number
  ): int {
    return ++$number;
  }

  public function dec(
    int $number,
    int $decNumner = 0
  ): int {
    return (--$number) - $decNumner;
  }

  public function slice(
    array $items,
    int $offset,
    int|null $length = null
  ): array {
    return array_slice( $items, $offset, $length );
  }  

  public function mapper(
    array $items,
    Closure $closure
  ): array {
    return array_map( $closure, $items );
  }

  public function where(
    array $items,
    Closure $closure
  ): array {
    return array_values( array_filter( $items, $closure ));
  }  

  public function indexOf(
    array $tokens,
    int $type
  ): int {
    foreach( $tokens as $cursor => $token ){
      if( $token[0] === $type ){
        return $cursor;
      }
    }

    return -1;
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
    $this->entitys[ $entity ] = $this->entitys[ $entity ]->get();
    
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
}