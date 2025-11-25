<?php

namespace App\Console\Commands;


use App\Enum\Document\DocumentStatusEnum;
use App\Enum\Document\DocumentStatusSignatureEnum;
use App\Enum\Order\OrderStatusEnum;
use App\Enum\Order\ReportStatusEnum;
use App\Models\Document\Document;
use App\Models\Order\Bid;
use App\Models\Order\Report;
use App\Models\Setting;
use App\Services\CreatePdfFileService;
use App\Services\DocumentServices\CorrectRecognitionService;
use App\Services\Nopaper\NopaperService;
use App\Services\OneC\OneCServices;
use App\Services\DocumentCreator\PdfCreatorService;
use App\Services\Register\EmailService;
use App\Services\TimeService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TestCommand extends Command
{
    protected $signature = 'test';

    protected $description = '';

    public function handle(): void
    {
        $user = User::query()->where('id',215)->first();
        $data = [
            'user'=>$user
        ];
        (new PdfCreatorService())->savePdf(
            'document.test',
        $data,
            '/source/documentCreator/'.$user->id.'/'.Carbon::now().'/nameTest.pdf'
        );
        //EmailService::sendConfirmUserModeration(User::query()->where('id',215)->first());

        die();

        //$payload = [
        //            'userPhone' => $userData['phone'],
        //            'email' => $userData['email'],
        //            'name' => $userData['name'],
        //            'surname' => $userData['surname'],
        //            'patronymic' => $userData['patronymic'] ?? null, // Optional field
        //            'isShortTimePassword' => true,
        //            'birthDate' => $userData['birth_date'], // Format: "1990-12-27T00:00:00.000Z"
        //            'gender' => $userData['gender'], // e.g., 1
        //            'passportData' => [
        //                'series' => $userData['passport_series'],
        //                'number' => $userData['passport_number'],
        //                'issuedBy' => $userData['passport_issued_by'],
        //                'issuingDate' => $userData['passport_issuing_date'], // Format: "2018-12-27T00:00:00.000Z"
        //                'issuerDepartmentCode' => $userData['passport_department_code'],
        //                'birthPlace' => $userData['passport_birth_place'],
        //            ]
        //        ];
        $user = User::query()->where('id',213)->first();
        $document = new Document();
        $document->uuid = Str::uuid();
        $document->user_id = $user->id;
        $document->file_path = 'source/userImg/92/0zBLRLjoayNBUuwy0uNf.jpeg';
        $document->file_name = 'docForPay_'.Carbon::now()->format('d.m.Y H:i:s').'.pdf';
        $document->status = DocumentStatusEnum::Signed->value;
        $document->status_signature = DocumentStatusSignatureEnum::NoSend->value;
        $document->date_signature = Carbon::now();
        $document->save();

//        $imagePath = str_replace('/storage','',$document->file_path);
//        $fileContent = base64_encode(Storage::disk('public')->get($imagePath));
//        $fileData    = [
//            'fileInfo' => [
//                'fileNameWithExtension' => basename($document->file_path),
//                'filebase64'            => $fileContent
//            ]
//        ];
//        echo "<pre>";
//        var_dump($fileData);
//        echo "</pre>";
//        die();
        $userData = [
            'phone'=> 79036650547,
            'email'=> 'fifka@mail.ru',
        ];

        $user = User::query()->where('id',215)->first();
        $doc = Document::query()->where('id',79)->first();
//32525
        $nopaper = new NopaperService();
        //$data = $nopaper->checkUserExists($user);
        //$data = $nopaper->sendDocumentsToNopaper($user);
        //$data = $nopaper->confirmSms($user,'000000');
        $data = $nopaper->getDocumentInfo($doc);



        echo "<pre>";
        var_dump($data);
        echo "</pre>";
        die();
//        $respons = $nopaper->checkUserExists($user);
//        echo "<pre>";
//        var_dump($respons);
//        echo "</pre>";
//        die();
//        $respons = $nopaper->registerUser($user);
//        echo "<pre>";
//        var_dump($respons);
//        echo "</pre>";
//        die();
//        $respons = $nopaper->checkUserExists($user->phone);
//        echo "<pre>";
//        var_dump($respons);
//        echo "</pre>";

        $nopaper->sendDocumentsToNopaper($user);
        //$nopaper->getDocumentInfo(32376);

    }
}
