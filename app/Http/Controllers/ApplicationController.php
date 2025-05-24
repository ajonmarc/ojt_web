<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class ApplicationController extends Controller
{
    public function index()
    {
        $applications = Application::with(['user', 'partner'])->get();
        $partners = Partner::where('status', 'Active')->get();
        $students = User::where('role', 'student')
            ->whereDoesntHave('applications', function($query) {
                $query->whereIn('status', ['Pending', 'Approved']);
            })
            ->get();

        return Inertia::render('Admin/Application', [
            'applications' => $applications->map(function ($application) {
                return [
                    'id' => $application->id,
                    'user' => $application->user,
                    'partner' => $application->partner,
                    'applicationDate' => $application->application_date,
                    'startDate' => $application->start_date,
                    'endDate' => $application->end_date,
                    'requiredHours' => $application->required_hours,
                    'completedHours' => $application->completed_hours,
                    'status' => $application->status,
                    'remarks' => $application->remarks,
                    'hasResume' => !empty($application->resume_path),
                    'hasLetter' => !empty($application->letter_path),
                    'preferredCompany' => $application->preferred_company,
                ];
            }),
            'partners' => $partners->map(function ($partner) {
                return [
                    'id' => $partner->id,
                    'name' => $partner->name
                ];
            }),
            'students' => $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'studentId' => $student->studentId
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'partner_id' => 'required|exists:partners,id',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
            'requiredHours' => 'required|integer|min:1',
            'status' => 'required|in:Pending,Approved,Rejected',
            'remarks' => 'nullable|string',
        ]);

        $validated['application_date'] = now();
        $validated['completed_hours'] = 0;

        Application::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Application $application)
    {
        $validated = $request->validate([
            'status' => 'required|in:Pending,Approved,Rejected',
            'remarks' => 'nullable|string',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date|after:startDate',
            'requiredHours' => 'required|integer|min:1',
            'completedHours' => 'nullable|integer|min:0',
            'partner_id' => 'nullable|exists:partners,id',
        ]);

        $application->update($validated);

        return redirect()->back();
    }

    public function destroy(Application $application)
    {
        // Delete associated files
        if ($application->resume_path) {
            Storage::disk('public')->delete($application->resume_path);
        }
        if ($application->letter_path) {
            Storage::disk('public')->delete($application->letter_path);
        }

        $application->delete();
        return redirect()->back();
    }

    public function downloadResume(Application $application)
    {
        if (!$application->resume_path) {
            abort(404);
        }

        return Storage::disk('public')->download(
            $application->resume_path,
            'resume_' . $application->user->name . '.pdf'
        );
    }

    public function downloadLetter(Application $application)
    {
        if (!$application->letter_path) {
            abort(404);
        }

        return Storage::disk('public')->download(
            $application->letter_path,
            'application_letter_' . $application->user->name . '.pdf'
        );
    }
}
