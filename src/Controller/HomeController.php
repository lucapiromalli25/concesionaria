<?php

namespace App\Controller;

use App\Repository\CuotasRepository;
use App\Repository\ReservasRepository;
use App\Repository\VehiculosRepository;
use App\Repository\VentasRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    #[Route('/', name: 'app_root')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(
        CuotasRepository $cuotasRepo,
        VehiculosRepository $vehiculosRepo,
        VentasRepository $ventasRepo,
        ReservasRepository $reservasRepo,
        HttpClientInterface $httpClient,
        CacheInterface $cache,
    ): Response {
        // Sin permiso de panel no va un 403 pelado: al usuario le explicamos que le
        // falta un rol. Los que quedaron sin rol en la migracion caen aca.
        if (!$this->isGranted('dashboard.ver')) {
            return $this->render('home/sin_permisos.html.twig');
        }

        $hoy = new \DateTimeImmutable('today');

        // --- Stock ---
        $porEstado = $vehiculosRepo->countByState();
        $inventario = $vehiculosRepo->inventoryValue();

        // --- Reservas activas ---
        $reservasActivas = $reservasRepo->findBy(['status' => 'Activa'], ['expiration_date' => 'ASC']);
        $reservasVencidas = array_filter(
            $reservasActivas,
            fn($r) => $r->getExpirationDate() && $r->getExpirationDate() < $hoy
        );

        // --- Cobranzas ---
        $vencidas = $cuotasRepo->overdueSummary();
        $proximas = $cuotasRepo->findUpcoming(7, 8);
        $cuotasVencidas = $cuotasRepo->findOverdue(8);

        // --- Ventas ---
        $ventasPorMes = $this->armarSerieMensual($ventasRepo->getSalesByMonth());
        $mesActual = $ventasPorMes[array_key_last($ventasPorMes)] ?? null;

        return $this->render('home/index.html.twig', [
            'porEstado'        => $porEstado,
            'inventario'       => $inventario,
            'reservasActivas'  => count($reservasActivas),
            'reservasVencidas' => count($reservasVencidas),
            'proximaReserva'   => $reservasActivas[0] ?? null,
            'vencidas'         => $vencidas,
            'cuotasVencidas'   => $cuotasVencidas,
            'proximasCuotas'   => $proximas,
            'ventasPorMes'     => $ventasPorMes,
            'ventasMes'        => $mesActual,
            'totalVendido'     => $ventasRepo->getTotalSalesValueByCurrency(),
            'cobrado'          => $cuotasRepo->sumPaidInstallmentsByCurrency(),
            'pendiente'        => $cuotasRepo->sumPendingInstallmentsByCurrency(),
            'topMarcas'        => $ventasRepo->getTopSellingBrands(5),
            'vendedores'       => $ventasRepo->findSalesCountAndAmountBySalesperson(5),
            'ultimosIngresos'  => $vehiculosRepo->findLatestArrivals(5),
            'ultimasVentas'    => $ventasRepo->findBy([], ['sale_date' => 'DESC'], 5),
            'dolar'            => $this->cotizacionDolar($httpClient, $cache),
        ]);
    }

    /**
     * Completa los 12 meses (incluidos los sin ventas) y separa el monto por
     * moneda, porque pesos y dolares no se suman entre si.
     */
    private function armarSerieMensual(array $filas): array
    {
        $meses = [];
        for ($i = 11; $i >= 0; $i--) {
            $fecha = new \DateTimeImmutable("first day of -{$i} months");
            $meses[$fecha->format('Y-m')] = [
                'etiqueta' => $fecha->format('M'),
                'periodo'  => $fecha->format('m/Y'),
                'cantidad' => 0,
                'montos'   => ['ARS' => 0.0, 'USD' => 0.0],
            ];
        }

        foreach ($filas as $fila) {
            $clave = sprintf('%04d-%02d', $fila['sales_year'], $fila['sales_month']);
            if (!isset($meses[$clave])) {
                continue;
            }
            $meses[$clave]['cantidad'] += (int) $fila['sales_count'];
            $meses[$clave]['montos'][$fila['moneda'] ?: 'ARS'] += (float) $fila['total_amount'];
        }

        return $meses;
    }

    /**
     * Cotizacion del dolar cacheada 30 minutos: no tiene sentido pegarle a la
     * API en cada carga del panel.
     */
    private function cotizacionDolar(HttpClientInterface $httpClient, CacheInterface $cache): array
    {
        return $cache->get('cotizacion_dolar', function (ItemInterface $item) use ($httpClient) {
            $item->expiresAfter(1800);

            try {
                $respuesta = $httpClient->request('GET', 'https://dolarapi.com/v1/dolares', ['timeout' => 3]);
                $cotizaciones = [];
                foreach ($respuesta->toArray() as $fila) {
                    if (in_array($fila['casa'], ['oficial', 'blue'], true)) {
                        $cotizaciones[$fila['casa']] = $fila;
                    }
                }

                return $cotizaciones;
            } catch (\Throwable) {
                return [];
            }
        });
    }
}
