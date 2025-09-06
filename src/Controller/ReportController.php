<?php

namespace App\Controller;

use App\Repository\VehiculosRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reports')]
#[IsGranted('ROLE_MANAGER')] // Solo Gerentes y Admins pueden generar reportes
class ReportController extends AbstractController
{
    #[Route('/export/vehicles', name: 'app_report_export_vehicles', methods: ['GET'])]
    public function exportVehicles(VehiculosRepository $vehiculosRepository): StreamedResponse
    {
        $vehiculos = $vehiculosRepository->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventario de Vehículos');

        // Definir las cabeceras
        $headers = [
            'ID', 'Marca', 'Modelo', 'Versión', 'Año', 'Patente', 'Color',
            'Kilometraje', 'Nro. Chasis', 'Nro. Motor', 'Estado', 'Precio de Venta',
            'Fecha de Ingreso', 'Proveedor'
        ];
        $sheet->fromArray($headers, null, 'A1');

        // Rellenar los datos
        $data = [];
        foreach ($vehiculos as $vehiculo) {
            $data[] = [
                $vehiculo->getId(),
                $vehiculo->getVersion()?->getModelo()?->getMarca()?->getName(),
                $vehiculo->getVersion()?->getModelo()?->getName(),
                $vehiculo->getVersion()?->getName(),
                $vehiculo->getAnio(),
                $vehiculo->getPlateNumber(),
                $vehiculo->getColor(),
                $vehiculo->getKilometers(),
                $vehiculo->getChassisNumber(),
                $vehiculo->getEngineNumber(),
                $vehiculo->getState(),
                $vehiculo->getSuggestedRetailPrice(),
                $vehiculo->getEntryDate() ? $vehiculo->getEntryDate()->format('d/m/Y') : '',
                $vehiculo->getSupplier()?->getName()
            ];
        }
        $sheet->fromArray($data, null, 'A2');
        
        // Estilo para las cabeceras
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4285F4']]
        ];
        $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);
        
        // Auto-ajustar el ancho de las columnas
        foreach (range('A', 'N') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        // Crear una respuesta para enviar el archivo al navegador
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        // Definir el nombre del archivo de descarga
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'inventario_vehiculos_' . date('Y-m-d') . '.xlsx'
        );

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}