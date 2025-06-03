<?php

namespace Websyspro\Entity\Core\Persisteds;

class TMySqlScript
{
  public static function Columns(
  ): string {
    return (
      "select information_schema.columns.table_name as 'table'
             ,information_schema.columns.column_name as 'name'
             ,information_schema.columns.column_type as 'type'
         from information_schema.columns
        where information_schema.columns.table_schema = 'Shop'
     order by information_schema.columns.table_name asc
             ,information_schema.columns.ordinal_position asc"
    ); 
  }

  public static function PrimaryKeys(
  ): string {
    return (
      "select information_schema.columns.table_name as 'table'
             ,information_schema.columns.column_name as 'name'
         from information_schema.columns
        where information_schema.columns.table_schema = 'Shop'
          and information_schema.columns.column_key = 'PRI'
     order by information_schema.columns.table_name asc
             ,information_schema.columns.ordinal_position asc"
    );
  }

  public static function Generations(
  ): string {
    return (
      "select information_schema.columns.table_name as 'table'
             ,information_schema.columns.column_name as 'name'
         from information_schema.columns
        where information_schema.columns.table_schema = 'Shop'
          and information_schema.columns.extra = 'auto_increment'
     order by information_schema.columns.table_name asc
             ,information_schema.columns.ordinal_position asc"
    );
  }

  public static function Requireds(
  ): string {
    return (
      "select information_schema.columns.table_name as 'table'
             ,information_schema.columns.column_name as 'name'
         from information_schema.columns
        where information_schema.columns.table_schema = 'Shop'
          and information_schema.columns.is_nullable = 'NO'
     order by information_schema.columns.table_name asc
             ,information_schema.columns.ordinal_position asc"
    );
  }

  public static function Uniques(
  ): string {
    return (
      "select information_schema.table_constraints.table_name as 'table'
             ,information_schema.table_constraints.constraint_name as 'name'
         from information_schema.table_constraints
        where information_schema.table_constraints.table_schema = 'Shop'
          and information_schema.table_constraints.constraint_type = 'UNIQUE'"
    );
  }

  public static function Statistics(
  ): string {
    return (
      "select information_schema.statistics.table_name as 'table'
             ,information_schema.statistics.index_name as 'name'
         from information_schema.statistics
        where information_schema.statistics.table_schema = 'Shop'
          and information_schema.statistics.index_name like 'INDEX_%'
          and information_schema.statistics.non_unique = 1
     group by information_schema.statistics.table_name
             ,information_schema.statistics.index_name"
    );
  }

  public static function ForeignKeys(
  ): string {
    return (
      "select information_schema.key_column_usage.table_name as 'table'
             ,information_schema.key_column_usage.constraint_name as 'name'
         from information_schema.key_column_usage 
        where information_schema.key_column_usage.table_schema = 'Shop'
          and information_schema.key_column_usage.referenced_table_name is not null"
    );
  }   
}