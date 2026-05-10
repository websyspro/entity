<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Interfaces\ForeignKeyItem;
use Websyspro\Entity\Decorations\EntityList;
use Websyspro\Entity\Enums\MultiLine;
use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionClass;

class TargetItem
{
  public UseItem $useItem;
  public foreignKeyItem $itemForeignKey;

  public function __construct(
    AbstractRepository $abstractRepository,
    UseItem $useItem,
    string $target
  ){
    $this->startup( 
      $abstractRepository,
      $useItem,
      $target
    );
  }

  public function startup(
    AbstractRepository $abstractRepository,
    UseItem $useItem,
    string $target    
  ): void {
    [ $_, $property ] = explode( "->", $target );

    if( property_exists( $useItem->getPath(), $property )){
      $refClass = new ReflectionClass( $useItem->getPath());
      $property = $refClass->getProperty( $property );

      if( $property->getType() instanceof ReflectionNamedType ){
        $classFromProperty = $property->getType()->getName();

        if( $classFromProperty === EntityList::class ){
          [ $attribute ] = $property->getAttributes( EntityList::class );

          if( $attribute instanceof ReflectionAttribute ){
            $entity = $attribute->newInstance()->entity;

            if( is_string( $entity )){
              $this->useItem = new UseItem( $entity );
              $this->startupForeigns( $this->useItem, $useItem, MultiLine::Yes, $abstractRepository );
            }
          }
        } else {
          $this->useItem = new UseItem( $classFromProperty );
          $this->startupForeigns( $useItem, $this->useItem, MultiLine::No, $abstractRepository );
        }
      }
    }
  }

  private function startupForeigns(
    UseItem $useItemChild,
    UseItem $useItemParent,
    MultiLine $multiLine,
    AbstractRepository $abstractRepository
  ): void {
    $entityStructure = $abstractRepository->entityStructure(
      $useItemChild->getPath()
    );

    if( $entityStructure instanceof EntityStructure ){
      if( empty( $entityStructure->foreigns ) === false ){
        if( isset( $entityStructure->foreigns[ $useItemParent->getPath()])){
          $itemForeignKey = $entityStructure->foreigns[
            $useItemParent->getPath()
          ];

          if( $itemForeignKey instanceof ForeignKeyItem ){
            $this->itemForeignKey = $multiLine == MultiLine::Yes
              ? $itemForeignKey : new ForeignKeyItem(
                $itemForeignKey->referenceTable,
                $itemForeignKey->referenceKey,
                $itemForeignKey->table,
                $itemForeignKey->key
              );
          }
        }
      }
    }

  }
}