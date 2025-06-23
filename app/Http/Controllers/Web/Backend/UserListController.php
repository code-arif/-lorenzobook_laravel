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
            $data = User::select('id', 'first_name','last_name' ,'email','mobile_number','cover', 'created_at')->whereNull('email')->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name' , function ($data) {
                    return $data->first_name . ' ' . $data->last_name;
                })
                ->addColumn('cover', function ($data) {
                    if ($data->cover) {
                        $url = asset($data->cover);
                        return '<img src="' . $url . '" alt="cover" width="50px" height="50px">';
                    } else {
                        return '---';
                    }
                })
                ->addColumn('created_at', function ($data) {
                    return $data->created_at ? $data->created_at->format('Y-m-d') : '---';
                })
                ->rawColumns(['name','cover','mobile_number', 'city', 'created_at'])
                ->make(true);
        }
        return view("backend.layouts.user.index");
    }
}
