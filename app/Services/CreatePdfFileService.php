<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use setasign\Fpdi\Fpdi;
use Dompdf\Dompdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CreatePdfFileService
{
    public array $files;
    public int $userId;
    public string $mergeFilePath;
    public array $filesTmp = [];

    public function __construct(array $files, $userId)
    {
        $this->files = $files;
        $this->userId = $userId;
        $this->setSettings();
        $this->createOneFile();
    }

    private function setSettings(): void
    {
        Settings::setPdfRendererName(Settings::PDF_RENDERER_MPDF);
        Settings::setPdfRendererPath(base_path('vendor/mpdf/mpdf'));
    }

    private function createOneFile(): void
    {
        foreach ($this->files as $file) {
            $this->filesTmp[] = $this->convertToPdf($file);
        }
        if (!empty($this->filesTmp)) {
            $this->mergeFilePath = $this->saveFile($this->mergePdf());
        }
    }

    private function convertToPdf(UploadedFile $file): ?string
    {
        $extension = $file->getClientOriginalExtension();
        $tempPath = sys_get_temp_dir();
        $outputPath = $tempPath.'/'.uniqid().'.pdf';

        switch ($extension) {
            case 'pdf':
                return $file->getRealPath(); // Уже в PDF формате
            case 'doc':
            case 'docx':
                $phpWord = IOFactory::load($file->getRealPath());
                $xmlWriter = IOFactory::createWriter($phpWord, 'PDF');
                $xmlWriter->save($outputPath);
                break;
            case 'jpg':
            case 'jpeg':
            case 'png':
                $imageData = base64_encode($file->getContent());
                $src = 'data:'.mime_content_type($file->getRealPath()).';base64,'.$imageData;
                $dompdf = new Dompdf();
                $html = '<html><body> <img style="max-height:1020px; max-width:660px;" src="'.$src.'"> </body></html>';
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                file_put_contents($outputPath, $dompdf->output());
                break;
            default:
                break;
        }

        return $outputPath;
    }

    private function mergePdf(): ?string
    {
        $pdf = new Fpdi();
        foreach ($this->filesTmp as $filePath) {
            $pageCount = $pdf->setSourceFile($filePath);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tplIdx = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($tplIdx);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tplIdx);
            }
        }
        return $pdf->Output('S');
    }

    private function saveFile(string $content): ?string
    {
        $filename = Str::random(20).'.pdf';
        Storage::disk('public')->put('source/pdf/'.$this->userId.'/'.$filename, $content);
        return Storage::url('source/pdf/'.$this->userId.'/'.$filename);
    }


}
