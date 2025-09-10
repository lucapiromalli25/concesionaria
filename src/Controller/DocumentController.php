<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DocumentController extends AbstractController
{
    #[Route('/download/document/{filename}', name: 'app_download_document')]
    public function downloadDocument(string $filename): BinaryFileResponse
    {
        // Construimos la ruta segura al archivo dentro de la carpeta /public
        $projectDir = $this->getParameter('kernel.project_dir');
        $filePath = $projectDir . '/public/documents/' . $filename;

        // Verificamos que el archivo realmente exista para evitar errores
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('El archivo no fue encontrado.');
        }

        // Creamos una respuesta especial para archivos
        $response = new BinaryFileResponse($filePath);

        // Forzamos la descarga en lugar de mostrarlo en el navegador
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        return $response;
    }
}