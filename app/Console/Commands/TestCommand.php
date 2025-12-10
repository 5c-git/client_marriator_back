<?php

namespace App\Console\Commands;


use App\Enum\Document\DocumentStatusEnum;
use App\Enum\Document\DocumentStatusSignatureEnum;
use App\Enum\Order\OrderStatusEnum;
use App\Enum\Order\ReportStatusEnum;
use App\Models\Document\Document;
use App\Models\Document\RecognitionDocument;
use App\Models\Order\Bid;
use App\Models\Order\Report;
use App\Models\Setting;
use App\Services\CreatePdfFileService;
use App\Services\DocumentServices\CorrectRecognitionService;
use App\Services\Nopaper\NopaperService;
use App\Services\OneC\OneCServices;
use App\Services\DocumentCreator\PdfCreatorService;
use App\Services\PVP\TimeBook\TimeBookService;
use App\Services\PVP\Verme\VermeService;
use App\Services\PVP\XFive\XFiveService;
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

        $service = new VermeService();
        $user = User::query()->where('id',215)->first();

        $data = $service->getShifts();
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
        die();

        $ff= RecognitionDocument::first();
        echo "<pre>";
        var_dump($ff->user_id);
        echo "</pre>";
        die();
        $user = User::query()->where('id',215)->first();
        $service = new XFiveService();
        $data = [
            'inn'=>'1231231',
            'snils'=>'123123123',
            'pervp'=>'31231'
        ];
        $service->registerUser($user);
        die();














        $user = User::query()->where('id',215)->first();




        $service = new XFiveService();
        $data = [
            'inn'=>'1231231',
            'snils'=>'123123123',
            'pervp'=>'31231'
        ];
        $service->registerUser($user);



        die();


        $user = User::query()->where('id',215)->first();
        $user->email = 'd1234fvdfv1f@tt.tt';
        $user->data = '{"1": {"testitem": ["c0e077b8-d11d-11eb-85fa-6cb3110f7042"]}, "2": {"gov": "42463204-236a-11ef-8627-6cb3110f7041", "nalogstatus": "nalogstatus_fiz_lico"}, "3": {"2Vr2TFKPAmY12FHtnyqWr2Sngg9F6s": null, "2xC9RxW86VOvK1tlENocp26QDwJNQ1": null, "3tslFpWgadwSvvmDXQ6rvvc3aTfd47": "size52-54", "7nNCUNdoVpAy5x1gXYp6jgvrvxOSqm": "8746b32d-07e6-4c53-8e6b-7952da6f91ec", "87ONZiY1OqKYKiTsrKZ5q1y0KsuH2n": "6add2c03-fd57-463c-b85c-a1a761727d4c", "HP2kvziquWyhpAPv9UmhlkRaIISQWz": "a360f7bf-110f-41b5-89a1-a5b30228b27f", "MZ562TBT1VVWW7nYNJcPG4BlBFRXSc": "http://preprod.marriator-api.fivecorners.ru/storage/source/pdf/215/qr8CFgPPTp/[79152142632][Водительское удостоверение].pdf", "TA8vxv9JUcfHTtYC1Q3nFiZ4kWgULT": null, "bdM1RtrjFrn9STfGT3GG8AiU9NtqR2": "4416f6be-3ab8-11dc-8519-000423ba5914", "nCbrHGJ11nqZrAcBzMhQgJUsK5aJ4y": "http://preprod.marriator-api.fivecorners.ru/storage/source/pdf/215/DQ0oT2w5GN/[79152142632][Медицинский допуск к управлению ТС].pdf", "qFq7ZZ6ADUYcAV2f6hDaS35yJ3T65z": "85fd1e0a-3ab5-11dc-8519-000423ba5914"}, "4": {"staticEmail": "testo@mail.ru", "staticPhoto": "http://preprod.marriator-api.fivecorners.ru/storage/source/userImg/215/RtDMHN3XdXAziJSNA2SO.jpeg", "PuGyZOcha8UkkMywTQw2Wa4DcLlD5m": "directory_age_WKEjM9OSoYDngcJoalG68SqoRI0mpS", "QsZI3i3WLzO5rNO2ZJjXBtx9nJBosd": "tes", "R7ydKRH6YRdU85KI2UIql0EDWNyvnr": "directory_documentation_o8Tdo9sL762jqbHpd54aD6KK09sJtr", "X2CSwnQZntQEdPc1Xq5lgeLahytrna": "test", "c5gAyG7YPWV7RiCx23srwQnYV8bv5U": "http://preprod.marriator-api.fivecorners.ru/storage/source/pdf/215/UJD6YswHV7/[79152142632][Документ удостоверяющий личность].pdf", "pnIXSuSWPMsx1T5IVWU7x9SNBZ7Ss4": "mess-Viber", "qfyZsDpYNPdRGxZFdPrbNPZhR5oHI5": false, "unC20BLqzsZbEEGWlnT663EkueUBUi": "male_gender"}}';
        $user->save();
        $user->data = '{"1": {"testitem": ["c0e077b8-d11d-11eb-85fa-6cb3110f742"]}, "2": {"gov": "42463204-236a-11ef-8627-6cb3110f741", "nalogstatus": "nalogstatus_fiz_lic"}, "3": {"2Vr2TFKPAmY12FHtnyqWr2Sngg9F6s": null, "2xC9RxW86VOvK1tlENocp26QDwJNQ1": null, "3tslFpWgadwSvvmDXQ6rvvc3aTfd47": "size52-54", "7nNCUNdoVpAy5x1gXYp6jgvrvxOSqm": "8746b32d-07e6-4c53-8e6b-7952da6f91ec", "87ONZiY1OqKYKiTsrKZ5q1y0KsuH2n": "6add2c03-fd57-463c-b85c-a1a761727d4c", "HP2kvziquWyhpAPv9UmhlkRaIISQWz": "a360f7bf-110f-41b5-89a1-a5b30228b27f", "MZ562TBT1VVWW7nYNJcPG4BlBFRXSc": "http://preprod.marriator-api.fivecorners.ru/storage/source/pdf/215/qr8CFgPPTp/[79152142632][Водитеьское удостовее].pdf", "TA8vxv9JUcfHTtYC1Q3nFiZ4kWgULT": null, "bdM1RtrjFrn9STfGT3GG8AiU9NtqR2": "4416f6be-3ab8-11dc-8519-000423ba5914", "nCbrHGJ11nqZrAcBzMhQgJUsK5aJ4y": "http://preprod.marriator-api.fivecorners.ru/storage/source/pdf/215/DQ0oT2w5GN/[79152142632][Медицинский допуск к управлению ТС].pdf", "qFq7ZZ6ADUYcAV2f6hDaS35yJ3T65z": "85fd1e0a-3ab5-11dc-8519-000423ba5914"}, "4": {"staticEmail": "testo@mail.r", "staticPhoto": "http://preprod.marriator-api.fivecorners.ru/storage/source/userImg/215/RtDMHN3XdXAziJSNA2S.jpeg", "PuGyZOcha8UkkMywTQw2Wa4DcLlD5m": "directory_age_WKEjM9OSoYDngcJoalG68SqoRI0mpS", "QsZI3i3WLzO5rNO2ZJjXBtx9nJBosd": "tes", "R7ydKRH6YRdU85KI2UIql0EDWNyvnr": "directory_documentation_o8Tdo9sL762jqbHpd54aD6KK09sJtr", "X2CSwnQZntQEdPc1Xq5lgeLahytrna": "test", "c5gAyG7YPWV7RiCx23srwQnYV8bv5U": "http://preprod.marriator-api.fivecorners.ru/storage/source/pdf/215/UJD6YswHV7/[79152142632][Документ удостоверяющий личность].pdf", "pnIXSuSWPMsx1T5IVWU7x9SNBZ7Ss4": "mess-Viber", "qfyZsDpYNPdRGxZFdPrbNPZhR5oHI5": false, "unC20BLqzsZbEEGWlnT663EkueUBUi": "male_gende"}}';
        $user->save();

        die();







        $service = new TimeBookService();
       $data = $service->createOrganization([
           'guid' => '550e8400-e29b-41d4-a716-441655440001',
           'name' => 'name',
           'serialNumber' => '213'
       ]);
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
        die();



        //




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
        $data = $nopaper->checkUserExists($user);
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
