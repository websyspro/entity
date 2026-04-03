<?php

namespace Websyspro\Entity;

use ReflectionFunction;
use Websyspro\Entity\Enums\MetaType;
use Websyspro\Entity\Enums\MultiLine;
use Websyspro\Entity\Enums\Type;
use Websyspro\Entity\Interfaces\Join;
use Websyspro\Entity\Shareds\OrderByAsc;
use Websyspro\Entity\Shareds\OrderByDesc;
use Websyspro\Entity\Shareds\StructureFile;
use Websyspro\Entity\Shareds\EntityStructure;
use Websyspro\Entity\Shareds\Param;
use Websyspro\Entity\Shareds\Token;

class Repository
{
  public mixed $fn;
  public int $page;
  public int $rowsPerPage;
  public array $columnsPrimary = [];
  public array $columnsSecondary = [];
  public array $joinsPrimary = [];
  public array $joinsSimplesPrimary = [];
  public array $joinsSecondary = [];
  public array $joinsSimplesSecondary = [];
  public array $wheresPrimary = [];
  public array $wheresSecondary = [];
  public array $prepareds = [];
  public OrderByAsc $orderByAsc;
  public OrderByDesc $orderByDesc;  
  public StructureFile $structureFile;
  public EntityStructure $entityStructure;
  
  public function __construct(
    string $entity
  ){
    $this->entityStructure = call_user_func_array(
      [ $entity, "meta" ], [ MetaType::Query ]
    );
  }
  
  public function where(
    callable $fn
  ): Repository {
    $this->fn = $fn;
    return $this;
  }

  public function orderByAsc(
    callable $fn
  ): Repository {
    $this->orderByAsc = new OrderByAsc(
      new ReflectionFunction( $fn )
    );

    return $this;
  }

  public function orderByDesc(
    callable $fn
  ): Repository {
    $this->orderByDesc = new OrderByDesc(
      new ReflectionFunction($fn)
    );

    return $this;
  }

  public function paged(
    int $page,
    int $rowsPerPage = 12
  ): Repository {
    $this->page = $page;
    $this->rowsPerPage = $rowsPerPage;
    return $this;
  }  

  public function queryBuilder(
  ): Repository {
    $this->queryBuilderStructureFile();
    $this->queryBuilderSQL();
    return $this;
  }

  private function queryBuilderStructureFile(
  ): void {
    $this->structureFile = new StructureFile(
      new ReflectionFunction($this->fn)
    );
  }

  private function columnsFromPrimary(
  ): string {
    foreach( $this->entityStructure->columns as $column ){
      $this->columnsPrimary[] = sprintf(
        '%1$s.%2$s As %2$s', $this->entityStructure->entity->table, $column, $column
      );
    }
    
    return implode( ",", $this->columnsPrimary );
  }

  private function columnsFromSecondary(
  ): string {
    foreach( $this->structureFile->parameters as $parameter ){
      foreach( $parameter->entityStructure->columns as $column ){
        $this->columnsSecondary[] = sprintf( 
          '%1$s.%2$s As %1$s_%2$s', $parameter->entityStructure->entity->table, $column, $column
        );
      }
    }

    return implode( ",", $this->columnsSecondary );
  }

  private function joinsPrimary(
  ): string {
    if( isset( $this->entityStructure )){
      $this->joinsSimplesPrimary[] = $this->entityStructure->entity->table;
    }

    foreach( $this->structureFile->joins as $join ){
      if( $join instanceof Join && $join->multiLine === MultiLine::No ){
        $existsJoins = isset( $join->table ) && isset( $join->key );
        $existsJoinsReference = isset( $join->referenceTable ) && isset( $join->referenceKey );

        if( $existsJoins === false || $existsJoinsReference === false ){
          $this->joinsSimplesPrimary[] = $join->tableBase; 
          
          continue;
        }

        $this->joinsPrimary[] = sprintf(
          'Inner Join %1$s On %2$s.%3$s = %4$s.%5$s', $join->tableBase, $join->table, $join->key, $join->referenceTable, $join->referenceKey,
        );
      }
    }

    return sprintf( '%1$s %2$s', 
      implode( ",", $this->joinsSimplesPrimary ), 
      implode( " ", $this->joinsPrimary )
    );
  }
  
