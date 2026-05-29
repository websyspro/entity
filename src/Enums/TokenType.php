<?php

namespace Websyspro\Commons\Enums;

enum Token:string 
{
  case ExpressionNode = "ExpressionNode";
  case ExpressionGroup = "ExpressionGroup";
  case ExpressionSubQuery = "ExpressionSubQuery";
  case ExpressionLogical = "ExpressionLogical";
  case ExpressionUnary = "ExpressionUnary";
  case ExpressionCompare = "ExpressionCompare";
  case ExpressionBetween = "ExpressionBetween";
  case ExpressionLike = "ExpressionLike";
  case ExpressionNotLike = "ExpressionNotLike";
  case ExpressionIn = "ExpressionIn";
  case ExpressionNotIn = "ExpressionNotIn";
  case ExpressionNegative = "ExpressionNegative";
}