<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $days = collect(range(0, 29))->map(function ($i) {
            return now()->subDays($i)->format('Y-m-d');
        })->reverse()->values();

        $roomCounts = \App\Models\Room::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');

        $groupCounts = \App\Models\Group::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');

        $channelCounts = \App\Models\Channel::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');

        $formatted_data = $days->mapWithKeys(function ($date) use ($roomCounts, $groupCounts, $channelCounts) {
            return [
                $date => [
                    'rooms' => (int) ($roomCounts[$date] ?? 0),
                    'groups' => (int) ($groupCounts[$date] ?? 0),
                    'channels' => (int) ($channelCounts[$date] ?? 0),
                ]
            ];
        });

        file_put_contents(public_path('transactions.json'), $formatted_data->toJson());

        $all_users = \App\Models\User::count();
        $all_groups = \App\Models\Group::count();
        $all_channels = \App\Models\Channel::count();
        $all_rooms = \App\Models\Room::count();

        return view('backend.layouts.dashboard', [
            'all_users' => $all_users,
            'all_groups' => $all_groups,
            'all_channels' => $all_channels,
            'all_rooms' => $all_rooms
        ]);
    }
}
