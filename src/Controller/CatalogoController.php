<?php

namespace App\Controller;

use App\Entity\Clientes;
use App\Entity\Marcas;
use App\Entity\Modelos;
use App\Entity\Proveedores;
use App\Entity\Versiones;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Alta rapida de catalogo desde el formulario de vehiculo, en un solo paso
 * (marca + modelo + version juntos) para no encadenar formularios.
 */
#[Route('/catalogo')]
class CatalogoController extends AbstractController
{
    #[Route('/buscar/clientes', name: 'app_catalogo_buscar_clientes', methods: ['GET'])]
    #[IsGranted('clientes.ver')]
    public function buscarClientes(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $q = trim((string) $request->query->get('q'));

        $qb = $em->getRepository(Clientes::class)->createQueryBuilder('c')
            ->select('c.id, c.first_name, c.last_name, c.document_number, c.phone')
            ->orderBy('c.last_name', 'ASC')
            ->setMaxResults(30);

        if ($q !== '') {
            $qb->where('c.first_name LIKE :q OR c.last_name LIKE :q OR c.document_number LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }

        $resultados = array_map(
            fn(array $r) => [
                'id'   => $r['id'],
                'text' => trim($r['first_name'] . ' ' . $r['last_name']),
                'hint' => trim(implode(' - ', array_filter(['DNI ' . $r['document_number'], $r['phone']]))),
            ],
            $qb->getQuery()->getArrayResult()
        );

        return new JsonResponse($resultados);
    }

    #[Route('/cliente-rapido', name: 'app_catalogo_cliente_rapido', methods: ['POST'])]
    #[IsGranted('clientes.crear')]
    public function clienteRapido(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $nombre   = trim((string) ($data['firstName'] ?? ''));
        $apellido = trim((string) ($data['lastName'] ?? ''));
        $documento = preg_replace('/\D/', '', (string) ($data['documentNumber'] ?? ''));

        if ($nombre === '' || $apellido === '' || $documento === '') {
            return new JsonResponse(['error' => 'Nombre, apellido y documento son obligatorios.'], Response::HTTP_BAD_REQUEST);
        }

        $cliente = $em->getRepository(Clientes::class)->findOneBy(['document_number' => $documento]);

        if (!$cliente) {
            $cliente = new Clientes();
            $cliente->setFirstName($nombre);
            $cliente->setLastName($apellido);
            $cliente->setDocumentNumber($documento);
            $cliente->setPhone(trim((string) ($data['phone'] ?? '')) ?: null);
            $this->stampAudit($cliente);
            $em->persist($cliente);
            $em->flush();
        }

        return new JsonResponse([
            'id'   => $cliente->getId(),
            'text' => trim($cliente->getFirstName() . ' ' . $cliente->getLastName()),
        ]);
    }

    #[Route('/buscar/versiones', name: 'app_catalogo_buscar_versiones', methods: ['GET'])]
    #[IsGranted('catalogo.ver')]
    public function buscarVersiones(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $q = trim((string) $request->query->get('q'));

        $qb = $em->getRepository(Versiones::class)->createQueryBuilder('v')
            ->select('v.id, v.name as version, mo.name as modelo, ma.name as marca')
            ->join('v.modelo', 'mo')
            ->join('mo.marca', 'ma')
            ->orderBy('ma.name', 'ASC')->addOrderBy('mo.name', 'ASC')
            ->setMaxResults(30);

        if ($q !== '') {
            $qb->where('ma.name LIKE :q OR mo.name LIKE :q OR v.name LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }

        $resultados = array_map(
            fn(array $r) => [
                'id'   => $r['id'],
                'text' => trim(sprintf('%s %s', $r['marca'], $r['modelo'])),
                'hint' => $r['version'],
            ],
            $qb->getQuery()->getArrayResult()
        );

        return new JsonResponse($resultados);
    }

    #[Route('/buscar/modelos', name: 'app_catalogo_buscar_modelos', methods: ['GET'])]
    #[IsGranted('catalogo.ver')]
    public function buscarModelos(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $q = trim((string) $request->query->get('q'));

        $qb = $em->getRepository(Modelos::class)->createQueryBuilder('mo')
            ->select('mo.id, mo.name AS modelo, ma.name AS marca')
            ->join('mo.marca', 'ma')
            ->orderBy('ma.name', 'ASC')->addOrderBy('mo.name', 'ASC')
            ->setMaxResults(30);

        if ($q !== '') {
            $qb->where('mo.name LIKE :q OR ma.name LIKE :q')->setParameter('q', '%' . $q . '%');
        }

        $resultados = array_map(
            fn(array $r) => [
                'id'   => $r['id'],
                'text' => trim($r['marca'] . ' ' . $r['modelo']),
            ],
            $qb->getQuery()->getArrayResult()
        );

        return new JsonResponse($resultados);
    }

    #[Route('/buscar/proveedores', name: 'app_catalogo_buscar_proveedores', methods: ['GET'])]
    #[IsGranted('proveedores.ver')]
    public function buscarProveedores(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $q = trim((string) $request->query->get('q'));

        $qb = $em->getRepository(Proveedores::class)->createQueryBuilder('p')
            ->select('p.id, p.name, p.documentNumber, p.phone')
            ->orderBy('p.name', 'ASC')
            ->setMaxResults(30);

        if ($q !== '') {
            $qb->where('p.name LIKE :q OR p.documentNumber LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }

        $resultados = array_map(
            fn(array $r) => [
                'id'   => $r['id'],
                'text' => $r['name'],
                'hint' => trim(implode(' - ', array_filter([$r['documentNumber'], $r['phone']]))) ?: null,
            ],
            $qb->getQuery()->getArrayResult()
        );

        return new JsonResponse($resultados);
    }

    #[Route('/version-rapida', name: 'app_catalogo_version_rapida', methods: ['POST'])]
    #[IsGranted('catalogo.crear')]
    public function versionRapida(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $marcaName   = trim((string) ($data['marca'] ?? ''));
        $modeloName  = trim((string) ($data['modelo'] ?? ''));
        $versionName = trim((string) ($data['version'] ?? ''));

        if ($marcaName === '' || $modeloName === '') {
            return new JsonResponse(['error' => 'La marca y el modelo son obligatorios.'], Response::HTTP_BAD_REQUEST);
        }

        $marca = $em->getRepository(Marcas::class)->createQueryBuilder('m')
            ->where('LOWER(m.name) = :name')->setParameter('name', mb_strtolower($marcaName))
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();

        if (!$marca) {
            $marca = (new Marcas())->setName($marcaName);
            $this->stampAudit($marca);
            $em->persist($marca);
        }

        $modelo = $em->getRepository(Modelos::class)->createQueryBuilder('mo')
            ->where('LOWER(mo.name) = :name')->andWhere('mo.marca = :marca')
            ->setParameter('name', mb_strtolower($modeloName))->setParameter('marca', $marca)
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();

        if (!$modelo) {
            $modelo = (new Modelos())->setName($modeloName)->setMarca($marca);
            $this->stampAudit($modelo);
            $em->persist($modelo);
        }

        $version = null;
        if ($versionName !== '') {
            $version = $em->getRepository(Versiones::class)->createQueryBuilder('v')
                ->where('LOWER(v.name) = :name')->andWhere('v.modelo = :modelo')
                ->setParameter('name', mb_strtolower($versionName))->setParameter('modelo', $modelo)
                ->setMaxResults(1)->getQuery()->getOneOrNullResult();
        }

        if (!$version) {
            $version = (new Versiones())->setName($versionName ?: null)->setModelo($modelo);
            $this->stampAudit($version);
            $em->persist($version);
        }

        $em->flush();

        return new JsonResponse([
            'id'   => $version->getId(),
            'text' => trim(sprintf('%s %s %s', $marca->getName(), $modelo->getName(), $version->getName() ?? '')),
        ]);
    }

    #[Route('/proveedor-rapido', name: 'app_catalogo_proveedor_rapido', methods: ['POST'])]
    #[IsGranted('proveedores.crear')]
    public function proveedorRapido(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $name = trim((string) ($data['name'] ?? ''));

        if ($name === '') {
            return new JsonResponse(['error' => 'El nombre es obligatorio.'], Response::HTTP_BAD_REQUEST);
        }

        $proveedor = $em->getRepository(Proveedores::class)->createQueryBuilder('p')
            ->where('LOWER(p.name) = :name')->setParameter('name', mb_strtolower($name))
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();

        if (!$proveedor) {
            $proveedor = (new Proveedores())->setName($name);
            $proveedor->setPhone(trim((string) ($data['phone'] ?? '')) ?: null);
            $proveedor->setDocumentNumber(trim((string) ($data['documentNumber'] ?? '')) ?: null);
            $em->persist($proveedor);
            $em->flush();
        }

        return new JsonResponse(['id' => $proveedor->getId(), 'text' => $proveedor->getName()]);
    }

    private function stampAudit(object $entity): void
    {
        $now = new \DateTimeImmutable();
        $entity->setCreatedAt($now);
        $entity->setUpdatedAt($now);
        $entity->setCreatedBy($this->getUser());
        $entity->setUpdatedBy($this->getUser());
    }
}
