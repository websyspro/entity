<?php

namespace Websyspro\Test\Edocente\Enums;

enum PostStatus:string
{
  case Publish = 'publish';
  case Future = 'future';
  case Draft = 'draft';
  case Pending = 'pending';
  case Private = 'private';
  case Trash = 'trash';
  case AutoDraft = 'auto-draft';
  case Inherit = 'inherit';
}