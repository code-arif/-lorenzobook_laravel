<?php

use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\UserController;
use App\Http\Controllers\Api\ChannelManageController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\FirebaseTokenController;
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
// Route::get('/category', [categoryController::class, 'index']);
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

/**
 * Auth Route
 */
Route::group(['middleware' => 'guest:api'], function ($router) {
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('/verify-mobile-otp', [RegisterController::class, 'verifyPhoneOtp']);
    Route::post('/resend-otp', [RegisterController::class, 'resendPhoneOtp']);
});

Route::group(['middleware' => 'auth:api'], function ($router) {
    Route::get('/me', [UserController::class, 'me']);
    Route::post('/update-profile', [UserController::class, 'updateProfile']);
    // Route::post('/update-avatar', [UserController::class, 'updateAvatar']);
    Route::get('/user/list', [UserController::class, 'user_list']);
    Route::get('/logout', [LogoutController::class, 'logout']);
    Route::post('/update-username', [UserController::class, 'updateUsername']);
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
Route::group(['prefix' => 'auth/chat', 'middleware' => 'auth:api'], function () {
    Route::get('/list', [ChatController::class, 'list']);
    Route::post('/send/{receiver_id}', [ChatController::class, 'send']);
    Route::get('/conversation/{receiver_id}', [ChatController::class, 'conversation']);
    Route::get('/room/{receiver_id}', [ChatController::class, 'room']);
    Route::get('/search', [ChatController::class, 'search']);
    Route::get('/seen/all/{receiver_id}', [ChatController::class, 'seenAll']);
    Route::get('/seen/single/{chat_id}', [ChatController::class, 'seenSingle']);

    Route::get('/conversation/{receiver_id}/search', [ChatController::class, 'searchConversation']); // done
    Route::delete('/conversation/{receiver_id}/clear-history', [ChatController::class, 'clearHistory']); // done
    Route::delete('/conversation/{receiver_id}/delete', [ChatController::class, 'deleteConversation']); // done
    Route::post('/mute/{receiver_id}', [ChatController::class, 'muteConversation']); //done
    Route::post('/unmute/{receiver_id}', [ChatController::class, 'unmuteConversation']); //done

    // delete specific message
    Route::delete('/message/{message_id}/delete', [ChatController::class, 'deleteMessage']);
    Route::post('/messages/delete-multiple', [ChatController::class, 'deleteMultipleMessages']);
});

// group chat manage
Route::middleware('auth:api')->prefix('auth/group')->group(function () {
    // Group CRUD
    Route::get('/list', [GroupController::class, 'list']);
    Route::post('/create', [GroupController::class, 'create']);
    Route::get('/show/{group_id}', [GroupController::class, 'show']); // group details with members
    Route::post('/update/{group_id}', [GroupController::class, 'update']);
    Route::delete('/delete/{group_id}', [GroupController::class, 'destroy']);

    // Member Management
    Route::post('/member/add/{group_id}', [GroupController::class, 'addMember']);
    Route::post('/member/remove/{group_id}', [GroupController::class, 'removeMember']);
    Route::post('/member/leave/{group_id}', [GroupController::class, 'leaveMember']);
    Route::post('/member/mute/{group_id}', [GroupController::class, 'muteMember']);
    Route::post('/member/unmute/{group_id}', [GroupController::class, 'unmuteMember']);
    Route::post('/member/ban/{group_id}', [GroupController::class, 'banMember']);
    Route::post('/member/unban/{group_id}', [GroupController::class, 'unbanMember']);
    Route::patch('/member/promote/{group_id}/{user_id}', [GroupController::class, 'promoteMember']);
    Route::patch('/member/demote/{group_id}/{user_id}', [GroupController::class, 'demoteMember']);

    Route::get('/search/{group_id}', [GroupController::class, 'searchMessages']);
});

// Group Chat
Route::middleware('auth:api')->prefix('group/chat')->group(function () {
    Route::post('/send/{group_id}', [GroupChatController::class, 'sendGroupMessage']);
    Route::get('/messages/{group_id}', [GroupChatController::class, 'getGroupMessages']);

    // Message management
    Route::delete('/clear-history/{group_id}', [GroupChatController::class, 'clearGroupChatHistory']); // done
    Route::delete('/message/delete/{message_id}', [GroupChatController::class, 'deleteGroupMessage']); // done
    Route::post('/messages/delete-multiple', [GroupChatController::class, 'deleteMultipleGroupMessages']); // done
});

// channel management
Route::middleware(['auth:api'])->controller(ChannelManageController::class)->prefix('auth/channel')->group(function () {
    Route::get('/list', 'list');
    Route::post('/store', 'store');
    Route::post('channel_type/{channel_id}', 'setType');


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
