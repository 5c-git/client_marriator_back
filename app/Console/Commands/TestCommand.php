<?php

namespace App\Console\Commands;


use App\Enum\Document\DocumentStatusEnum;
use App\Enum\Document\DocumentStatusSignatureEnum;
use App\Enum\Order\OrderStatusEnum;
use App\Enum\Order\ReportStatusEnum;
use App\Models\Document\Document;
use App\Models\Document\RecognitionDocument;
use App\Models\Fields\Directory\Counterparty;
use App\Models\Fields\Directory\Project;
use App\Models\Order\Bid;
use App\Models\Order\Order;
use App\Models\Order\Report;
use App\Models\Setting;
use App\Services\CreatePdfFileService;
use App\Services\DocumentCreator\UserDocumentCreatorService;
use App\Services\DocumentServices\CorrectRecognitionService;
use App\Services\Nopaper\NopaperService;
use App\Services\OneC\OneCServices;
use App\Services\DocumentCreator\PdfCreatorService;
use App\Services\PVP\PVPService;
use App\Services\PVP\TimeBook\TimeBookService;
use App\Services\PVP\Verme\VermeService;
use App\Services\PVP\XFive\XFiveService;
use App\Services\Register\EmailService;
use App\Services\TimeService;
use App\Services\User\DataForDocumentCreatorService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TestCommand extends Command
{
    protected $signature = 'test';

    protected $description = '';

    public function handle(): void
    {

        $service = new VermeService();
        $data = $service->getData();
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
        die();



        $service = new TimeBookService();
        $r = RecognitionDocument::query()->first();
        $user = User::query()->where('id',$r->user_id)->first();
        $data = $service->getData();
        //$data = $service->assignToShift('f',$user);
//        $bid = new Bid();
//        $bid->external_id = 'external_id';
//        $data = $service->getTimesheets($user,$bid);
//        $data = $service->cancelAssignment($bid->external_id);
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
        die();


//        $service = new VermeService();
//        $data = $service->getShifts();
//        echo "<pre>";
//        var_dump($data);
//        echo "</pre>";
//        die();
//        $r = RecognitionDocument::query()->where('user_id','!=',172)->first();

        $user = User::query()->where('id',$r->user_id)->first();
        $bid = new Bid();
        $bid->external_id = '3d07e6b6-4260-7a55-2c09-107a681fc0d3';
//        echo "<pre>";
//        var_dump($service->getData());
//        echo "</pre>";
////        $res = $service->createEmployee($user);
////        echo "<pre>";
////        var_dump($res);
////        echo "</pre>";
//        die();
         $data = $service->getTimesheets($user,$bid);
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
        die();




        $r = RecognitionDocument::query()->first();
        $user = User::query()->where('id',$r->user_id)->first();
        $service = new VermeService();
        //$data = $service->registerUser($user);
        $data = $service->assignToShift($user,'71ade6e8-bcce-4c1f-ad4d-498a5f55dda8');//a2d2125e-70ec-40ed-83a5-03a2c6494058
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
        die();

        $service = new UserDocumentCreatorService();
        $counterparty = Counterparty::query()->where('id',1)->first();
        $r = RecognitionDocument::query()->first();
        $user = User::query()->where('id',$r->user_id)->first();
        $result = $service->createContract($user,$counterparty);
        echo "<pre>";
        var_dump($result);
        echo "</pre>";
        die();

        $user = User::where('id',172)->first();
        $userData = '{"gov": "982d33e2-bee6-453a-992e-11d13fa66fa1", "testitem": ["c0e077b8-d11d-11eb-85fa-6cb3110f704122", "c0e077b8-d11d-11eb-85fa-6cb3110f704121", "c0e077b8-d11d-11eb-85fa-6cb3110f704120", "c0e077b8-d11d-11eb-85fa-6cb3110f704119", "c0e077b8-d11d-11eb-85fa-6cb3110f704118", "c0e077b8-d11d-11eb-85fa-6cb3110f704117", "c0e077b8-d11d-11eb-85fa-6cb3110f704116", "c0e077b8-d11d-11eb-85fa-6cb3110f704115", "c0e077b8-d11d-11eb-85fa-6cb3110f704114", "c0e077b8-d11d-11eb-85fa-6cb3110f704113", "c0e077b8-d11d-11eb-85fa-6cb3110f704112", "c0e077b8-d11d-11eb-85fa-6cb3110f704111", "c0e077b8-d11d-11eb-85fa-6cb3110f704110", "c0e077b8-d11d-11eb-85fa-6cb3110f704109", "c0e077b8-d11d-11eb-85fa-6cb3110f70411", "c0e077b8-d11d-11eb-85fa-6cb3110f7041", "c0e077b8-d11d-11eb-85fa-6cb3110f7040", "c0e077b8-d11d-11eb-85fa-6cb3110f7039", "c0e077b8-d11d-11eb-85fa-6cb3110f7039", "c0e077b8-d11d-11eb-85fa-6cb3110f7038"], "vidideayt": ["directory_view_activities_e7JEmQrGSvQ2JCDJlgTd09jNoiFfGm", "directory_view_activities_AudJFcNa8lg8QJVLmHQjGqXxe4T1ZY", "directory_view_activities_DJZ8mtC9ZcV4H1Fb1w3gGiU9sBLH8u"], "nalogstatus": "nalogstatus_fiz_lico", "staticEmail": "test121@mail.ru", "staticPhoto": "http://preprod.marriator-api.fivecorners.ru/storage/source/userImg/406/iFd0ZnKTlG1da5WiFdAG.jpeg", "2Vr2TFKPAmY12FHtnyqWr2Sngg9F6s": null, "2xC9RxW86VOvK1tlENocp26QDwJNQ2": null, "3tslFpWgadwSvvmDXQ6rvvc3aTfd47": "size44-46", "5nibSuUwMDHHB965TRu8iT4exCxhRN": "a3eddd98-67f3-11eb-85ec-6cb3110f7042", "7nNCUNdoVpAy5x1gXYp6jgvrvxOSqm": "67a2687f-4eff-454e-902f-5759e15ed948", "87ONZiY1OqKYKiTsrKZ5q1y0KsuH2n": "b3014396-3310-49d8-8d8d-4961de6f53ec", "9nuDjP3c3Ule99uIiPArhyE1rssGHF": "111111111111", "HP2kvziquWyhpAPv9UmhlkRaIISQWz": "211d7de5-2c72-46a6-bfa5-9396d08e5a50", "IloSAoeA5hNj6iKQuM3saaBSmw7nvC": "11111111111", "MZ562TBT1VVWW7nYNJcPG4BlBFRXSc": "http://preprod.marriator-api.fivecorners.ru/storage/source/pdf/406/foh4qY4LHU/[79881234455][Водительское удостоверение].pdf", "P93JuDTcWlnJfgOJOmM1VtP9vSVnK8": null, "PuGyZOcha8UkkMywTQw2Wa4DcLlD5m": "directory_age_dobJSzuU94Ta9RitXi4MV2Y7sxhI3G", "QsZI3i3WLzO5rNO2ZJjXBtx9nJBosd": "ТЕСТЕР", "QtbukuJ4ALhNnMCwBQvA9PE5pIu6Zh": false, "R7ydKRH6YRdU85KI2UIql0EDWNyvnr": "directory_documentation_o8Tdo9sL762jqbHpd54aD6KK09sJtr", "TA8vxv9JUcfHTtYC1Q3nFiZ4kWgULT": null, "WJWEOeCGKJMKHNrlDbat5QfuEXXo4a": null, "X2CSwnQZntQEdPc1Xq5lgeLahytrna": "СПЕЦИАЛИСТ", "bdM1RtrjFrn9STfGT3GG8AiU9NtqR2": "4416f6bc-3ab8-11dc-8519-000423ba5914", "c5gAyG7YPWV7RiCx23srwQnYV8bv5U": "http://preprod.marriator-api.fivecorners.ru/storage/source/pdf/406/RDZ5T42P3y/[79881234455][Документ удостоверяющий личность].pdf", "cjQR6vTQzEopYaOmlvFyAKaplVQlh4": "http://preprod.marriator-api.fivecorners.ru/storage/source/pdf/406/UvFGtIv53e/[79881234455][Адрес регистрации].pdf", "n1bZyr8bWZYmCOcT5KFQg1MRqvQzj2": "11111111111111111", "nCbrHGJ11nqZrAcBzMhQgJUsK5aJ4y": "http://preprod.marriator-api.fivecorners.ru/storage/source/pdf/406/EdrKSf4tei/[79881234455][Медицинский допуск к управлению ТС].pdf", "pnIXSuSWPMsx1T5IVWU7x9SNBZ7Ss4": "mess-Telegram", "qFq7ZZ6ADUYcAV2f6hDaS35yJ3T65z": "85fd1e08-3ab5-11dc-8519-000423ba5914", "qfyZsDpYNPdRGxZFdPrbNPZhR5oHI5": false, "unC20BLqzsZbEEGWlnT663EkueUBUi": "female_gender", "vk0wcCKTq7sP67Iybq19sLyJckCZzz": ["territoriya_poiska_predlozhenij_77", "territoriya_poiska_predlozhenij_16"], "xId3LKEIZ1w1hidPtsoEP2jPLvJoCz": false}';
        $user->data = $userData;
        $user->save();

        die();



        $service = new UserDocumentCreatorService();
        $r = RecognitionDocument::query()->first();
        $user = User::query()->where('id',$r->user_id)->first();
        $project = Project::query()->where('id',11)->first();
        $counterpartyId = $project->counterparties()->first()->id;
        $counterparty = Counterparty::query()->where('id',$counterpartyId)->first();
        //$user->project()->sync($project->id);
        $order = new Order();
        $order->user_id =$user->id;
        $result = $service->createContract($user,$counterparty);
        echo "<pre>";
        var_dump($result);
        echo "</pre>";
        die();



                $service = new TimeBookService();
        $data = $service->createOrganization([
            'guid' => '550e8400-e29b-41d4-a716-441655440004',
            'name' => 'БИЭНТИ Альянс',
            'serialNumber' => '216'
        ]);
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
        $data = $service->createSubdivision([
            'guid' => '550e8400-e29b-41d4-a716-441655440005',
            'name' => 'БИЭНТИ Альянс',
            'serialNumber' => '217',
            'organizationGuid' => '550e8400-e29b-41d4-a716-441655440004'
        ]);
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
        $data = $service->createStaffPosition([
            'guid' => '550e8400-e29b-41d4-a716-441655440006',
            'name' => 'БИЭНТИ Альянс',
            'serialNumber' => '218',
            'organizationGuid' => '550e8400-e29b-41d4-a716-441655440004'
        ]);
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
        die();


        $service = new TimeBookService();
        //$r = RecognitionDocument::query()->first();
//        $user = User::query()->where('id',$r->user_id)->first();
//        $user->time_book_guid = 'bc9b833d-1fa8-4b6a-82b0-868af4f07fc9';
//        $user->save();
//        $service->createEmployee($user);
        $data = $service->getData();
        echo "<pre>";
        var_dump($data);
        echo "</pre>";


        die();

        $pvpService = new XFiveService();
        $user = User::query()->where('email','saida@5corners.ru')->first();
        //$data = $pvpService->getData();
        $data = $pvpService->assignToShift($user,112542200);
        //112542200
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
        $data = $pvpService->getChanges();
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
        die();

        $pvpService = new PVPService(new VermeService());
        //$pvpService->startLoad();

        $r = RecognitionDocument::query()->first();
        $user = User::query()->where('id',$r->user_id)->first();
        $pvpService->assignToShift($user,'cf1946a9-d93b-4fa4-9468-2d1b913fad5f');//a2d2125e-70ec-40ed-83a5-03a2c6494058
        die();
        //assignToShift
//        echo "<pre>";
//        var_dump(Carbon::parse('20250901080000')->format('Y-m-d H:i:s'));
//        echo "</pre>";
//        die();
//        $service = new TimeBookService();
//        $data = $service->createOrganization([
//            'guid' => '550e8400-e29b-41d4-a716-441655440001',
//            'name' => 'name',
//            'serialNumber' => '213'
//        ]);
//        $data = $service->createSubdivision([
//            'guid' => '550e8400-e29b-41d4-a716-441655440002',
//            'name' => 'name',
//            'serialNumber' => '214',
//            'organizationGuid' => '550e8400-e29b-41d4-a716-441655440001'
//        ]);
//        $data = $service->createStaffPosition([
//            'guid' => '550e8400-e29b-41d4-a716-441655440003',
//            'name' => 'name',
//            'serialNumber' => '215',
//            'organizationGuid' => '550e8400-e29b-41d4-a716-441655440001'
//        ]);
//        echo "<pre>";
//        var_dump($data);
//        echo "</pre>";
//        die();

//        $r = RecognitionDocument::query()->first();
//        $user = User::query()->where('id',$r->user_id)->first();
//        $service = new VermeService();
//        $data = $service->registerUser($user);
//        echo "<pre>";
//        var_dump($data);
//        echo "</pre>";
//
//        $data = $service->assignToShift($user,'1c6e8200-1933-4b44-8a65-b35cc7221149');
//        echo "<pre>";
//        var_dump($data);
//        echo "</pre>";
//        $bid = new Bid();
//        $bid->external_id = '1c6e8200-1933-4b44-8a65-b35cc7221149';
//        $bid->date_start = Carbon::parse('2026-01-23 05:00:00');
//        $bid->date_end = Carbon::parse('2026-01-23 14:00:00');
//        $pvpService = new PVPService(new VermeService());
//        $pvpService->getResultData($user,$bid);


        die();


//        $user = User::query()->first();
//        $sourcePath = public_path('nameTest.pdf');
//        $destinationPath = "source/document/".$user->id."/".date('YmdHis').rand(1000000,9999999).'testDoc.pdf';
//        $fileContent = File::get($sourcePath);
//        Storage::disk('public')->put($destinationPath, $fileContent);
//        $fileUrl = Storage::url($destinationPath);
//        $doc = new Document();
//        $doc->uuid = Str::uuid();
//        $doc->user_id = $user->id;
//        $doc->file_name = date('YmdHis').rand(1000000,9999999).'testDoc.pdf';
//        $doc->file_path = $fileUrl;
//        $doc->status = DocumentStatusEnum::Signed->value;
//        $doc->status_signature = DocumentStatusSignatureEnum::NoSend->value;
//        $doc->date_signature = Carbon::now();
//        $doc->save();
        die();


        $pvpService = new PVPService(new VermeService());
        $pvpService->startLoad();

        die();


       $r = RecognitionDocument::query()->first();
        $user = User::query()->where('id',$r->user_id)->first();
        $service = new XFiveService();
        $data = [
            'inn'=>'1231231',
            'snils'=>'123123123',
            'pervp'=>'31231'
        ];
        $data = $service->registerUser($user);
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
