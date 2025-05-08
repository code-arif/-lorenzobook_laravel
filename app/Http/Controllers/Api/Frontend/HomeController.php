<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Enums\PageEnum;
use App\Enums\SectionEnum;
use App\Helpers\Helper;
use App\Models\CMS;
use App\Models\Setting;

class HomeController extends Controller
{
    public function index()
    {
        $data = [];

        $cmsItems = CMS::where('page', PageEnum::HOME)
                    ->where('status', 'active')
                    ->whereIn('section', [SectionEnum::HOME_BANNER, SectionEnum::HOME_BANNERS])
                    ->get();

        $data['home_banner']    = $cmsItems->where('section', SectionEnum::HOME_BANNER)->first();
        $data['home_banners']   = $cmsItems->where('section', SectionEnum::HOME_BANNERS)->values();
        $data['common']         = CMS::where('page', PageEnum::COMMON)->where('status', 'active')->get();
        $data['settings']       = Setting::first();

        return Helper::jsonResponse(true, 'Home Page', 200, $data);

    }
}
