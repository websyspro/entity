<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Decorations\Mappers\SetMapper;
use Websyspro\Commons\DataList;
use ReflectionObject;
use ReflectionClass;
use stdClass;

class Mapper
{
  public function __construct(
    private string $sourceClass,
    private string $targetClass
  ){}

  public function isDataListClass(
    string $classListType
  ): bool {
    return class_exists($classListType) 
        && $classListType === DataList::class;
  }

  public function objectMapper(
    object $source,
    string $targetClass
  ): object {
    $reflectSourceObject = (
      new ReflectionObject($source)
    );

    $reflectTargetClass = (
      new ReflectionClass($targetClass)
    );

    $reflectTargetClassInstance = (
      $reflectTargetClass->newInstanceWithoutConstructor()
    );

    foreach($reflectTargetClass->getProperties() as $propertyFromTarget){     
      $propertyFromTargetIsNull = (
        $propertyFromTarget->getType()->allowsNull()
      );

      if(property_exists($source, $propertyFromTarget->getName())){
        $propertyFromSource = (
          $reflectSourceObject->getProperty(
            $propertyFromTarget->getName()
          )
        );

        $propertyIsEquals = (
          $propertyFromTarget->getName() === 
          $propertyFromSource->getName()
        );

        if($propertyIsEquals){
          $hasDataList = $this->isDataListClass(
            $propertyFromTarget->getType()->getName()
          );
          
          $attributesFromProperty = (
            $propertyFromTarget->getAttributes(
              SetMapper::class
            )
          );

          $setMappers = [];
 
          if($hasDataList){

            if(sizeof($attributesFromProperty) !== 0){
              [ $propertyTargetType ] = $attributesFromProperty;
              $setMappers = $propertyTargetType->newInstance()->mapper(); 
            } else {
              $setMappers = [
                stdClass::class,
                stdClass::class
              ];
            }

            if($propertyFromSource->getValue($source) instanceof DataList){
              $reflectTargetClassInstance->{
                $propertyFromTarget->getName()
              } = Mapper::to(...$setMappers)->from(
                $propertyFromSource->getValue($source)->all()
              );
            }

          } else {
            if(sizeof($attributesFromProperty) !== 0){
              [ $propertyTargetType ] = $attributesFromProperty;
              $setMappers = $propertyTargetType->newInstance()->mapper();

              $reflectTargetClassInstance->{
                $propertyFromTarget->getName()
              } = Mapper::to(...$setMappers)->from(
                $propertyFromSource->getValue($source)
              );
            } else {
              $isNotValueNull = is_null(
                $propertyFromSource->getValue($source)
              ) === false;

              if($isNotValueNull){
                $reflectTargetClassInstance->{
                  $propertyFromTarget->getName()
                } = $propertyFromSource->getValue($source);
              } else {
                /* O valor do source é NULL logo eu preciso verificar */
                /* O target prperty aceita NULL */
                if($propertyFromTargetIsNull){
                  $reflectTargetClassInstance->{
                    $propertyFromTarget->getName()
                  } = $propertyFromSource->getValue($source);
                } else {
                  /* Deve mostrar um error 500 */
                  /* ERROR:: Mostar que target property is not null */      
                }
              }
            }
          }
        } else {
          /* O target prperty aceita NULL */
          if($propertyFromTargetIsNull === false){
            /* Deve mostrar um error 500 */
            /* ERROR:: Mostar que target property is not null */      
          }
        }       
      } else {
        /* O target prperty aceita NULL */
        if($propertyFromTargetIsNull === false){
          /* Deve mostrar um error 500 */
          /* ERROR:: Mostar que target property is not null */      
        }
      }
    }


    return $reflectTargetClassInstance;
  }
  
  public function from(
    object|array $source
  ): mixed {
    if(is_array($source)){
      $dataList = (
        DataList::create($source)
      )->mapper(fn(mixed $mixed) => (
        Mapper::to(
          $this->sourceClass,
          $this->targetClass
        )->from($mixed)
      ));
        
      return $dataList;
    }
      
    return $this->objectMapper(
      $source, $this->targetClass
    );
  }

  public static function to(
    string $sourceClass,
    string $targetClass
  ): Mapper {
    return new static(
      $sourceClass,
      $targetClass
    );
  }
}