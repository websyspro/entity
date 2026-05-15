<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;

class CompareUtil
{
  public static function analyzed(
    ExpressionCompare $expressionCompare,
    Collection $tokens
  ): array {
    $tokenVariable = $tokens->getOneOrFail(
      ExpressionUtil::find( $tokens, T_VARIABLE )
    );

    if( $tokenVariable instanceof Token ){
      $scope = ClosureUtil::scopeByVariable( 
        $expressionCompare->scopes, $tokenVariable->value
      );

      if( $scope instanceof Scope ){
        if( $scope->entity instanceof Entity ){
          $tokenField = $tokens->getOneOrFail(
            ExpressionUtil::find( $tokens, T_OBJECT_OPERATOR ) + 1
          );
          
          if( $tokenField instanceof Token ){
            $entityStructure = ClosureUtil::getEntityStructure( $scope->entity->class );
            if( Util::inArray( $tokenField->value, $entityStructure->columns )){
              return [ $scope->entity, new Field( $tokenField->value, $entityStructure->alias[ $tokenField->value ] ?? $tokenField->value ),
                $entityStructure->types[ $tokenField->value ]->columnType
              ];
            }
          }
        }
      }
    }
    
    return [];
  }
}
