@php
use \Illuminate\Support\Str;
use App\Enums\PageEnum;
use App\Enums\SectionEnum;
$cms_banner = $cms['home']->firstWhere('section', SectionEnum::HOME_BANNER);
$cms_banners = $cms['home']->where('section', SectionEnum::HOME_BANNERS)->values();
@endphp

@extends('frontend.app', ['title' => 'landing page'])
@section('content')

<!--heeder-->
<div class="demo-screen-headline main-demo main-demo-1 overflow-hidden pb-0 mb-2" id="home">
    <div class="container px-5 px-md-0">
        <div class="overflow-hidden">
            <div class="row">
                <div class="col-lg-6 text-left pos-relative overflow-hidden p-3">
                    <h1 class="text-shadow text-dark">{{ $cms_banner->title ?? 'Album example' }}</h1>
                    <h6 class="mt-3">{!! $cms_banner->description ?? 'Something short and leading about the collection below—its contents, the creator, etc. Make it short and sweet, but not too short so folks don’t simply skip over it entirely.' !!}</h6>
                    <a href="{{ $cms_banner->btn_link ?? '#' }}" class="btn btn-pill btn-primary btn-w-md py-2 me-2 mb-1">{{ $cms_banner->btn_text ?? 'Main call to action' }}<i class="fe fe-activity ms-2"></i></a>
                    <a href="#" class="btn btn-pill btn-secondary btn-w-md py-2 mb-1">Demo<i
                            class="fe fe-file-text mx-2"></i></a>
                </div>
                <div class="col-lg-6 text-left pos-relative overflow-hidden market-image">
                    <img alt="" class="logo-2" src="{{ asset($cms_banner->image ?? 'frontend/images/landing/market.png') }}">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container px-2 px-md-0 mb-5">
    <div class="row gy-2">
        @if ($cms_banners->count() > 0)
        @foreach ($cms_banners as $item)
        <div class="col-lg-3 col-sm-6 d-flex align-items-center">
            <div class="card shadow-sm border-0 overflow-hidden w-100">
                <div class="card-body d-flex align-items-center p-3">
                    <a href="#" class="d-block w-100 h-100">
                        <img class="img-fluid mx-auto d-block" src="{{ $item->image ?? 'default/logo.svg' }}" alt="">
                    </a>
                    <div class="text-center">
                        <h5 class="mt-3">{{ $item->metadata['rating'] ?? '5' }}</h5>
                        <h5 class="mt-3">{{ $item->title ?? 'Example headline.' }}</h5>
                        <p class="fs-13">{!! $item->description ?? 'Some representative placeholder content for the first slide of the carousel.' !!}</p>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        @else
        @for ($i = 1; $i <= 4; $i++)
            <div class="col-lg-3 col-sm-6 d-flex align-items-center">
            <div class="card shadow-sm border-0 overflow-hidden w-100">
                <div class="card-body d-flex align-items-center p-3">
                    <a href="#" class="d-block w-100 h-100">
                        <img class="img-fluid mx-auto d-block" src="{{ asset('default') }}/logo.svg" alt="">
                    </a>
                    <div class="text-center">
                        <h5 class="mt-3">{{ $i }}. Example headline</h5>
                        <p class="fs-13">Some representative placeholder content for slide {{ $i }} of the carousel.</p>
                    </div>
                </div>
            </div>
    </div>
    @endfor
    @endif
</div>
</div>
<!--heeder end-->

