<?php

namespace App\Http\Controllers\Admin\Page\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\Log\LogRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use App\Traits\Log\Loggable;
use Illuminate\Support\Facades\File;


class LogController extends Controller
{
    use Loggable;

    public function index()
    {
        $logsPath = storage_path('logs');

        $folders = array_filter(glob($logsPath . '/*'), 'is_dir');

        $result = [];

        foreach ($folders as $folderPath) {
            $folderName = basename($folderPath);

            $files = array_filter(glob($folderPath . '/*'), 'is_file');

            $result[$folderName] = [];

            foreach ($files as $filePath) {
                $fileName = basename($filePath);

                $result[$folderName][$fileName] = route('downloadLog', [
                    'folder' => $folderName,
                    'file'   => $fileName,
                ]);
            }
        }

        return view('admin.system.log.index', compact('result'));
    }


    public function download($folder, $file)
    {
        $path = storage_path("logs/{$folder}/{$file}");
        if (file_exists($path) && str_starts_with(realpath($path), storage_path('logs'))) {
            return response()->download($path);
        }
        abort(404);
    }

}
