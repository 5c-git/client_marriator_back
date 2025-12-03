<?php
namespace App\Services\DocumentCreator;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\PDF as PDFBase;

class PdfCreatorService
{
    public function generatePdf(string $template, array $data, string $filename = 'document.pdf'): Response|false
    {
        if (!View::exists($template)) {
            return false;
        }
        return $this->createPdf($template,$data)->download($filename);
    }

    public function savePdf(string $template, array $data, string $filepath): array
    {
        $content = $this->getPdfContent($template,$data);
        Storage::disk('public')->put($filepath, $content);
        return [
            'path' => $filepath,
            'url' => Storage::disk('public')->url($filepath),
            'full_path' => Storage::disk('public')->path($filepath)
        ];
    }

    private function getPdfContent(string $template, array $data): string
    {
        return $this->createPdf($template,$data)->output();
    }

    private function createPdf(string $template, array $data): PDFBase
    {
        $pdf = Pdf::loadView($template, $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('defaultFont', 'DejaVu Sans');
        $pdf->setOption('charset', 'UTF-8');
        return $pdf;
    }
}