<div class="hor-content main-content mt-0">
    <div class="side-app">
        <!-- CONTAINER -->
        <div class="main-container">

            <!-- Clients section-->
            <div class="bg-primary section bg-white" id="Clients">
                <div class="container">
                    <div class="row justify-content-center text-center">
                        <div class="col-lg-8 text-center">
                            <h3 class="header-family text-white">Clients are our most important assets.</h3>
                            <p class="sub-text text-white pb-3">We have the best client in the market who thrives us to perform better.
                            </p>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-lg-12">
                            <div class="customer-logos">
                                <div class="slide"><img src="{{ asset('frontend') }}/images/landing/companies/apple.png" alt=""></div>
                                <div class="slide"><img src="{{ asset('frontend') }}/images/landing/companies/chrome.png" alt=""></div>
                                <div class="slide"><img src="{{ asset('frontend') }}/images/landing/companies/google.png" alt=""></div>
                                <div class="slide"><img src="{{ asset('frontend') }}/images/landing/companies/edge.png" alt=""></div>
                                <div class="slide"><img src="{{ asset('frontend') }}/images/landing/companies/firefox.png" alt=""></div>
                                <div class="slide"><img src="{{ asset('frontend') }}/images/landing/companies/opera.png" alt=""></div>
                                <div class="slide"><img src="{{ asset('frontend') }}/images/landing/companies/safari.png" alt=""></div>
                                <div class="slide"><img src="{{ asset('frontend') }}/images/landing/companies/bing.png" alt=""></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Clients section Close-->

            <!-- Features -->
            <div class="section bg-white pb-7" id="Features">
                <div class="container">
                    <div class="row text-center justify-content-center">
                        <div class="col-lg-8 ps-4">
                            <h3 class="header-family">Posts</h3>
                            <p class="text-default sub-text">The Noa admin template comes with ready-to-use features that are
                                completely easy-to-use for any user, even for a beginner.</p>
                        </div>
                    </div>
                    <div class="row">
                        @foreach ($posts as $post)
                        <div class="col-12 col-md-4 p-4 fanimate">
                            <div class="features-icon mt-3 mb-3">
                                <img src="{{ $post->thumbnail ? asset($post->thumbnail) : asset('default/logo.svg') }}" alt="Post Image" class="img-fluid" style="width: 50px; max-height: 50px;">
                            </div>
                            <h4 class="mx-1">{{ $post->name ?? 'Unnamed Project' }}</h4>
                            <p class="text-muted mb-3 mx-1">{!! Str::limit($post->content ?? 'No description available', 50) !!}</p>
                            <a class="mx-1" href="{{ route('post.show', base64_encode($post->slug)) }}">Read More...</a>
                        </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $posts->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
            <!-- Features Close-->

        
            <!-- Contact-->
            <div class="bg-image-landing bg-white section" id="Contact">
                <div class="container">
                    <div class="row text-center justify-content-center">
                        <div class="col-lg-8">
                            <h3 class="header-family">Contact us</h3>
                            <p class="text-default sub-text">You can get in touch any time.</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="card mt-3 mb-0">
                            <div class="card-body text-dark px-0 pb-0">
                                <div class="statistics-info">
                                    <div class="row">
                                        <div class="col-md-6 col-sm-5">
                                            <div class="">
                                                <div class="text-dark">
                                                    <div class="services-statistics">
                                                        <div class="row text-center">
                                                            <div class="col-xl-6 col-md-6 col-lg-6">
                                                                <div class="card p-0">
                                                                    <div class="card-body p-0">
                                                                        <div class="row">
                                                                            <div class="col-xl-3 col-md-3">
                                                                                <div
                                                                                    class="counter-icon border border-primary text-primary">
                                                                                    <i class="fe fe-map-pin"></i>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xl-6 col-md-9 px-0 mb-1">
                                                                                <h5 class="mb-1 fw-semibold">Main Branch</h5>
                                                                                <p class="fs-13 text-muted">San Francisco, CA
                                                                                </p>
                                                                            </div>
                                                                            <div class="col-xl-3">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xl-6 col-md-6 col-lg-6">
                                                                <div class="card p-0">
                                                                    <div class="card-body p-0">
                                                                        <div class="row">
                                                                            <div class="col-xl-3 col-md-3">
                                                                                <div
                                                                                    class="counter-icon border border-primary text-primary">
                                                                                    <i class="fe fe-headphones"></i>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xl-6 col-md-9 px-0 mb-1">
                                                                                <h5 class="mb-1 fw-semibold">Phone & Email</h5>
                                                                                <p class="mb-0 fs-13 text-muted">+125 254 3562
                                                                                </p>
                                                                                <p class="fs-13 text-muted">georgeme@abc.com</p>
                                                                            </div>
                                                                            <div class="col-xl-3"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xl-6 col-md-6 col-lg-6">
                                                                <div class="card p-0">
                                                                    <div class="card-body p-0">
                                                                        <div class="row">
                                                                            <div class="col-xl-3 col-md-3">
                                                                                <div
                                                                                    class="counter-icon border border-primary text-primary">
                                                                                    <i class="fe fe-mail"></i>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xl-6 col-md-9 px-0 mb-1">
                                                                                <h5 class="mb-1 fw-semibold">Contact</h5>
                                                                                <p class="mb-0 fs-13 text-muted">www.example.com
                                                                                </p>
                                                                                <p class="fs-13 text-muted">example@dev.com</p>
                                                                            </div>
                                                                            <div class="col-xl-3"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xl-6 col-md-6 col-lg-6">
                                                                <div class="card p-0">
                                                                    <div class="card-body p-0">
                                                                        <div class="row">
                                                                            <div class="col-xl-3 col-md-3">
                                                                                <div
                                                                                    class="counter-icon border border-primary text-primary">
                                                                                    <i class="fe fe-airplay"></i>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xl-6 col-md-9 px-0 mb-1">
                                                                                <h5 class="mb-1 fw-semibold">Working Hours</h5>
                                                                                <p class="mb-0 fs-13 text-muted">Mon - Fri: 9am
                                                                                    - 6pm</p>
                                                                                <p class="fs-13 text-muted">Sat - sun: Holiday
                                                                                </p>
                                                                            </div>
                                                                            <div class="col-xl-3"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-7">
                                            <div class="">
                                                <form action="{{ route('contact.store') }}" method="post" class="form-horizontal  m-t-20 row">
                                                    @csrf
                                                    <div class="col-xl-12">
                                                        <div class="form-group">
                                                            <input name="name" class="form-control" type="text" required placeholder="Name">
                                                            @error('name')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-12">
                                                        <div class="form-group">
                                                            <input name="email" class="form-control" type="email" required placeholder="Email">
                                                            @error('email')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-12">
                                                        <div class="form-group">
                                                            <input name="subject" class="form-control" type="text" required placeholder="Subject">
                                                            @error('subject')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-12">
                                                        <div class="form-group">
                                                            <textarea name="message" class="form-control" rows="5" required placeholder="Your Comment"></textarea>
                                                            @error('message')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <button type="submit" class="btn btn-pill btn-primary btn-w-sm py-2  waves-effect waves-light">Submit</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Contact close-->

            <!--Support-->
            <!-- <div class="demo-screen-skin bg-primary">
                <div class="container text-center text-white">
                    <div id="demo" class="row">
                        <div class="col-lg-6">
                            <div class="feature-1">
                                <a href="#"></a>
                                <div class="mb-3">
                                    <i class="si si-earphones-alt"></i>
                                </div>
                                <h4 class="fs-25">Get Support</h4>
                                <p class="mb-1 text-white">Need Help? Don't worry. Please visit our support website. Our
                                    dedicated team will help you.</p>
                                <h6 class="mb-0">Support : <a class="text-dark"
                                        href="mailto:support@spruko.com">support@spruko.com</a></h6>
                            </div>
                        </div>
                        <div class="col-lg-6 mt-5 mt-xl-0 mt-lg-0">
                            <div class="feature-1">
                                <a href="#"></a>
                                <div class="mb-3">
                                    <i class="si si-bubbles"></i>
                                </div>
                                <h4 class="fs-25">Pre-Sale Questions</h4>
                                <p class="mb-1 text-white">Please feel free to ask any questions before making the purchase.</p>
                                <h6 class="mb-0">Ask : <a class="text-dark"
                                        href="mailto:support@spruko.com">support@spruko.com</a></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
            <!--Support close-->

            <!--Subscribe-->
            <div class="demo-screen-skin bg-primary">
                <div class="container text-center text-white">
                    <div id="demo" class="row">
                        <div class="col-lg-12">
                            <form action="{{ route('subscriber.store') }}" method="post">
                                @csrf
                                <div class="text-center">
                                    <h1 class="display-4 fw-bold mb-4">Stay Connected</h1>
                                    <div class="d-flex justify-content-center">
                                        <div class="col-md-4 my-2">
                                            <div class="input-group">
                                                <span class="input-group-text bg-primary text-white">@</span>
                                                <input type="email" name="email" class="form-control" placeholder="Enter your email" aria-label="Email">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-4">
                                        <button type="submit" class="btn btn-success btn-lg px-5 fw-bold">Subscribe Now</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!--Subscribe close-->
        </div>
    </div>
    <!-- CONTAINER CLOSED -->
</div>
@endsection