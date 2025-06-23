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

        $all_months = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

        $transactions = Transaction::select(
            DB::raw("MONTHNAME(created_at) as month"),
            DB::raw("SUM(CASE WHEN type = 'increment' THEN amount ELSE 0 END) as increment_total"),
            DB::raw("SUM(CASE WHEN type = 'decrement' THEN amount ELSE 0 END) as decrement_total")
        )
            ->where('status', 'success')
            ->groupBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    strtolower($item->month) => [
                        'increment' => number_format($item->increment_total, 2),
                        'decrement' => number_format($item->decrement_total, 2)
                    ]
                ];
            });

        $formatted_data = collect($all_months)->mapWithKeys(function ($month) use ($transactions) {
            return [
                $month => $transactions->get($month, ['increment' => '0.00', 'decrement' => '0.00'])
            ];
        });

        if (file_exists(public_path('transactions.json'))) {
            file_put_contents(public_path('transactions.json'), $formatted_data->toJson());
        }

        $all_users = User::count();
        $all_groups = DB::table('groups')->count();
        $all_channels = DB::table('channels')->count();
        $all_rooms = DB::table('rooms')->count();

        return view('backend.layouts.dashboard', [
            'all_users' => $all_users,
            'all_groups' => $all_groups,
            'all_channels' => $all_channels,
            'all_rooms' => $all_rooms

        ]);
    }
}
