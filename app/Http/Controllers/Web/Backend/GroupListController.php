<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Group;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;

class GroupListController extends Controller
{
     public function index(Request $request)
    {
    //  $data = Group::select('id', 'name', 'image_url', 'group_type', 'created_by', 'created_at')->with('createdBy')->get();

    //  dd($data);

        if ($request->ajax()) {
            $data = Group::select('id', 'name', 'image_url', 'group_type', 'created_by', 'created_at')->with('createdBy')->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('created_by', function ($data) {
                    return $data->createdBy ? $data->createdBy->first_name . ' ' . $data->createdBy->last_name : '---';
                })
                ->addColumn('image_url', function ($data) {
                    if ($data->image_url) {
                        $url = asset($data->image_url);
                        return '<img src="' . $url . '" alt="group image" width="50px" height="50px">';
                    } else {
                        return '---';
                    }
                })
                ->addColumn('created_at', function ($data) {
                    return $data->created_at ? $data->created_at->format('Y-m-d') : '---';
                })
                ->rawColumns(['image_url', 'created_by', 'created_at'])
                ->make(true);
        }
        return view("backend.layouts.group.index");
    }
}
