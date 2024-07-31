<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use setasign\Fpdi\Fpdi;
use Dompdf\Dompdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\Models\Fields\Fields;


class CreatePdfFileService
{
    public array $files;
    public int $userId;
    public string $userPhone;
    public string $fieldUuid;
    public string $fieldName;
    public string $mergeFilePath;
    public array $filesTmp = [];
    public array $filesOriginalName = [];
    public string $error = '';

    public function __construct(array $files, $userId,$userPhone,$fieldUuid)
    {
        $this->files = $files;
        $this->userId = $userId;
        $this->userPhone = $userPhone;
        $this->fieldUuid = $fieldUuid;
        if($this->checkUuid()) {
            $this->setSettings();
            $this->createOneFile();
        }
    }

    private function checkUuid():bool
    {
       $field = Fields::where('uuid',$this->fieldUuid)->first();
       if($field){
           $this->fieldName = $field->name;
           return true;
       }else{
           $this->error = 'Поле не найдено';
           return false;
       }
    }

    private function setSettings(): void
    {
        Settings::setPdfRendererName(Settings::PDF_RENDERER_MPDF);
        Settings::setPdfRendererPath(base_path('vendor/mpdf/mpdf'));
    }

    private function createOneFile(): void
    {
        foreach ($this->files as $file) {
            [$this->filesTmp[], $this->filesOriginalName[]] = $this->convertToPdf($file);
        }
        if (!empty($this->filesTmp) && empty($this->error)) {
            $this->mergeFilePath = $this->saveFile($this->mergePdf());
        }
    }

    private function convertToPdf(UploadedFile $file): ?array
    {
        try {
            $extension = $file->getClientOriginalExtension();
            $tempPath = sys_get_temp_dir();
            $outputPath = $tempPath . '/' . uniqid() . '.pdf';

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
                    $src = 'data:' . mime_content_type($file->getRealPath()) . ';base64,' . $imageData;
                    $dompdf = new Dompdf();
                    $html = '<html><body> <img style="max-height:1020px; max-width:660px;" src="' . $src . '"> </body></html>';
                    $dompdf->loadHtml($html);
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();
                    file_put_contents($outputPath, $dompdf->output());
                    break;
                default:
                    break;
            }
        } catch (\Throwable $e) {
            $this->error = 'Ошибка конвертации файлов, ' . $file->getClientOriginalName() . ' замените его на скриншоты контента';
            return ['',$file->getClientOriginalName()];
        }

        return [$outputPath, $file->getClientOriginalName()];
    }

    private function mergePdf(): ?string
    {

        $pdf = new Fpdi();
        foreach ($this->filesTmp as $i=>$filePath) {
            try {
                $pageCount = $pdf->setSourceFile($filePath);
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $tplIdx = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($tplIdx);
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($tplIdx);
                }
            } catch (\Throwable $e) {
                $this->error = 'Ошибка конвертации файлов, ' . $this->filesOriginalName[$i] . ' замените его на скриншоты контента';
                return '';
            }
        }

        return $pdf->Output('S');
//        $outputPdf = uniqid() . '.pdf';
//        $pdf = new PDFlib();
//        if ($pdf->begin_document($outputPdf, "") == 0) {
//            die("Error: " . $pdf->get_errmsg());
//        }
//        foreach ($this->filesTmp as $pdfFile) {
//            $pageCount = $pdf->pcos_get_number($pdfFile, "length:pages");
//            for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
//                $pdfDoc = $pdf->open_pdi_document($pdfFile, "");
//                $page = $pdf->open_pdi_page($pdfDoc, $pageNumber, "");
//                $pdf->begin_page_ext(0, 0, "");
//                $pdf->fit_pdi_page($page, 0, 0, "");
//                $pdf->close_pdi_page($page);
//                $pdf->end_page_ext("");
//                $pdf->close_pdi_document($pdfDoc);
//            }
//        }
//        $pdf->end_document("");
//        $pdfContent = file_get_contents($outputPdf);
//        unlink($outputPdf);
//        return $pdfContent;
    }

    private function saveFile(string $content): ?string
    {
        $filename = '['.$this->userPhone.']'.'['.$this->fieldName.']' . '.pdf';
        $linkRund = Str::random(10);
        Storage::disk('public')->put('source/pdf/' . $this->userId . '/'.$linkRund.'/' . $filename, $content);
        return Storage::url('source/pdf/' . $this->userId . '/'.$linkRund.'/' . $filename);
    }


}
