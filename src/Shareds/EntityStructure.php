<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
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
use function in_array, count, is_array, is_object;

class EntityStructure
{
  public ReflectionClass $reflectionClass;
  public array $attributes = [];
  public array $contexts = [];

  public function __construct(
    public string $class
  ){}

  public static function where(
    array|object $array,
    Closure $closure,
    array $arrayFromArry = []
  ): array {
    foreach($array as $key => $val){
      if(is_numeric($key)){
        $closure($val, $key) ? $arrayFromArry[] = $val : [];
      } else {
        $closure($val, $key) ? $arrayFromArry[$key] = $val : [];
      }
    }

    unset( $array );
    return $arrayFromArry;
  }

  public static function map(
    array|object $array,
    Closure $closure
  ): array|object {
    if(is_array($array)){
      foreach($array as $key => $val){
        $array[$key] = $closure($val, $key);
      }
    } else
    if(is_object($array)){
      foreach($array as $key => $val){
        $array->{$key} = $closure($val, $key);
      }      
    }

    unset( $closure );
    return $array;
  }  

  private function getReflection(
  ): void {
    $this->reflectionClass = new ReflectionClass($this->class);
  }

  private function getReflectionAttributes(
  ): void {
    $properties = $this->reflectionClass->getProperties(
      ReflectionProperty::IS_PUBLIC
    );

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
    $attributeEntityNameArr = $this->reflectionClass
      ->getAttributes(EntityName::class);

      if(count($attributeEntityNameArr) === 1){
        [ $entityName ] = $attributeEntityNameArr;

        if($entityName instanceof ReflectionAttribute){
        $entityNameInstance = $entityName->newInstance();
        
        if($entityNameInstance instanceof EntityName){
          [ $entityNameInstanceTable ] = array_reverse(
            explode( '\\', $this->class )
          );

          $this->contexts['entity'] = [ 
            $entityNameInstance->name, str_replace(
              "Entity", "", $entityNameInstanceTable
            )
          ];
        }
      }
    }  
  }

  private function getReflectionTypes(
  ): void {
    foreach($this->attributes as $attribute){
      $isColumnType = in_array($attribute[1]->getName(), [
        Date::class, Datetime::class, Time::class,
        Decimal::class, Number::class,
        Text::class, LongText::class,
        Enum::class, Flag::class,
      ]);

      if( $isColumnType === true ){
        $this->contexts['types'][
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
          ? $attribute[1]->newInstance() 
          : $attribute[1];
      }
    }

    return $attributesArr;
  } 
  
  private function getReflectionAlias(
  ): void {
    $this->contexts['alias'] = $this->map(
      $this->getReflectionByAttribute(
        ColumnName::class, true
      ), fn() => 1
    );
  }  

  private function getReflectionIndex(
  ): void {
    $this->contexts['indexes'] = $this->getReflectionByAttribute(
      Index::class, true
    );
  }

  private function getReflectionUniques(
  ): void {
    $this->contexts['uniques'] = $this->getReflectionByAttribute(
      Unique::class, true
    );
  } 
  
  private function getReflectionForeignKeys(
  ): void {
    $this->contexts['foreignKeys'] = $this->getReflectionByAttribute(
      ForeignKey::class, true
    );
  }

  private function getReflectionPrimaryKeys(
  ): void {
    $this->contexts['primaryKeys'] = $this->map(
      $this->getReflectionByAttribute(
        PrimaryKey::class, false
      ), fn() => true
    );
  }

  private function getReflectionNotNulls(
  ): void {
    $this->contexts['notNulls'] = $this->map(
      $this->getReflectionByAttribute(
        NotNull::class, false
      ), fn() => true
    );
  }
  
  private function getReflectionAutoIncrements(
  ): void {
    $this->contexts['autoIncrements'] = $this->map(
      $this->getReflectionByAttribute(
        AutoIncrement::class, false
      ), fn() => true
    );
  }  

  private function startups(
  ): void {
    $this->getReflection();
    $this->getReflectionAttributes();
    $this->getReflectionEntity();
    $this->getReflectionTypes();
    $this->getReflectionAlias();
    $this->getReflectionIndex();
    $this->getReflectionUniques();
    $this->getReflectionForeignKeys();
    $this->getReflectionPrimaryKeys();
    $this->getReflectionNotNulls();
    $this->getReflectionAutoIncrements();

    /* clear variable(s) */
    unset($this->reflectionClass);
    unset($this->attributes);
    unset($this->entity);
    unset($this->class);
  }  

  public function get(
  ): mixed {
    $this->startups();
    return $this;
  }  
}