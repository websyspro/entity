<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Websyspro\Entity\Decorations\BaseEntity;
use Websyspro\Entity\Decorations\ColumnName;
use Websyspro\Entity\Decorations\Columns\Date;
use Websyspro\Entity\Decorations\Columns\Datetime;
use Websyspro\Entity\Decorations\Columns\Decimal;
use Websyspro\Entity\Decorations\Columns\Enum;
use Websyspro\Entity\Decorations\Columns\Flag;
use Websyspro\Entity\Decorations\Columns\LongText;
use Websyspro\Entity\Decorations\Columns\Number;
use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Decorations\Columns\Time;
use Websyspro\Entity\Decorations\Constraints\ForeignKey;
use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Decorations\Constraints\Unique;
use Websyspro\Entity\Decorations\EntityName;
use Websyspro\Entity\Decorations\Generations\AutoIncrement;
use Websyspro\Entity\Decorations\Requireds\NotNull;
use Websyspro\Entity\Decorations\Statistics\Index;
use function in_array, count, sprintf;

/* defined consts to objects */
define( 'T_Entity', 'entity' );
define( 'T_Columns', 'columns' );
define( 'T_Types', 'types' );
define( 'T_Alias', 'alias' );
define( 'T_Indexes', 'indexes' );
define( 'T_Uniques', 'uniques' );
define( 'T_Foreign_Keys', 'foreign_keys' );
define( 'T_Primary_Keys', 'primary_keys' );
define( 'T_Not_Nulls', 'not_nulls' );
define( 'T_Auto_Increments', 'auto_increments' );  


