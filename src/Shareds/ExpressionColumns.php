<?php

namespace Websyspro\Entity\Shareds;

use function array_slice;

class ExpressionColumns
extends ExpressionAbstract
{
  public function getCache(
  ): string {
    $cacheClassKey = md5($this->cacheClassKey);
    $cacheMethodKey = md5($this->cacheMethodKey);
    return "orm-select-$cacheClassKey-$cacheMethodKey";
  }

  public function getVerifyExistsParenteses(
    array $contexts = []
  ): array {
    [ $tokenStart, $tokenEnd ] = [
      ...array_slice( $contexts, 0, 1 ),
      ...array_slice( $contexts, -1, 1 )
    ];

    if( $tokenStart[0] === T_START_BRACKET ){
      if( $tokenEnd[0] === T_END_BRACKET ){
        return array_slice( $contexts, 1, -1 );
      }
    }

    return $contexts;
  }

  public function getGroupByComma(
    array $contexts = []
  ): array {
    return $this->groupByTypes(
      $contexts, [ T_COMMA ]
    );
  }

  public function getParserFields(
    array $contexts = []
  ): array {
    foreach( $contexts as $i => $tokens ){
      [ $variable, $_, $field
      ] = $tokens;
      
      [ $scopes ] = array_values(
        array_filter( $this->scopes, 
          fn(array $scope) => (
            $scope[2] === $variable[1]
          )
        )
      );

      [ $instance, $table
      ] = $scopes;

      $contexts[$i] = [
        T_EXP_FIELD, $table, $field[1], $this->getFieldMethods( $tokens )
      ];
    }

    return $contexts;
  }  

  public function getParserValues(
    array $contexts = []
  ): array {
    $contexts = $this->getVerifyExistsParenteses( $contexts );
    $contexts = $this->getGroupByComma( $contexts );
    $contexts = $this->getParserFields( $contexts );
    print_r($contexts);
    return $contexts;
  }

  public function get(
  ): array {
    $this->startups();
    return [];
  }
}