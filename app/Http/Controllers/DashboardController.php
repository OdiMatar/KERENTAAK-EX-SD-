<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $latestLogs = DB::table('technical_logs')
            ->leftJoin('users', 'technical_logs.user_id', '=', 'users.id')
            ->select([
                'technical_logs.action',
                'technical_logs.message',
                'technical_logs.created_at',
                'users.name as user_name',
            ])
            ->latest('technical_logs.created_at')
            ->limit(8)
            ->get();

        return view('dashboard.index', [
            'latestLogs' => $latestLogs,
        ]);
    }
}
