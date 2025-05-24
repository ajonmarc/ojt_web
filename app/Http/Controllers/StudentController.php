<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partner;
use App\Models\Evaluation;
use App\Models\Progress;
use App\Models\Application;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function studentDashboard(): Response
    {
        $user = auth()->user();
        
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

        return Inertia::render('Student/Dashboard', [
            'auth' => [
                'user' => [
                    'name' => $user->name,
                    'studentId' => $user->studentId,
                ]
            ],
            'application' => $application ? [
                'status' => $application->status,
                'remarks' => $application->remarks,
                'startDate' => $application->start_date,
                'endDate' => $application->end_date,
                'requiredHours' => 500, // Default value
                'completedHours' => $completionData ? $completionData['completedHours'] : 0,
            ] : null,
            'partner' => $application && $application->partner ? [
                'partnerName' => $application->partner->name,
                'address' => $application->partner->address,
            ] : null,
            'evaluations' => $evaluations->map(fn($eval) => [
                'id' => $eval->id,
                'date' => $eval->created_at->format('M d, Y'),
                'supervisor' => $eval->supervisor->name ?? 'Unknown',
                'rating' => $eval->rating ?? 'N/A',
                'feedback' => $eval->feedback ?? 'No feedback provided',
            ]),
        ]);
    }

    public function studentProfile(): Response
    {
        return Inertia::render('Student/Profile');
    }

    public function studentInfo(): Response
    {
        return Inertia::render('Student/Info');
    }

    public function studentEvaluation(): Response
    {
        return Inertia::render('Student/Evaluation');
    }

    public function studentProgress(): Response
    {
        $user = auth()->user();
        
        // Get the student's application with partner info
        $application = Application::where('user_id', $user->id)
            ->with('partner')
            ->latest()  // Get the most recent application
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
        
        // Get recent progress entries
        $recentProgress = Progress::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->take(5)
            ->get()
            ->map(function($entry) {
                return [
                    'id' => $entry->id,
                    'date' => $entry->date,
                    'hours' => $entry->hours_rendered,
                    'tasks' => $entry->tasks_completed,
                    'remarks' => $entry->remarks
                ];
            });

        return Inertia::render('Student/Progress', [
            'auth' => [
                'user' => [
                    'name' => $user->name,
                    'studentId' => $user->studentId,
                ]
            ],
            'application' => $application ? [
                'status' => $application->status,
                'remarks' => $application->remarks,
                'startDate' => $application->start_date,
                'endDate' => $application->end_date,
                'requiredHours' => 500, // Default value
                'completedHours' => $completionData ? $completionData['completedHours'] : 0,
                'partner' => $application->partner ? [
                    'name' => $application->partner->name,
                    'address' => $application->partner->address,
                ] : null,
            ] : null,
            'progress' => $application && $application->status === 'Approved' ? [
                'totalHours' => $completionData['totalHours'],
                'requiredHours' => 500,
                'completionPercentage' => $completionData['percentage'],
                'recentEntries' => $recentProgress
            ] : null
        ]);
    }

    public function application()
    {
        $application = Application::where('user_id', auth()->id())
            ->with('partner')
            ->latest()
            ->first();

        return Inertia::render('Student/Application', [
            'existingApplication' => $application
        ]);
    }

    public function submitApplication(Request $request)
    {
        $request->validate([
            'resume' => 'required|file|max:5120', // 5MB max
            'applicationLetter' => 'required|file|max:5120', // 5MB max
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
            'preferredCompany' => 'nullable|string|max:255',
        ]);

        // Store files
        $resumePath = $request->file('resume')->store('applications/resumes', 'public');
        $letterPath = $request->file('applicationLetter')->store('applications/letters', 'public');

        // Create application record
        $application = Application::create([
            'user_id' => auth()->id(),
            'resume_path' => $resumePath,
            'letter_path' => $letterPath,
            'preferred_company' => $request->preferredCompany,
            'start_date' => $request->startDate,
            'end_date' => $request->endDate,
            'status' => 'Pending'
        ]);

        return redirect()->route('student.progress')->with('success', 'Application submitted successfully!');
    }

    public function updateApplication(Request $request, Application $application)
    {
        // Verify ownership
        if ($application->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'resume' => 'nullable|file|max:5120',
            'applicationLetter' => 'nullable|file|max:5120',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
            'preferredCompany' => 'nullable|string|max:255',
        ]);

        $data = [
            'preferred_company' => $request->preferredCompany,
            'start_date' => $request->startDate,
            'end_date' => $request->endDate,
        ];

        // Handle resume upload if provided
        if ($request->hasFile('resume')) {
            Storage::disk('public')->delete($application->resume_path);
            $data['resume_path'] = $request->file('resume')->store('applications/resumes', 'public');
        }

        // Handle application letter upload if provided
        if ($request->hasFile('applicationLetter')) {
            Storage::disk('public')->delete($application->letter_path);
            $data['letter_path'] = $request->file('applicationLetter')->store('applications/letters', 'public');
        }

        $application->update($data);

        return redirect()->route('student.progress')->with('success', 'Application updated successfully!');
    }

    public function deleteApplication(Application $application)
    {
        // Verify ownership
        if ($application->user_id !== auth()->id()) {
            abort(403);
        }

        // Delete associated files
        Storage::disk('public')->delete($application->resume_path);
        Storage::disk('public')->delete($application->letter_path);

        $application->delete();

        return redirect()->route('student.progress')->with('success', 'Application deleted successfully!');
    }
}
