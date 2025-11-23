<?php
namespace App\Services\DocumentCreator;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\PDF as PDFBase;

class UserDocumentCreatorService
{
    public function createInvoice(string $template, array $data): PDFBase
    {
        $pdf = Pdf::loadView($template, $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('defaultFont', 'DejaVu Sans');
        return $pdf;
    }
}
