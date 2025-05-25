<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Application;
use App\Models\Evaluation;
use App\Models\Progress;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function apiMobileDashboard(Request $request)
    {
        try {
            $user = $request->user(); // Authenticated user
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            // Get the student's application with partner info
            $application = Application::where('user_id', $user->id)
                ->with('partner')
                ->latest()
                ->first();

            // Calculate completion status if application is approved
            $completionData = null;
            if ($application && $application->status === 'Approved') {
                $totalHours = 500; // Default OJT hours requirement
                $completedHours = Progress::where('user_id', $user->id)
                    ->sum('hours_rendered');

                $completionData = [
                    'totalHours' => $totalHours,
                    'completedHours' => $completedHours,
                    'percentage' => $totalHours > 0 ? round(($completedHours / $totalHours) * 100, 1) : 0,
                ];
            }

            // Get recent evaluations
            $evaluations = Evaluation::where('student_id', $user->id)
                ->with('supervisor')
                ->latest()
                ->take(3)
                ->get();

            return response()->json([
                'student' => [
                    'name' => $user->name,
                    'studentId' => $user->studentId ?? 'N/A', // Fallback if student_id is null
                    'ojtProgram' => $user->ojtProgram ?? 'N/A',
                ],
                'application' => $application ? [
                    'status' => $application->status,
                    'remarks' => $application->remarks ?? null,
                    'start_date' => $application->start_date ? $application->start_date->format('Y-m-d') : null,
                    'end_date' => $application->end_date ? $application->end_date->format('Y-m-d') : null,
                    'required_hours' => 500, // Default value
                    'completed_hours' => $completionData ? $completionData['completedHours'] : 0,
                ] : null,
                'partner' => $application && $application->partner ? [
                    'partnerName' => $application->partner->name ?? 'N/A',
                    'partnerAddress' => $application->partner->address ?? 'N/A',
                    'partnerPhone' => $application->partner->phone ?? 'N/A',
                    'partnerEmail' => $application->partner->email ?? 'N/A',
                ] : null,
                'evaluations' => $evaluations->map(function ($eval) {
                    return [
                        'id' => $eval->id,
                        'date' => $eval->created_at ? $eval->created_at->format('Y-m-d') : null,
                        'supervisor' => $eval->supervisor->name ?? 'Unknown',
                        'score' => $eval->rating ?? 0, // Changed to match web version (rating instead of score)
                        'feedback' => $eval->feedback ?? 'No feedback provided',
                    ];
                })->toArray(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Dashboard API error: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }
}