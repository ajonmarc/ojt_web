<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ApplicationController extends Controller
{
    public function api_application(Request $request)
    {
        try {
            $application = Application::where('user_id', $request->user()->id)
                ->with('partner')
                ->latest()
                ->first();

            return response()->json([
                'existingApplication' => $application ? [
                    'id' => $application->id,
                    'status' => $application->status,
                    'resume_path' => $application->resume_path,
                    'letter_path' => $application->letter_path,
                    'preferred_company' => $application->preferred_company,
                    'start_date' => $application->start_date ? $application->start_date->format('Y-m-d') : null,
                    'end_date' => $application->end_date ? $application->end_date->format('Y-m-d') : null,
                    'remarks' => $application->remarks,
                    'partner' => $application->partner ? [
                        'name' => $application->partner->name,
                    ] : null,
                ] : null,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Application fetch error: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Failed to fetch application'], 500);
        }
    }

    public function submit(Request $request)
    {
        try {
            $request->validate([
                'resume' => 'required|file|max:5120', // 5MB max
                'applicationLetter' => 'required|file|max:5120', // 5MB max
                'otherDocuments.*' => 'nullable|file|max:5120', // Optional other documents
                'startDate' => 'required|date',
                'endDate' => 'required|date|after:startDate',
                'preferredCompany' => 'nullable|string|max:255',
            ]);

            // Store files
            $resumePath = $request->file('resume')->store('applications/resumes', 'public');
            $letterPath = $request->file('applicationLetter')->store('applications/letters', 'public');
            $otherDocsPaths = [];
            if ($request->hasFile('otherDocuments')) {
                foreach ($request->file('otherDocuments') as $doc) {
                    $otherDocsPaths[] = $doc->store('applications/other', 'public');
                }
            }

            // Create application record
            $application = Application::create([
                'user_id' => $request->user()->id,
                'resume_path' => $resumePath,
                'letter_path' => $letterPath,
                'other_documents' => $otherDocsPaths ? json_encode($otherDocsPaths) : null,
                'preferred_company' => $request->preferredCompany,
                'start_date' => $request->startDate,
                'end_date' => $request->endDate,
                'status' => 'Pending',
            ]);

            return response()->json(['message' => 'Application submitted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Application submit error: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Failed to submit application'], 500);
        }
    }

    public function update(Request $request, Application $application)
    {
        try {
            if ($application->user_id !== $request->user()->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $request->validate([
                'resume' => 'nullable|file|max:5120',
                'applicationLetter' => 'nullable|file|max:5120',
                'otherDocuments.*' => 'nullable|file|max:5120',
                'startDate' => 'required|date',
                'endDate' => 'required|date|after:startDate',
                'preferredCompany' => 'nullable|string|max:255',
            ]);

            $data = [
                'preferred_company' => $request->preferredCompany,
                'start_date' => $request->startDate,
                'end_date' => $request->endDate,
            ];

            // Handle resume upload
            if ($request->hasFile('resume')) {
                Storage::disk('public')->delete($application->resume_path);
                $data['resume_path'] = $request->file('resume')->store('applications/resumes', 'public');
            }

            // Handle application letter upload
            if ($request->hasFile('applicationLetter')) {
                Storage::disk('public')->delete($application->letter_path);
                $data['letter_path'] = $request->file('applicationLetter')->store('applications/letters', 'public');
            }

            // Handle other documents
            if ($request->hasFile('otherDocuments')) {
                $existingDocs = $application->other_documents ? json_decode($application->other_documents, true) : [];
                foreach ($existingDocs as $doc) {
                    Storage::disk('public')->delete($doc);
                }
                $newDocs = [];
                foreach ($request->file('otherDocuments') as $doc) {
                    $newDocs[] = $doc->store('applications/other', 'public');
                }
                $data['other_documents'] = json_encode($newDocs);
            }

            $application->update($data);

            return response()->json(['message' => 'Application updated successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Application update error: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Failed to update application'], 500);
        }
    }

    public function delete(Request $request, Application $application)
    {
        try {
            if ($application->user_id !== $request->user()->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Delete associated files
            Storage::disk('public')->delete($application->resume_path);
            Storage::disk('public')->delete($application->letter_path);
            if ($application->other_documents) {
                $otherDocs = json_decode($application->other_documents, true);
                foreach ($otherDocs as $doc) {
                    Storage::disk('public')->delete($doc);
                }
            }

            $application->delete();

            return response()->json(['message' => 'Application deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Application delete error: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Failed to delete application'], 500);
        }
    }
}