<?php

use Websyspro\Entity\Shareds\AbstractRepository;
use Websyspro\Test\Crm\Entitys\ItemPropostaEntity;
use Websyspro\Test\Crm\Entitys\ObraEntity;
use Websyspro\Test\Crm\Entitys\PropostaEntity;

$start = microtime( true );

// $repository = new Repository( UsuarioEntity::class );
// $repository->where( 
//   fn( UsuarioEntity $usuario ) => 
//     $usuario->EmailConfirmed === true &&
//     $usuario->ConsultorVendasEspeciais->Id === $usuario->Id &&
//     $usuario->ConsultorVendasEspeciais->IsActive === true &&
//     $usuario->ConsultorVendasEspeciais->IsDeleted === false &&
//     $usuario->ConsultorVendasEspeciais->GerenteVendasEspeciais->Id === $usuario->ConsultorVendasEspeciais->GerenteVendasEspeciaisId &&
//     $usuario->ConsultorComercial->Id === $usuario->Id &&
//     $usuario->ConsultorComercial->IsActive === true &&
//     $usuario->ConsultorComercial->IsDeleted === false &&
//     $usuario->ConsultorComercial->GerenteComercial->Id === $usuario->ConsultorComercial->GerenteComercialId &&
//     $usuario->Perfils->where( fn( UsuarioPerfilsEntity $usuarioPerfil ) =>
//       $usuarioPerfil->UserId === $usuario->Id &&
//       $usuarioPerfil->Perfil->Id === $usuarioPerfil->RoleId
//     )
//   );
// $repository->orderByAsc( fn( UsuarioEntity $usuario ) => [ $usuario->Id ]);
// $repository->paged( 1, 24 );
// $rows = $repository->get();

// $repository = new Repository( ObraEntity::class );
// $repository->where(fn( ObraEntity $obra ) => (
//   $obra->IsActive === true &&
//   $obra->IsDeleted === false &&
//   $obra->Colecao->Id === $obra->ColecaoId &&
//   $obra->Editora->Id === $obra->EditoraId &&
//   $obra->Segmento->Id === $obra->SegmentoId &&
//   $obra->Empresa->Id === $obra->EmpresaId &&
//   $obra->ValoresObra->ObraId === $obra->Id
// ));
// $repository->orderByAsc( fn( ObraEntity $obra ) => [ $obra->Id ]);
// $repository->paged( 1, 12 );
// $rows = $repository->get();

// $repository = new Repository( PropostaEntity::class );
// $repository->where( fn( PropostaEntity $proposta ) => (
//   $proposta->IsActive === true &&
//   $proposta->IsDeleted === false &&
//   $proposta->Distribuidor->Id === $proposta->DistribuidorId &&
//   $proposta->ConsultorVendasEspeciais->Id === $proposta->ConsultorVendasEspeciaisId &&
//   $proposta->ConsultorVendasEspeciais->GerenteVendasEspeciais->Id === $proposta->ConsultorVendasEspeciais->GerenteVendasEspeciaisId &&
//   $proposta->Instituicao->Id === $proposta->InstituicaoId &&
//   $proposta->Instituicao->Municipio->Id === $proposta->Instituicao->MunicipioId
// ));
// $repository->orderByAsc( fn( PropostaEntity $proposta ) => [ $proposta->Id ]);
// $repository->paged( 1, 2 );
// $rows = $repository->get();

// $repo = new Repository( PropostaEntity::class )
//   ->where( fn( PropostaEntity $i ) => $i->Id === 1 )
//   ->select( fn( PropostaEntity $i ) => [ 
//       $i->Id, 
//       $i->NomeProposta,
//       $i->itemsProposta->sum(
//         fn( ItemPropostaEntity $s ) => $s->ValorUnitario
//       ) 
//   ])
//   ->firstOrDefault();

$repo = new AbstractRepository( PropostaEntity::class );
$repo
  ->include( fn( PropostaEntity $p ) => $p->itemsProposta->where( fn( ItemPropostaEntity $i ) => $i->IsActive && !$i->IsDeleted )
    ->include( fn( ItemPropostaEntity $i ) => $i->obra
      ->include( fn( ObraEntity $i ) => $i->ValoresObra )
    )
  )
  ->where( fn( PropostaEntity $i ) => 
      $i->IsActive && 
     !$i->IsDeleted && (
      $i->Created >= '01/01/2026' &&
      $i->IsActive === true && 
      '01/31/2026' >= $i->Created
    ) && $i->IsActive === true
  )->select( fn( PropostaEntity $i ) => [ 
    $i->Id, $i->NomeProposta, $i->itemsProposta->sum(
      fn( ItemPropostaEntity $s ) => $s->ValorUnitario * $s->Quantidade
    ) 
  ]);


// $repository = new Repository(PostEntity::class);
// $repository->where(
//   fn( PostEntity $post ) => (
//     $post->Status === PostStatus::Publish &&
//     $post->Type === PostType::Obra &&
//     $post->Metas->where( fn( PostMetaEntity $postMeta ) => (
//         $postMeta->postId === $post->Id && 
//         $postMeta->metaKey === 'link_do_pdf'
//       )
//     )
//   ) 
// );
// $repository->orderByAsc( fn( PostEntity $post ) => [ $post->Date ]);
// $repository->paged( 1, 32 );
// $rows = $repository->get();

$leftTimer = number_format(( microtime( true ) - $start ) * 1000, 6, ",", "." );
echo "Execute timer: {$leftTimer}(ms)" . PHP_EOL . PHP_EOL;

print_r( $repo->wheres );
// print_r( $repo->includes );

// print_r( "ROWS: " . sizeof($rows) . PHP_EOL . PHP_EOL );

// print_r( Util::hydrate( $rows, UsersEntity::class ));
// print_r( $rows );

// $includeStringParser = new IncludeStringParser();
// print_r( 
//   $includeStringParser->parse('fn( PropostaEntity $i ) => $i->itemsProposta
//     ->where( fn( ItemPropostaEntity $i ) => $i->IsActive && !$i->IsDeleted )
//     ->include( fn( ItemPropostaEntity $i ) => $i->obra
//       ->include( fn( ObraEntity $i ) => $i->ValoresObra )
//     )', PropostaEntity::class
//   )
// );