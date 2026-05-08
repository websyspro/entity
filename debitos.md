1. any() → EXISTS

PRIORIDADE: ⭐⭐⭐⭐⭐

PHP
$i->items->any(
   fn($o) => $o->IsActive
)
SQL
exists (
   select 1
     from Item
    where Item.ParentId = Parent.Id
      and Item.IsActive = 1
)
Observações

Essa é a base de tudo.

2. !any() → NOT EXISTS

PRIORIDADE: ⭐⭐⭐⭐⭐

PHP
!$i->items->any(
   fn($o) => $o->IsActive
)
SQL
not exists (
   select 1
     from Item
    where Item.ParentId = Parent.Id
      and Item.IsActive = 1
)
Observações

Muito comum.

3. count()

PRIORIDADE: ⭐⭐⭐⭐⭐

PHP
$i->items->count() > 5
SQL
(
   select count(*)
     from Item
    where Item.ParentId = Parent.Id
) > 5
4. count(predicate)

PRIORIDADE: ⭐⭐⭐⭐⭐

PHP
$i->items->count(
   fn($o) => $o->IsActive
) > 5
SQL
(
   select count(*)
     from Item
    where Item.ParentId = Parent.Id
      and Item.IsActive = 1
) > 5
5. sum()

PRIORIDADE: ⭐⭐⭐⭐⭐

PHP
$i->items->sum(
   fn($o) => $o->Valor
) > 1000
SQL
(
   select sum(Item.Valor)
     from Item
    where Item.ParentId = Parent.Id
) > 1000
Observações

MUITO importante para relatórios.

6. avg()

PRIORIDADE: ⭐⭐⭐⭐

PHP
$i->items->avg(
   fn($o) => $o->Valor
)
SQL
(
   select avg(Item.Valor)
     from Item
    where Item.ParentId = Parent.Id
)
7. min()

PRIORIDADE: ⭐⭐⭐⭐

PHP
$i->items->min(
   fn($o) => $o->Valor
)
SQL
(
   select min(Item.Valor)
     from Item
    where Item.ParentId = Parent.Id
)
8. max()

PRIORIDADE: ⭐⭐⭐⭐

PHP
$i->items->max(
   fn($o) => $o->Valor
)
SQL
(
   select max(Item.Valor)
     from Item
    where Item.ParentId = Parent.Id
)
9. all()

PRIORIDADE: ⭐⭐⭐⭐

PHP
$i->items->all(
   fn($o) => $o->IsActive
)
SQL REAL
not exists (
   select 1
     from Item
    where Item.ParentId = Parent.Id
      and Item.IsActive = 0
)
Observações IMPORTANTES

ALL geralmente vira:

NOT EXISTS(condição inversa)
10. first()

PRIORIDADE: ⭐⭐⭐

PHP
$i->items->first()
SQL
select top 1 *

ou

limit 1
11. first(predicate)

PRIORIDADE: ⭐⭐⭐

PHP
$i->items->first(
   fn($o) => $o->IsActive
)
12. orderBy()

PRIORIDADE: ⭐⭐⭐⭐

PHP
$i->items
   ->orderBy(fn($o) => $o->Created)
SQL
order by Item.Created asc
13. orderByDescending()

PRIORIDADE: ⭐⭐⭐⭐

PHP
->orderByDescending(fn($o) => $o->Created)
14. select()

PRIORIDADE: ⭐⭐⭐⭐⭐

PHP
->select(fn($o) => $o->Nome)
SQL
select Nome
15. where()

PRIORIDADE: ⭐⭐⭐⭐⭐

Você já está fazendo 😄

16. contains()

PRIORIDADE: ⭐⭐⭐⭐

PHP
$ids->contains($i->Id)
SQL
where Id in (...)
17. distinct()

PRIORIDADE: ⭐⭐⭐

PHP
->distinct()
SQL
select distinct
18. groupBy()

PRIORIDADE: ⭐⭐⭐⭐

PHP
->groupBy(fn($o) => $o->CategoriaId)
SQL
group by CategoriaId
19. having()

PRIORIDADE: ⭐⭐⭐⭐

PHP
->having(fn($g) => $g->sum(...) > 100)
SQL
having sum(...) > 100
20. joins automáticos via navigation

PRIORIDADE: ⭐⭐⭐⭐⭐

PHP
$o->Produto->CategoriaId
SQL
join Produto
21. nested any()

PRIORIDADE: ⭐⭐⭐⭐⭐

PHP
$i->items->any(
   fn($o) =>
      $o->produto->categorias->any(
         fn($c) => $c->IsActive
      )
)
22. outer scope access

PRIORIDADE: ⭐⭐⭐⭐⭐

PHP
$o->Created > $i->Created
Observação

Você JÁ percebeu isso 😄

23. null checks

PRIORIDADE: ⭐⭐⭐⭐⭐

PHP
$o->DeletedAt === null
SQL
DeletedAt is null
24. string methods

PRIORIDADE: ⭐⭐⭐⭐

PHP
$o->Nome->contains('abc')
SQL
Nome like '%abc%'
25. startsWith()

PRIORIDADE: ⭐⭐⭐⭐

PHP
$o->Nome->startsWith('A')
SQL
Nome like 'A%'
26. endsWith()

PRIORIDADE: ⭐⭐⭐⭐

SQL
Nome like '%A'
27. between()

PRIORIDADE: ⭐⭐⭐

PHP
$o->Created->between($a, $b)
SQL
between @a and @b
28. coalesce/null handling

PRIORIDADE: ⭐⭐⭐

PHP
($o->Valor ?? 0) > 10
SQL
coalesce(Valor, 0)
Ordem que eu implementaria
FASE 1

✅ where
✅ any
✅ !any
✅ count
✅ sum
✅ joins
✅ outer scopes
✅ groups
✅ null

FASE 2

✅ avg/min/max
✅ orderBy
✅ select
✅ distinct
✅ contains

FASE 3

✅ groupBy
✅ having
✅ nested aggregates
✅ all

FASE 4

✅ expression optimization
✅ query caching
✅ compiled queries
✅ SQL dialect abstraction