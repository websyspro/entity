<?php 

namespace Websyspro\Test\Crm\Entitys;

use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Decorations\Requireds\NotNull;
use Websyspro\Entity\Decorations\Constraints\Unique;
use Websyspro\Entity\Decorations\Columns\Datetime;
use Websyspro\Entity\Decorations\Columns\Number;
use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Decorations\Columns\Flag;
use Websyspro\Entity\Shareds\AbstractEntity;

class ColecaoEntity 
extends AbstractEntity
{ 
  #[Text(36)]
  #[Unique(1)]
  #[PrimaryKey()]
	public string $Id;

  #[NotNull()]
  #[Datetime()]
	public string $Created;
  
  #[NotNull()]
  #[Number()]
	public string $CreatedById;

  #[NotNull()]
  #[Datetime()]
	public string $Updated;

  #[NotNull()]
  #[Number()]  
	public string $UpdatedById;

  #[Text(100)]
  public string $Nome;

  #[Text(36)]
	public string $EditoraId;


  #[Text(36)]
	public string $SegmentoId;

  #[Text(36)]
	public string $DisciplinaId;

  #[Flag()]
	public string $IsActive;

  #[Flag()]
	public string $IsDeleted;

  #[Flag()]
	public string $Sincronizado;

  #[NotNull()]
  #[Datetime()]
	public string $UltimaSincronizacao;
}