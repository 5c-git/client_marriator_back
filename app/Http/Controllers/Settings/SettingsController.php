<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SettingResource;
use Illuminate\Http\Request;
use App\Models\Setting;


class SettingsController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * @OA\Get(
     *     path="/api/settings/getFromKey/",
     *     operationId="get settings value for key",
     *     tags={"settings"},
     *     summary="Получить значение настроек по ключу",
     *     description="Метод для получениея конкретных данных из настроек",
     *     @OA\Parameter(
     *         name="key",
     *         in="query",
     *         description="Ключ из настроек",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="Успешный запрос",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":"значение"},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getFromKey(Request $request)
    {
        if (!empty($request->key)) {
            $setting = Setting::query()->where('key', $request->key)->first();
            return new SettingResource($setting);
        }
        return new ErrorResource();
    }

    /**
     * @OA\Get(
     *     path="/api/settings/getAll/",
     *     operationId="get all settings keys and values",
     *     tags={"settings"},
     *     summary="Получить всех значений настроек",
     *     description="Метод для получениея всех данных из настроек",
     *     @OA\Response(
     *       response="200",
     *       description="Успешный запрос",
     *       @OA\JsonContent(
     *           @OA\Examples(example="result", value={"status": "success","result":{{"key": "tel","value": "+453453"},{"key": "cor","value": "10"}}},summary="Успех"),
     *       )
     *     ),
     * )
     */

    public function getAll(){
        return SettingResource::collection(Setting::query()->get());
    }

}
