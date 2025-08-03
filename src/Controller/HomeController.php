<?php

namespace App\Controller;

use App\Repository\VehiculosRepository;
use App\Repository\VentasRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\ReservasRepository;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    #[Route('/', name: 'app_root')]
    #[IsGranted('ROLE_USER')]
    public function index(VehiculosRepository $vehiculosRepo, VentasRepository $ventasRepo, ReservasRepository $reservasRepo): Response
    {
        // --- KPIs Principales ---
        $totalVehiculos = $vehiculosRepo->countInStock();
        $ventasMesActual = $ventasRepo->countSalesThisMonth();
        $valorTotalInventario = $vehiculosRepo->sumInventoryValue();
        $totalVendidoHistorico = $ventasRepo->getTotalSalesValue();

        // --- Gráfico 1: Tendencia de Ventas (últimos 15 días) ---
        $salesTrendData = $ventasRepo->getSalesTrend(15);
        $trendLabels = [];
        $trendData = [];
        $dateRange = new \DatePeriod(new \DateTimeImmutable('-14 days midnight'), new \DateInterval('P1D'), new \DateTimeImmutable('+1 day'));
        $dailySales = [];
        foreach($dateRange as $date) {
            $dailySales[$date->format('Y-m-d')] = 0;
        }
        foreach ($salesTrendData as $row) {
            if (isset($dailySales[$row['sale_day']])) {
                $dailySales[$row['sale_day']] = $row['count'];
            }
        }
        foreach ($dailySales as $day => $count) {
            $trendLabels[] = (new \DateTime($day))->format('d/m');
            $trendData[] = $count;
        }
        $salesTrendChart = [
            'labels' => $trendLabels,
            'data' => $trendData
        ];
        
        // --- Gráfico 2: Stock por Marca ---
        $statsPorMarca = $vehiculosRepo->countVehiclesByBrand();
        $marcaLabels = [];
        $marcaData = [];
        foreach ($statsPorMarca as $stat) {
            $marcaLabels[] = $stat['name'];
            $marcaData[] = $stat['vehicleCount'];
        }
        $vehiculosPorMarcaChart = [
            'labels' => $marcaLabels,
            'data' => $marcaData,
        ];

        // --- Gráfico 3: Ventas y Montos por Mes (últimos 12 meses) ---
        $salesByMonthData = $ventasRepo->getSalesByMonth();
        $salesByMonthChart = ['labels' => [], 'count_data' => [], 'amount_data' => []];
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = new \DateTimeImmutable("-{$i} months");
            $months[$date->format('Y-m')] = ['count' => 0, 'amount' => 0];
        }
        foreach ($salesByMonthData as $row) {
            $key = $row['sales_year'] . '-' . str_pad($row['sales_month'], 2, '0', STR_PAD_LEFT);
            if (isset($months[$key])) {
                $months[$key]['count'] = $row['sales_count'];
                $months[$key]['amount'] = $row['total_amount'];
            }
        }
        foreach ($months as $month => $values) {
            $salesByMonthChart['labels'][] = (new \DateTimeImmutable($month . '-01'))->format('M Y');
            $salesByMonthChart['count_data'][] = $values['count'];
            $salesByMonthChart['amount_data'][] = $values['amount'];
        }

        // --- Feed de Actividad Reciente ---
        $recentSales = $ventasRepo->findBy([], ['sale_date' => 'DESC'], 5);
        $recentReservations = $reservasRepo->findBy([], ['reservation_date' => 'DESC'], 5);
        $recentVehicles = $vehiculosRepo->findBy([], ['created_at' => 'DESC'], 5);

        $activityFeed = [];
        foreach ($recentSales as $sale) {
            $activityFeed[] = [
                'type' => 'Venta', 'date' => $sale->getSaleDate(), 'icon' => 'fa-dollar-sign', 'color' => 'success',
                'text' => "Venta del {$sale->getVehiculo()->getVersion()->getModelo()->getMarca()->getName()} a {$sale->getCliente()->getFirstName()} {$sale->getCliente()->getLastName()}"
            ];
        }
        foreach ($recentReservations as $reserva) {
            $activityFeed[] = [
                'type' => 'Reserva', 'date' => $reserva->getReservationDate(), 'icon' => 'fa-calendar-check', 'color' => 'warning',
                'text' => "Reserva del {$reserva->getVehiculo()->getVersion()->getModelo()->getName()} por {$reserva->getCliente()->getFirstName()}"
            ];
        }
        foreach ($recentVehicles as $vehicle) {
            $activityFeed[] = [
                'type' => 'Ingreso', 'date' => $vehicle->getCreatedAt(), 'icon' => 'fa-car', 'color' => 'info',
                'text' => "Ingreso del {$vehicle->getVersion()->getModelo()->getMarca()->getName()} {$vehicle->getVersion()->getModelo()->getName()} {$vehicle->getVersion()->getName()}"
            ];
        }

        usort($activityFeed, fn($a, $b) => $b['date'] <=> $a['date']);
        $activityFeed = array_slice($activityFeed, 0, 7);

        // --- Top Marcas Más Vendidas ---
        $topSellingBrands = $ventasRepo->getTopSellingBrands(3);

        // --- Top Vendedores del Mes ---
        $topSalespersons = $ventasRepo->findTopSalespersonsThisMonth(3);
        
        // --- Lista de últimos vehículos ingresados ---
        $ultimosIngresos = $vehiculosRepo->findLatestArrivals(5);

        // --- NUEVA LÓGICA PARA EL KPI DE VENTAS POR EMPLEADO ---
        $salespersonSales = $ventasRepo->findSalesCountAndAmountBySalesperson();
        $salespersonChartData = [
            'labels' => [],
            'salesCount' => [],
            'salesAmount' => [],
        ];
        foreach ($salespersonSales as $salesperson) {
            $salespersonChartData['labels'][] = $salesperson['salespersonName'];
            $salespersonChartData['salesCount'][] = (int) $salesperson['salesCount'];
            $salespersonChartData['salesAmount'][] = (float) $salesperson['salesAmount'];
        }
        
        // --- Renderizar la plantilla con todas las variables ---
        return $this->render('home/index.html.twig', [
            'totalVehiculos' => $totalVehiculos,
            'ventasMesActual' => $ventasMesActual,
            'valorTotalInventario' => $valorTotalInventario,
            'totalVendidoHistorico' => $totalVendidoHistorico,
            'salesTrendChart' => $salesTrendChart,
            'vehiculosPorMarcaChart' => $vehiculosPorMarcaChart,
            'salesByMonthChart' => $salesByMonthChart,
            'topSellingBrands' => $topSellingBrands,
            'activityFeed' => $activityFeed,
            'topSalespersons' => $topSalespersons,
            'ultimosIngresos' => $ultimosIngresos,
            'salespersonChartData' => $salespersonChartData, // <-- ¡Aquí pasamos los nuevos datos!
        ]);
    }
}