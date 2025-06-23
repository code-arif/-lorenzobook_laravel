<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;

class UserListController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::select('id', 'name','email', 'avatar', 'created_at','status')
                        ->where('role', 'user')
                        ->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('avatar', function ($data) {
                    if ($data->avatar) {
                        $url = asset($data->avatar);
                        return '<img src="' . $url . '" alt="avatar" width="50px" height="50px">';
                    } else {
                        return '---';
                    }
                })
                ->addColumn('email', function ($data) {
                    return $data->email ?? '---';
                })

                ->addColumn('created_at', function ($data) {
                    return $data->created_at ? $data->created_at->format('Y-m-d') : '---';
                })
                ->rawColumns(['avatar', 'email', 'city', 'created_at'])
                ->make(true);
        }
        return view("backend.layouts.user.index");
    }
}
