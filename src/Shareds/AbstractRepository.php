<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use ReflectionFunction;
use Websyspro\Entity\Enums\MetaType;

class AbstractRepository
extends UtilsRepository
{
  public Collection $rows;
  public UseList $useList;
  public Collection $includes;
  public Collection $wheres;
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
      $includes = $this->normalizedScriptInclude(
        $this, $this->readScripByFunc( $this->rows, $reflectionFunction )
      );

      if( isset( $this->includes ) === false ){
        $this->includes = new Collection();
      }

      $this->includes->merge( $includes );
    }

    return $this;
  }  

  public function where(
    callable $fn   
  ): AbstractRepository {
    $reflectionFunction = $this->startup( $fn );

    if( $reflectionFunction instanceof ReflectionFunction ){
      $wheres = $this->normalizedScriptWhere(
        $this, $this->readScripByFunc( $this->rows, $reflectionFunction )
      );

      if( isset( $this->wheres ) === false ){
        $this->wheres = new Collection();
      }

      $this->wheres->merge( $wheres );
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