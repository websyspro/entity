<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\MetaType;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use ReflectionFunction;
use Websyspro\Entity\Interfaces\RepositoryStructure;

class AbstractRepository
extends UtilsRepository
{
  public Collection $rows;
  public UseList $useList;
  public Collection $includes;
  public WhereList $whereList;
  public static Collection $cacheEntityStructure;

  public function __construct(
    string $entity
  ){}
  
  public function select(
    callable $fn   
  ): AbstractRepository {
    $reflectionFunction = $this->startup( $fn );
      
    return $this;
  }

  public function include(
    callable $fn   
  ): AbstractRepository {
    $reflectionFunction = $this->startup( $fn );

    if( $reflectionFunction instanceof ReflectionFunction ){
      $this->includes = $this->normalizedScriptInclude(
        $this, $reflectionFunction, $this->readScripByFunc(
          $this->rows, $reflectionFunction
        )
      );

    }

    return $this;
  }  

  public function where(
    callable $fn   
  ): AbstractRepository {
    $reflectionFunction = $this->startup( $fn );

    if( $reflectionFunction instanceof ReflectionFunction ){
      $this->whereList = $this->normalizedScriptWhere(
        $this, $reflectionFunction, $this->readScripByFunc( 
          $this->rows, $reflectionFunction
        )
      );
    }

    return $this;
  }  

  public function groupBy(
    callable $fn   
  ): AbstractRepository {
    return $this;
  }

  public function entityStructure(
    string $entity
  ): EntityStructure|null {
    if( isset( AbstractRepository::$cacheEntityStructure ) === false ){
      AbstractRepository::$cacheEntityStructure = new Collection();
    }

    if( class_exists( $entity )){
      if( AbstractRepository::$cacheEntityStructure->getOneOrFail( $entity ) instanceof EntityStructure ){
        return AbstractRepository::$cacheEntityStructure->getOneOrFail( $entity );
      }

      $entityStructure = call_user_func_array(
        [ $entity, "meta" ], [ MetaType::Query ]
      );

      if( $entityStructure instanceof EntityStructure ){
        AbstractRepository::$cacheEntityStructure->add(
          $entityStructure, $entity
        );

        return $entityStructure;
      }
    }
    
    return null;
  }

  public function getStructure(
  ): RepositoryStructure {
    return new RepositoryStructure(
      $this->includes, $this->whereList
    );
  }

  private function startupRows(
    ReflectionFunction $rf  
  ): void {
    if( isset( $this->rows ) === false ){
      $this->rows = $this->readRowsFromScript( $rf );
    }
  }  

  private function startupUses(
  ): void {
    if( isset( $this->uses ) === false ){
      $this->useList = new UseList(
        $this->rows
          ->where( fn( string $use ) => Util::match( "#^\s*use\s*#", $use ))
          ->mapper( fn( string $use ) => Util::replace( "#(^\s*use\s*)|(;\s*$)#", $use ))
          ->mapper( fn( string $use ) => new UseItem( $use ))
          ->toArray()
      );
    }
  }
  
  private function startup(
    callable $fn
  ): ReflectionFunction {
    $reflectionFunction = new ReflectionFunction( $fn );
    
    if( $reflectionFunction instanceof ReflectionFunction ){
      $this->startupRows( $reflectionFunction );
      $this->startupUses();
    }

    return $reflectionFunction;
  }
}