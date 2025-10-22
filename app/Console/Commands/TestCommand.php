<?php

namespace App\Console\Commands;


use App\Enum\Order\OrderStatusEnum;
use App\Enum\Order\ReportStatusEnum;
use App\Models\Document\Document;
use App\Models\Order\Bid;
use App\Models\Order\Report;
use App\Services\CreatePdfFileService;
use App\Services\DocumentServices\CorrectRecognitionService;
use App\Services\Nopaper\NopaperService;
use App\Services\OneC\OneCServices;
use App\Services\Verme\VermeService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class TestCommand extends Command
{
    protected $signature = 'test';

    protected $description = '';

    public function handle(): void
    {
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
        $userData = [
            'phone'=> 79036650547,
            'email'=> 'fifka@mail.ru',
        ];

        $user = User::query()->where('id',172)->first();
        $doc = Document::query()->where('id',24)->first();
//32525
        $nopaper = new NopaperService();
        //$data = $nopaper->checkUserExists($user);
        //$data = $nopaper->sendDocumentsToNopaper($user);
        //$data = $nopaper->confirmSms($user,'000000');
        //$data = $nopaper->getDocumentInfo($doc);



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
