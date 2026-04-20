<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public $select;
    public function __construct()
    {
        $this->select = ['id', 'first_name', 'last_name', 'cover', 'mobile_number'];
    }

    public function me()
    {
        $data = User::select($this->select)->with('roles')->find(auth('api')->user()->id);
        return Helper::jsonResponse(true, 'User details fetched successfully', 200, $data);
    }

    public function updateProfile(Request $request)
    {

        $validatedData = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',

        ]);


        $user = auth('api')->user();

        if ($request->hasFile('cover')) {
            if (!empty($user->cover)) {
                Helper::fileDelete(public_path($user->getRawOriginal('cover')));
            }
            $validatedData['cover'] = Helper::fileUpload($request->file('cover'), 'user/cover', getFileName($request->file('cover')));
        } else {
            $validatedData['cover'] = $user->cover;
        }

        $user->update($validatedData);

        $data = User::select($this->select)->with('roles')->find($user->id);


        return Helper::jsonResponse(true, 'Profile updated successfully', 200, $data);
    }

    public function updateAvatar(Request $request)
    {
        $validatedData = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);
        $user = auth('api')->user();
        if (!empty($user->avatar)) {
            Helper::fileDelete(public_path($user->getRawOriginal('avatar')));
        }
        $validatedData['avatar'] = Helper::fileUpload($request->file('avatar'), 'user/avatar', getFileName($request->file('avatar')));
        $user->update($validatedData);
        $data = User::select($this->select)->with('roles')->find($user->id);
        return Helper::jsonResponse(true, 'Avatar updated successfully', 200, $data);
    }

    public function delete()
    {
        $user = User::findOrFail(auth('api')->id());
        if (!empty($user->avatar) && file_exists(public_path($user->avatar))) {
            Helper::fileDelete(public_path($user->avatar));
        }
        Auth::logout('api');
        $user->delete();
        return Helper::jsonResponse(true, 'Profile deleted successfully', 200);
    }

    public function destroy()
    {
        $user = User::findOrFail(auth('api')->id());
        if (!empty($user->avatar) && file_exists(public_path($user->avatar))) {
            Helper::fileDelete(public_path($user->avatar));
        }
        Auth::logout('api');
        $user->forceDelete();
        return Helper::jsonResponse(true, 'Profile deleted successfully', 200);
    }



    // user list
    public function user_list(Request $request)
    {
        $query = User::select($this->select)
            ->where('id', '!=', auth('api')->id())
            ->with('roles');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                    ->orWhere('last_name', 'like', '%' . $request->search . '%')
                    ->orWhere('mobile_number', 'like', '%' . $request->search . '%');
            });
        } else {
            $query->whereNull('email');
        }

        $users = $query->paginate(10);

        return Helper::jsonResponse(true, 'User list fetched successfully', 200, $users);
    }

}
