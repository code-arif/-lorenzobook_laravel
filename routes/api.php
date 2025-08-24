<?php

use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\UserController;
use App\Http\Controllers\Api\Auth\SocialLoginController;
use App\Http\Controllers\Api\ChannelManageController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\FirebaseTokenController;
use App\Http\Controllers\Api\Frontend\categoryController;
use App\Http\Controllers\Api\Frontend\FaqController;
use App\Http\Controllers\Api\Frontend\FriendController;
use App\Http\Controllers\Api\Frontend\GroupChatController;
use App\Http\Controllers\Api\Frontend\HomeController;
use App\Http\Controllers\Api\Frontend\ImageController;
use App\Http\Controllers\Api\Frontend\PostController;
use App\Http\Controllers\Api\Frontend\SubcategoryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Frontend\SettingsController;
use App\Http\Controllers\Api\Frontend\SocialLinksController;
use App\Http\Controllers\Api\Frontend\SubscriberController;
use App\Http\Controllers\Api\GroupController;
use Illuminate\Support\Facades\Route;


//page
Route::get('/page/home', [HomeController::class, 'index']);

Route::get('/category', [categoryController::class, 'index']);
Route::get('/subcategory', [SubcategoryController::class, 'index']);

Route::get('/social/links', [SocialLinksController::class, 'index']);
Route::get('/settings', [SettingsController::class, 'index']);
Route::get('/faq', [FaqController::class, 'index']);
Route::post('subscriber/store', [SubscriberController::class, 'store'])->name('subscriber.store');

/*
# Post
*/
Route::middleware(['auth:api'])->controller(PostController::class)->prefix('auth/post')->group(function () {
    Route::get('/', 'index');
    Route::post('/store', 'store');
    Route::get('/show/{id}', 'show');
    Route::post('/update/{id}', 'update');
    Route::delete('/delete/{id}', 'destroy');
});

Route::get('/posts', [PostController::class, 'posts']);
Route::get('/post/show/{post_id}', [PostController::class, 'post']);

Route::middleware(['auth:api'])->controller(ImageController::class)->prefix('auth/post/image')->group(function () {
    Route::get('/', 'index');
    Route::post('/store', 'store');
    Route::get('/delete/{id}', 'destroy');
});

/*
# Auth Route
*/

Route::group(['middleware' => 'guest:api'], function ($router) {


    //register
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('/verify-mobile-otp', [RegisterController::class, 'verifyPhoneOtp']);
    Route::post('/resend-otp', [RegisterController::class, 'resendPhoneOtp']);
});

Route::group(['middleware' =>'auth:api'], function ($router) {

    Route::get('/me', [UserController::class, 'me']);
    Route::post('/update-profile', [UserController::class, 'updateProfile']);
    // Route::post('/update-avatar', [UserController::class, 'updateAvatar']);


    Route::get('/user/list', [UserController::class, 'user_list']);
});

/*
# Firebase Notification Route
*/

Route::middleware(['auth:api'])->controller(FirebaseTokenController::class)->prefix('firebase')->group(function () {
    Route::get("test", "test");
    Route::post("token/add", "store");
    Route::post("token/get", "getToken");
    Route::post("token/delete", "deleteToken");
})->middleware('auth:api');

/*
# In App Notification Route
*/

Route::middleware(['auth:api'])->controller(NotificationController::class)->prefix('notify')->group(function () {
    Route::get('test', 'test');
    Route::get('/', 'index');
    Route::get('status/read/all', 'readAll');
    Route::get('status/read/{id}', 'readSingle');
})->middleware('auth:api');





Route::middleware(['auth:api'])->controller(FriendController::class)->prefix('auth/friend')->group(function () {

    Route::get('/list', 'list');
    Route::post('/send', 'send');

    // friend details
    Route::get('/details/{id}', 'details');

});





/*
# Chat Route
*/



Route::middleware(['auth:api'])->controller(ChatController::class)->prefix('auth/chat')->group(function () {

    Route::get('/list', 'list');
    Route::post('/send/{receiver_id}', 'send');
    Route::get('/conversation/{receiver_id}', 'conversation');
    Route::get('/room/{receiver_id}', 'room');
    Route::get('/search', 'search');
    Route::get('/seen/all/{receiver_id}', 'seenAll');
    Route::get('/seen/single/{chat_id}', 'seenSingle');


});


// group chat manage
Route::middleware(['auth:api'])->controller(GroupChatController::class)->prefix('group/chat')->group(function () {


    Route::post('/send/{group_id}', 'sendGroupMessage');
    Route::get('/get-message/{group_id}', 'getGroupMessages');




});



Route::middleware(['auth:api'])->controller(GroupController::class)->prefix('auth/group')->group(function () {

    Route::get('/list', 'list');
    Route::post('/create', 'create');
    Route::get('/show/{group_id}', 'show');
    Route::post('/update/{group_id}', 'update');
    Route::delete('/delete/{group_id}', 'destroy');
    Route::post('add/member/{group_id}', 'addMember');
    Route::post('/remove/member/{group_id}', 'removeMember');


    Route::post('/leave/member/{group_id}', 'leaveMember');


    Route::post('/mute/member/{group_id}', 'muteMember');
    Route::post('/unmute/member/{group_id}', 'unmuteMember');
    Route::post('/ban/member/{group_id}', 'banMember');
    Route::post('/unban/member/{group_id}', 'unbanMember');

    // New routes
    Route::patch('/promote/member/{group_id}/{user_id}', 'promoteMember');
    Route::patch('/demote/member/{group_id}/{user_id}', 'demoteMember');
});



// channel management
Route::middleware(['auth:api'])->controller(ChannelManageController::class)->prefix('auth/channel')->group(function () {
    Route::get('/list', 'list');
    Route::post('/store', 'store');
    Route::post('channel_type/{channel_id}','setType');


    Route::get('/show/{channel_id}', 'show');
    Route::post('/update/{channel_id}', 'update');
    Route::delete('/delete/{channel_id}', 'destroy');



    Route::post('/add/subscriber/{channel_id}', 'addMember');
    Route::post('/subscriber/member/{channel_id}', 'removeMember');
    Route::post('/leave/subscriber/{channel_id}', 'leaveMember');
});








Route::post('/save-fcm-token', [FirebaseTokenController::class, 'test_token_store']);

Route::post('/send-call', [FirebaseTokenController::class, 'sendCall']);



/*
# CMS
*/

Route::prefix('cms')->name('cms.')->group(function () {
    Route::get('home', [HomeController::class, 'index'])->name('home');
});
