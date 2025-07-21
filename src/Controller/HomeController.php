<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted; 

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    #[Route('/', name: 'app_root')] // Hacemos que la raíz del sitio también sea el home
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        // --- DATOS DE EJEMPLO (En el futuro vendrán de la base de datos) ---

        // 1. Estadísticas principales
        $totalVehiculos = 157;
        $ventasMesActual = 23;
        $valorTotalInventario = 7850000;

        // 2. Datos para el gráfico de vehículos por marca
        $vehiculosPorMarca = [
            'labels' => ['Ford', 'Chevrolet', 'Toyota', 'Nissan', 'Volkswagen'],
            'data' => [35, 28, 22, 18, 15],
        ];

        // 3. Lista de últimos vehículos ingresados
        $ultimosIngresos = [
            ['id' => 1, 'marca' => 'Ford', 'modelo' => 'Ranger', 'ano' => 2023],
            ['id' => 2, 'marca' => 'Toyota', 'modelo' => 'Corolla', 'ano' => 2024],
            ['id' => 3, 'marca' => 'Chevrolet', 'modelo' => 'Onix', 'ano' => 2023],
        ];
        
        // --- Fin de datos de ejemplo ---

        return $this->render('home/index.html.twig', [
            'totalVehiculos' => $totalVehiculos,
            'ventasMesActual' => $ventasMesActual,
            'valorTotalInventario' => $valorTotalInventario,
            'vehiculosPorMarca' => $vehiculosPorMarca,
            'ultimosIngresos' => $ultimosIngresos,
        ]);
    }
}