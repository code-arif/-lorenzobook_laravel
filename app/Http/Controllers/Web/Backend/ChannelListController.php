<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Channel;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;

class ChannelListController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Channel::select('id', 'name', 'image_url', 'channel_type', 'created_by', 'created_at')->with('createdBy')->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('created_by', function ($data) {
                    return $data->createdBy ? $data->createdBy->first_name . ' ' . $data->createdBy->last_name : '---';
                })
                ->addColumn('image_url', function ($data) {
                    if ($data->image_url) {
                        $url = asset($data->image_url);
                        return '<img src="' . $url . '" alt="channel image" width="50px" height="50px">';
                    } else {
                        return '---';
                    }
                })
                ->addColumn('channel_type', function ($data) {
                    return ucfirst($data->channel_type);
                })
                ->addColumn('created_at', function ($data) {
                    return $data->created_at ? $data->created_at->format('Y-m-d') : '---';
                })
                ->rawColumns(['image_url', 'created_by', 'created_at'])
                ->make(true);
        }
        return view("backend.layouts.channel.index");
    }
}
