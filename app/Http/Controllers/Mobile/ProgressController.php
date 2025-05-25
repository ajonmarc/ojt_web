<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Progress;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    public function progress()
    {
        $user = Auth::user();

        // Get latest application
        $application = Application::where('user_id', $user->id)
            ->latest()
            ->first();

        $completionData = null;

        if ($application && $application->status === 'Approved') {
            $totalHours = 500;
            $completedHours = Progress::where('user_id', $user->id)->sum('hours_rendered');

            $completionData = [
                'totalHours' => $totalHours,
                'completedHours' => $completedHours,
                'percentage' => $totalHours > 0 ? round(($completedHours / $totalHours) * 100, 1) : 0,
            ];
        }

        // Recent progress entries
        $recentProgress = Progress::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->take(5)
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'date' => $entry->date,
                    'hours' => $entry->hours_rendered,
                    'tasks' => $entry->tasks_completed,
                    'remarks' => $entry->remarks,
                ];
            });

        return response()->json([
            'auth' => [
                'user' => [
                    'name' => $user->name,
                    'studentId' => $user->studentId,
                    'ojtProgram' => $user->ojtProgram,
                ]
            ],
            'application' => $application ? [
                'status' => $application->status,
                'remarks' => $application->remarks,
                'startDate' => $application->start_date,
                'endDate' => $application->end_date,
                'requiredHours' => 500,
                'completedHours' => $completionData ? $completionData['completedHours'] : 0,
                'partner' => $application->preferred_company ? [
                    'name' => $application->preferred_company,
                    'address' => $application->preferred_company_address ?? 'N/A',
                ] : null,
            ] : null,
            'progress' => $application && $application->status === 'Approved' ? [
                'totalHours' => $completionData['totalHours'],
                'requiredHours' => 500,
                'completionPercentage' => $completionData['percentage'],
                'recentEntries' => $recentProgress,
            ] : null,
        ]);
    }
}
