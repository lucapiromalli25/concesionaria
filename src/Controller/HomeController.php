<?php

namespace App\Controller;

use App\Repository\VehiculosRepository;
use App\Repository\VentasRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    #[Route('/', name: 'app_root')]
    #[IsGranted('ROLE_USER')]
    public function index(VehiculosRepository $vehiculosRepo, VentasRepository $ventasRepo): Response
    {
        // 1. Estadísticas principales obtenidas de la BD
        $totalVehiculos = $vehiculosRepo->countInStock();
        $ventasMesActual = $ventasRepo->countSalesThisMonth();
        $valorTotalInventario = $vehiculosRepo->sumInventoryValue();

        // 2. Datos para el gráfico de Tendencia de Ventas
        $salesTrendData = $ventasRepo->getSalesTrend(15);
        $trendLabels = [];
        $trendData = [];
        $dateRange = new \DatePeriod(new \DateTime('-14 days'), new \DateInterval('P1D'), new \DateTime('+1 day'));
        $dailySales = [];
        foreach($dateRange as $date) {
            $dailySales[$date->format('Y-m-d')] = 0;
        }
        foreach ($salesTrendData as $row) {
            $dailySales[$row['sale_day']] = $row['count'];
        }
        foreach ($dailySales as $day => $count) {
            $trendLabels[] = (new \DateTime($day))->format('d/m');
            $trendData[] = $count;
        }
        $salesTrendChart = [
            'labels' => $trendLabels,
            'data' => $trendData
        ];
        
        // 3. Datos para el gráfico de Stock por Marca
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

        // 4. Lista de últimos vehículos ingresados
        $ultimosIngresos = $vehiculosRepo->findLatestArrivals(5);
        
        // 5. Renderizar la plantilla con todas las variables
        return $this->render('home/index.html.twig', [
            'totalVehiculos' => $totalVehiculos,
            'ventasMesActual' => $ventasMesActual,
            'valorTotalInventario' => $valorTotalInventario,
            'salesTrendChart' => $salesTrendChart,
            'vehiculosPorMarcaChart' => $vehiculosPorMarcaChart, // <-- CORREGIDO
            'ultimosIngresos' => $ultimosIngresos,
        ]);
    }
}