<?php

namespace App\Services\DocumentCreator;

use App\Enum\Document\DocumentStatusEnum;
use App\Enum\Document\DocumentStatusSignatureEnum;
use App\Enum\Document\DocumentTemplates\DocumentTemplatesEnum;
use App\Enum\Document\DocumentTemplates\DocumentTemplatesFieldEnum;
use App\Models\Document\Document;
use App\Models\Document\DocumentTemplate;
use App\Models\Fields\Directory\Counterparty;
use App\Models\Order\Bid;
use App\Models\Order\Order;
use App\Models\Order\Task;
use App\Models\User;
use App\Models\User\UserContractData;
use App\Models\Order\OrderInterface;
use App\Services\User\DataForDocumentCreatorService;
use Carbon\Carbon;
use Illuminate\Support\Str;


class UserDocumentCreatorService
{

    private DocumentTemplatesEnum $contract = DocumentTemplatesEnum::details;
    private DocumentTemplatesEnum $invoice = DocumentTemplatesEnum::payment;

    public PdfCreatorService $pdfService;

    public function __construct()
    {
        $this->pdfService = new PdfCreatorService();
    }

    public function createInvoice(User $user, OrderInterface $order): ?Document
    {
        $documentTemplate = $this->getDocumentTemplate($this->invoice);
        if ($documentTemplate) {
            $template    = $this->getTemplateUrl($documentTemplate);
            $filePathPdf = '/source/documentCreator/' . $user->id . '/' . Carbon::now(
                ) . '/' . $this->invoice->name . '_for_invoiceOrder_' . $order->id . '.pdf';
            $dataInvoice = $this->getDataForDoc($user, $order);
            $this->pdfService->savePdf($template, $dataInvoice, $filePathPdf);
            $document                   = new Document();
            $document->uuid             = Str::uuid();
            $document->user_id          = $user->id;
            $document->file_name        = $this->invoice->name . '_for_invoiceOrder_' . $order->id . '.pdf';
            $document->file_path        = $filePathPdf;
            $document->status           = DocumentStatusEnum::Signed->value;
            $document->status_signature = DocumentStatusSignatureEnum::NoSend->value;
            $document->date_signature   = Carbon::now();
            $document->save();
        }
        return $document ?? null;
    }

    public function createContract(User $user, Counterparty $counterparty): ?Document
    {
        if (!self::checkSignContract($user, $counterparty)) {
            $documentTemplate = $this->getDocumentTemplate($this->contract);
            if ($documentTemplate) {
                $template     = $this->getTemplateUrl($documentTemplate);
                [$dataContract,$dataForSave] = $this->getDataForContract($user, $counterparty);
                $filePathPdf  = '/source/documentCreator/' . $user->id . '/' . Carbon::now(
                    ) . '/' . $this->contract->name . '_for_counterparty_' . $counterparty->id . '.pdf';
                $this->pdfService->savePdf($template, $dataContract, $filePathPdf);
                $document                   = new Document();
                $document->uuid             = Str::uuid();
                $document->user_id          = $user->id;
                $document->file_name        = $this->contract->name . '_for_counterparty_' . $counterparty->id . '.pdf';
                $document->file_path        = $filePathPdf;
                $document->status           = DocumentStatusEnum::Signed->value;
                $document->status_signature = DocumentStatusSignatureEnum::NoSend->value;
                $document->date_signature   = Carbon::now();
                $document->save();
                $this->saveDataContract($document, $counterparty, $documentTemplate, $dataForSave);
            }
        }
        return $document ?? null;
    }

    private function getDataForDoc(User $user, OrderInterface $order): array
    {
        $data         = [];
        $counterparty = self::getCounterpartyByOrder($order);
        if ($counterparty) {
            $userData = UserContractData::query()
                ->where('user_id', $user->id)
                ->where('counterparty_id', $counterparty->id)
                ->where('date_start', '<=', Carbon::now())
                ->where('date_end', '>=', Carbon::now())
                ->first();
            if ($userData) {
                $data = $userData->data;
            }

            $dataForSave = $data;
            $userDataService = new DataForDocumentCreatorService($user, $counterparty);
            foreach (DocumentTemplatesFieldEnum::cases() as $field) {
                if(!$field->fromBD()){
                    $function = $field->getValue();
                    if ($function) {
                        $data[$field->name] = $userDataService->$function();
                    }
                }elseif (empty($data[$field->name])){
                    $function = $field->getValue();
                    if ($function) {
                        $data[$field->name] = $userDataService->$function();
                        $dataForSave[$field->name] = $data[$field->name];
                    }
                }
            }
            $userData->data = $dataForSave;
            $userData->save();

        }

        return $data;
    }

    public function getDataForContract(User $user, Counterparty $counterparty): array
    {
        $userDataService = new DataForDocumentCreatorService($user, $counterparty);
        $data            = [];
        $dataForBd       = [];
        foreach (DocumentTemplatesFieldEnum::cases() as $field) {
            $function = $field->getValue();
            if ($function) {
                $data[$field->name] = $userDataService->$function();
                if($field->fromBD()){
                    $dataForBd[$field->name] = $data[$field->name];
                }
            }
        }
        return [$data,$dataForBd];
    }

    private function getDocumentTemplate(DocumentTemplatesEnum $documentTemplates): ?DocumentTemplate
    {
        $documentTemplate = DocumentTemplate::where('type', $documentTemplates->value)
            ->where('date_start', '<=', Carbon::now())
            ->where('date_end', '>=', Carbon::now())
            ->first();
        return $documentTemplate ?? null;
    }

    private function getTemplateUrl(DocumentTemplate $documentTemplate): string
    {
        return 'document.' . $documentTemplate->type->name . $documentTemplate->date_start . $documentTemplate->date_end;
    }

    private function saveDataContract(
        Document $document,
        Counterparty $counterparty,
        DocumentTemplate $documentTemplate,
        array $data
    ): void {
        $contractData                  = new UserContractData();
        $contractData->user_id         = $document->user_id;
        $contractData->counterparty_id = $counterparty->id;
        $contractData->data            = $data;
        $contractData->date_start      = $documentTemplate->date_start;
        $contractData->date_end        = $documentTemplate->date_end;
        $contractData->save();
    }

    static function getCounterpartyByOrder(OrderInterface $order): ?Counterparty
    {
        $project = '';
        if ($order instanceof Order) {
            $project = $order->user?->project?->first();
        }

        if ($order instanceof Task) {
            $project = $order->project ?? $order->order?->user?->project?->first();
        }

        if ($order instanceof Bid) {
            $project = $order->order?->user?->project?->first()
                ?? $order->task?->project
                ?? $order->task?->order?->user?->project?->first();
        }

        if ($project) {
            $counterparty = $project->counterparties()->first();
        }

        return $counterparty ?? null;
    }

    static function checkSignContract(User $user, Counterparty $counterparty): bool
    {
        $userData = UserContractData::query()
            ->where('user_id', $user->id)
            ->where('counterparty_id', $counterparty->id)
            ->where('date_start', '<=', Carbon::now())
            ->where('date_end', '>=', Carbon::now())
            ->first();
        return !empty($userData);
    }

    static function checkSignContractByOrder(User $user, OrderInterface $order): bool
    {
        $counterparty = self::getCounterpartyByOrder($order);
        if ($counterparty) {
            return self::checkSignContract($user, $counterparty);
        }
        return false;
    }

}
