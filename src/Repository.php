<?php

namespace Websyspro\Entity;

use ReflectionFunction;
use Websyspro\Entity\Core\Database;
use Websyspro\Entity\Enums\DriverType;
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
  public string $sql;
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

  public function get(
  ): array {
    return Database::query( $this->sql, $this->prepareds );
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
      $this->columnsPrimary[] = sprintf( '%1$s.%2$s As %3$s', 
        $this->entityStructure->entity->table, 
        $this->entityStructure->alias[ $column ] ?? $column,
        $this->entityStructure->alias[ $column ] ?? $column
      );
    }
    
    return implode( ",", $this->columnsPrimary );
  }

  private function columnsFromSecondary(
  ): string {
    foreach( $this->structureFile->parameters as $parameter ){
      foreach( $parameter->entityStructure->columns as $column ){
        $this->columnsSecondary[] = sprintf( '%1$s.%3$s As %2$s_%4$s', 
          $parameter->entityStructure->entity->table,
          $parameter->entityStructure->entity->alias, 
          $parameter->entityStructure->alias[ $column ] ?? $column, $column
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
  
  private function orderByPrimary(
  ): string|null {
    return null;
    // if( $this->orderBy->exist() === false ){
    //   if( $this->entityStructure->primaryKey->exist()){
    //     $orderByFromPrimaryKeys = $this->entityStructure->primaryKey->mapper(
    //       fn( PrimaryKey $primaryKey ) => Util::sprintFormat( "%s.%s Asc", [  
    //         $this->entityStructure->entity->table, $primaryKey->name
    //       ])
    //     );

    //     return Util::sprintFormat(
    //       "Order By %s", [ $orderByFromPrimaryKeys->joinWithComma() ]
    //     );
    //   } else {
    //     $orderByFromColumns = $this->entityStructure->columns->slice(0, 1)->mapper(
    //       fn( string $primaryKey ) => Util::sprintFormat( "%s.%s Asc", [  
    //         $this->entityStructure->entity->table, $primaryKey 
    //       ])
    //     );

    //     return Util::sprintFormat(
    //       "Order By %s.%s Asc", [ $orderByFromColumns->joinWithComma() ]
    //     );        
    //   }
    // }

    // return Util::sprintFormat(
    //   "Order By %s", [ $this->orderBy->joinWithComma() ]
    // );
  }
  
  private function pagedPrimary(
  ): string|null {
    $this->page = isset( $this->page ) === false ? 1 : $this->page;
    $this->rowsPerPage = isset( $this->rowsPerPage ) === false ? 12 : $this->rowsPerPage;

    return match( DriverType::tryFrom( Database::getDriver())){
      DriverType::MySql => sprintf( "Limit %s, %s", ( $this->page - 1 ) * $this->rowsPerPage, $this->rowsPerPage ),
      DriverType::SqlServer => sprintf( "Offset %s Rows Fetch Next %s Rows Only", ( $this->page - 1 ) * $this->rowsPerPage, $this->rowsPerPage ),
      DriverType::PostgreSQL => sprintf( "Limit %s Offset %s", $this->rowsPerPage, ( $this->page - 1 ) * $this->rowsPerPage ),
        
      default => null
    };    
  }

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
    return "Select %s From ( Select %s From %s %s %s %s ) As %s %s Limit 12";
  }  

  private function queryBuilderSQL(
  ): void {
    $this->sql = preg_replace_callback( 
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

    print_r( $this->sql );
  }
}