class EntityStructure
extends ExpressionUtils
{
  public ReflectionClass $reflectionClassBase;
  public ReflectionClass $reflectionClassChild;
  public array $attributes = [];
  public array $contexts = [];

  public function __construct(
    public string $class
  ){}

  private function getGroupByNumber(
    string $contextsLabel,
     array $contexts,
     array $contextsArr = []
  ): array {
    foreach($contexts as $key => $group){
      $contextsArr[$group][] = $key;
    }
    
    return $this->mapper(
      $contextsArr, fn(array $items) => sprintf(
        '%s_%s', $contextsLabel, join( '_', $items )
      )
    );
  }

  private function getReflection(
  ): void {
    $this->reflectionClassBase = new ReflectionClass(BaseEntity::class);
    $this->reflectionClassChild = new ReflectionClass($this->class);
  }

  private function getReflectionAttributes(
  ): void {
    $properties = $this->reflectionClassChild
      ->getProperties(ReflectionProperty::IS_PUBLIC);

    if( empty($properties) === false ){
      foreach($properties as $property){
        $attributes = $property->getAttributes();

        if( empty($attributes) === false ){
          foreach($attributes as $attribute){
            $this->attributes[] = [$property, $attribute];
          }
        }
      }
    }
  }

  private function getReflectionEntity(
  ): void {
    $attributeEntityNameArr = $this->reflectionClassChild
      ->getAttributes(EntityName::class);

    if(count($attributeEntityNameArr) === 1){
      [ $entityName ] = $attributeEntityNameArr;

      if($entityName instanceof ReflectionAttribute){
        $entityNameInstance = $entityName->newInstance();
      
        if($entityNameInstance instanceof EntityName){
          [ $entityNameInstanceTable ] = array_reverse(
            explode( '\\', $this->class )
          );

          $this->contexts[T_Entity] = [ 
            $entityNameInstance->name, str_replace(
              'Entity', '', $entityNameInstanceTable
            )
          ];
        }
      }
    } else {
      [ $entityNameInstanceTable ] = array_reverse(
        explode( '\\', $this->class )
      );

      $this->contexts[T_Entity] = [ 
        str_replace( 'Entity', '', $entityNameInstanceTable),
        str_replace( 'Entity', '', $entityNameInstanceTable)
      ];
    }  
  }

  private function getColumns(
    ReflectionClass $reflectionClass
  ): array {
    return $this->mapper(
      $reflectionClass->getProperties(
        ReflectionProperty::IS_PUBLIC
      ), fn( ReflectionProperty $p ) => $p->name
    );
  }

  private function getReflectionColumns(
    array $columnsBase = [],
    array $columnsChilds = []
  ): void {
    $columnsBase = $this->getColumns(
      $this->reflectionClassBase
    );
    
    $columnsChilds = $this->getColumns(
      $this->reflectionClassChild
    );
    
    $this->contexts[T_Columns] = array_merge(
      $this->where( $columnsBase,
        fn(string $column) => 
          $column === reset($columnsBase)),
      $this->where( $columnsChilds,
        fn(string $column) =>
          !in_array($column, $columnsBase)),
      $this->where( $columnsBase,
        fn(string $column) =>
          $column !== reset($columnsBase))
    );
  }

  private function getReflectionTypes(
  ): void {
    foreach($this->attributes as $attribute){
      $isColumnType = in_array( $attribute[1]->getName(), [
        Date::class, Datetime::class, Time::class,
        Decimal::class, Number::class,
        Text::class, LongText::class,
        Enum::class, Flag::class,
      ]);

      if( $isColumnType === true ){
        $this->contexts[T_Types][
          $attribute[0]->name
        ] = $attribute[1]->getName();
      }
    }
  }

  private function getReflectionByAttribute(
    string $findAttribute,
      bool $isNewInstance = false,
     array $attributesArr = [] 
  ): array {
    foreach($this->attributes as $attribute){
      if( $attribute[1]->getName() === $findAttribute ){
        $attributesArr[$attribute[0]->name] = $isNewInstance 
          ? $attribute[1]->newInstance() : $attribute[1];
      }
    }

    return $attributesArr;
  } 
  
  private function getReflectionAlias(
  ): void {
    $this->contexts[T_Alias] = $this->mapper(
      $this->getReflectionByAttribute(
        ColumnName::class, true
      ), fn(ColumnName  $columnName ) => $columnName->columnName
    );
  }  

  private function getReflectionIndex(
  ): void {
    $this->contexts[T_Indexes] = $this->mapper(
      $this->getReflectionByAttribute(
        Index::class, true
      ), fn(Index $index) => $index->indexGroup 
    );

    $this->contexts[T_Indexes] = $this->getGroupByNumber(
      T_Indexes, $this->contexts[T_Indexes]
    );    
  }

  private function getReflectionUniques(
  ): void {
    $this->contexts[T_Uniques] = $this->mapper(
      $this->getReflectionByAttribute(
        Unique::class, true
      ), fn(Unique $unique) => $unique->uniqueGroup 
    );

    $this->contexts[T_Uniques] = $this->getGroupByNumber(
      T_Uniques, $this->contexts[T_Uniques]
    );
  } 
  
  private function getReflectionForeignKeys(
  ): void {
    // $this->contexts[T_Foreign_Keys] = $this->getReflectionByAttribute(
    //   ForeignKey::class, true
    // );

    // $this->contexts[T_Foreign_Keys] = $this->mapper(
    //   $this->contexts[T_Foreign_Keys], function(ForeignKey $foreignKey, string $key){
    //     $entityReference = new EntityStructure(
    //       $foreignKey->entityReference
    //     );

    //     return [ 
    //       $this->contexts[T_Entity][0], $key,
    //       $entityReference->get()->contexts[T_Entity][0],
    //       $entityReference->get()->contexts[T_Primary_Keys][0]
    //     ];
    //   }
    // );
  }

  private function getReflectionPrimaryKeys(
  ): void {
    // $this->contexts[T_Primary_Keys] = $this->mapper(
    //   $this->getReflectionByAttribute(
    //     PrimaryKey::class, false
    //   ), fn(mixed $_, string $key) => $key
    // );

    // $this->contexts[T_Primary_Keys] = array_values(
    //   $this->contexts[T_Primary_Keys]
    // );
  }

  private function getReflectionNotNulls(
  ): void {
    // $this->contexts[T_Not_Nulls] = $this->mapper(
    //   $this->getReflectionByAttribute(
    //     NotNull::class, false
    //   ), fn(mixed $_, string $key) => $key
    // );
  }
  
  private function getReflectionAutoIncrements(
  ): void {
    // $this->contexts[T_Auto_Increments] = $this->mapper(
    //   $this->getReflectionByAttribute(
    //     AutoIncrement::class, false
    //   ), fn(mixed $_, string $key) => $key
    // );
  } 
  
  private function getCache(
  ): string {
    return sprintf(
      "orm-entity-%s.php", md5(
        strtolower( $this->class)
      )
    );
  }

  private function startups(
  ): void {
    $this->getReflection();
    $this->getReflectionAttributes();
    $this->getReflectionEntity();
    $this->getReflectionColumns();
    $this->getReflectionTypes();
    $this->getReflectionAlias();
    $this->getReflectionIndex();
    $this->getReflectionUniques();
    $this->getReflectionForeignKeys();
    $this->getReflectionPrimaryKeys();
    $this->getReflectionNotNulls();
    $this->getReflectionAutoIncrements();
  }  

  public function get(
  ): mixed {
    $cacheFileDir = implode( 
      DIRECTORY_SEPARATOR, [
        BASEDIR_APP, "Cache", $this->getCache()
      ]
    );

    if( file_exists( $cacheFileDir )){
      $this->contexts = require_once $cacheFileDir;
    } else {
      $this->startups();
      file_put_contents( $cacheFileDir, sprintf(
          "<?php\n\nreturn %s;", var_export( $this->contexts, true )
        ), LOCK_EX 
      );
    }

    return $this->contexts;
  }  
}