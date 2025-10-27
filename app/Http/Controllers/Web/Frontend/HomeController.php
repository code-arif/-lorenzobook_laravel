<?php

namespace App\Http\Controllers\Web\Frontend;

use App\Enums\PageEnum;
use App\Http\Controllers\Controller;
use App\Models\CMS;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Support\Str;

use function Symfony\Component\String\b;

class HomeController extends Controller
{
    public function index()
    {
        // $cms = [
        //     'home' => CMS::where('page', PageEnum::HOME)->where('status', 'active')->get(),
        //     'common' => CMS::where('page', PageEnum::COMMON)->where('status', 'active')->get(),
        // ];

        // $posts = Post::where('status', 'active')->paginate(9);

        // return view('frontend.layouts.index', compact('cms', 'posts'));

        return view('auth.login');
    }

    public function post($slug){
        $cms = [
            'home' => CMS::where('page', PageEnum::HOME)->where('status', 'active')->get(),
            'common' => CMS::where('page', PageEnum::COMMON)->where('status', 'active')->get(),
        ];
        $post = Post::where('slug', base64_decode($slug))->where('status', 'active')->firstOrFail();
        return view('frontend.layouts.post', compact('cms', 'post'));
    }

    public function policies(){
        $cms = Page::where('slug', 'privacy-policy')->where('status', 'active')->firstOrFail();
        return view('frontend.layouts.policies', compact('cms'));
    }



}
