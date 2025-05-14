<?php

namespace App\Http\Controllers\UserRoles;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaceResource;
use App\Models\User;
use App\Services\ApiTokenService\ApiTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\Register\SmsCodeService;
use App\Models\Fields\Directory\Project;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\BrandResource;
use Illuminate\Support\Collection;
use App\Http\Requests\SetBrandImgRequest;
use App\Http\Resources\SuccessResource;
use App\Http\Requests\SetPlaceRequest;

class ClientController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function getBrand()
    {
        $brands = Auth::user()->project
            ->flatMap(fn($project) => $project->brands)
            ->unique('id');
        return BrandResource::collection($brands);
    }

    public function setBrandImg(SetBrandImgRequest $request)
    {
        $user = Auth::user();
        $brands = Auth::user()->project
            ->flatMap(fn($project) => $project->brands)
            ->unique('id')?->where('id',$request->brandId)?->first();

        if(!empty($brands)){
            $user->img = $brands->logo;
            $user->save();
        }
        return new SuccessResource();
    }

    public function getPlace()
    {
        $places = Auth::user()->project
            ->flatMap(fn($project) => $project->places)
            ->unique('id');
        return PlaceResource::collection($places);
    }

    public function setPlace(SetPlaceRequest $request)
    {
        $user = Auth::user();
        $place = $user->project
            ->flatMap(fn($project) => $project->places)
            ->unique('id')->where('id',$request->placeId)->first();
        if(!empty($place)) {
            $user->place()->sync([$place->id]);
            $user->save();
        }

        return new SuccessResource();
    }

}
