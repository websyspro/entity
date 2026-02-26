<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\EntityRoot;

class HierarchyJoin {
  /**
   * @param Entity|null $entity Nome da entidade atual
   * @param Entity|null $entityParent Nome da entidade pai na hierarquia
   * @param array|null $entityHistory Histórico de tipos de relacionamento (oneToOne, oneToMany)
   * @param EntityRoot|null $entityRoot Indica se a entidade é raiz na hierarquia
   * @param ForeignKey|null $entityForeignKey Informações do JOIN com a entidade pai
   */
  public function __construct(
    public Entity|null $entity,
    public Entity|null $entityReference = null,
    public array|null $entityHistory = null,
    public EntityRoot|null $entityRoot = EntityRoot::Yes,
    public ForeignKey|null $entityForeignKey = null
  ){}
}