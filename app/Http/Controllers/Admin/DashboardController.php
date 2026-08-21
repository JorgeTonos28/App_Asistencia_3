<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Participant;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $stats = [
            'total_events' => Event::count(),
            'active_events' => Event::where('status', 'active')->count(),
            'total_participants' => Participant::count(),
            'total_attendances' => Attendance::count(),
            'today_attendances' => Attendance::whereDate('check_in_at', $today)->count(),
        ];

        // Eventos activos y recientes
        $activeEvents = Event::where('status', 'active')
            ->withCount('attendances')
            ->orderBy('event_date', 'asc')
            ->take(5)
            ->get();

        // Asistencias recientes en tiempo real
        $recentAttendances = Attendance::with(['participant', 'event'])
            ->latest('check_in_at')
            ->take(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'activeEvents', 'recentAttendances'));
    }
}