  private function joinsSecondary(
  ): string {
    if( isset( $this->entityStructure )){
      $this->joinsSimplesSecondary[] = $this->entityStructure->entity->table;
    }

    foreach( $this->structureFile->joins as $join ){
      if( $join instanceof Join ){
        $existsJoins = isset( $join->table ) && isset( $join->key );
        $existsJoinsReference = isset( $join->referenceTable ) && isset( $join->referenceKey );

        if( $existsJoins === false || $existsJoinsReference === false ){
          $this->joinsSimplesSecondary[] = $join->tableBase; 
          
          continue;
        }

        $this->joinsSecondary[] = sprintf(
          'Inner Join %1$s On %2$s.%3$s = %4$s.%5$s', $join->tableBase, $join->table, $join->key, $join->referenceTable, $join->referenceKey,
        );
      }
    }
    
    return sprintf( '%1$s %2$s', 
      implode( ",", $this->joinsSimplesSecondary ), 
      implode( " ", $this->joinsSecondary )
    );    
  }

  private function wheresPrimary(
  ): string|null {
    foreach( $this->structureFile->tokens as $i => $token ){
      if( $token instanceof Token && $token->multiLine === MultiLine::No ){
        if( $token->type === Type::Logical ){
          if( $this->structureFile->tokens[ $i - 1 ]->type === Type::StartGroup ){
            continue;
          }
        }

        if( empty( $this->wheresPrimary ) && $token->type === Type::Logical ){
          continue;
        }        

        $this->wheresPrimary[] = $token->value;
      }
    }

    if( empty( $this->wheresPrimary ) === false ){
      return sprintf( "Where %s", implode( " ", $this->wheresPrimary ));
    }

    return null;
  }

  private function wheresSecondary(
  ): string|null {
    foreach( $this->structureFile->tokens as $i => $token ){
      if( $token instanceof Token && $token->multiLine === MultiLine::Yes ){
        if( $token->type === Type::Logical ){
          if( $this->structureFile->tokens[ $i - 1 ]->type === Type::StartGroup ){
            continue;
          }
        }

        if( empty( $this->wheresSecondary ) && $token->type === Type::Logical ){
          continue;
        }

        $this->wheresSecondary[] = $token->value;
      }
    }

    if( empty( $this->wheresSecondary ) === false ){
      return sprintf( "Where %s", implode( " ", $this->wheresSecondary ));
    }

    return null;    
  }
  
  private function wheresOrderBy(
  ): void {}
  
  private function pagedPrimary(
  ): void {}

  private function setReplaceParams(
    array $matches
  ): string {
    [ $paramKey ] = $matches;
    $param = $this->structureFile->params[$paramKey];
    if( $param instanceof Param ){
      $this->prepareds[] = $param->value;  
    }

    return "?";
  }  
  
  private function queryBuilderSQLFormat(
  ): string {
    return "Select %s From ( Select %s From %s %s %s %s ) As %s %s";
  }  

  private function queryBuilderSQL(
  ): void {
    $sql = preg_replace_callback( 
      "#\:param_\d+#", fn( array $matches ) => (
        $this->setReplaceParams( $matches )
      ), 
      sprintf( 
        $this->queryBuilderSQLFormat(),
        $this->columnsFromSecondary(),
        $this->columnsFromPrimary(),
        $this->joinsPrimary(),
        $this->wheresPrimary(),
        "",
        "",
        $this->joinsSecondary(),
        $this->wheresSecondary()
      )
    );

    print_r( $sql );
  }
}