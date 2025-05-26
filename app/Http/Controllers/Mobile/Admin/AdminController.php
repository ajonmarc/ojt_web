<?php

namespace App\Http\Controllers\Mobile\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Program;
use App\Models\Application;
use App\Models\Partner;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function admin_home()
    {
        // Fetch stats
        $totalStudents = User::where('role', 'student')->count();
        $totalPrograms = Program::count();
        $activeStudents = User::where('role', 'student')->where('status', 'Active')->count();
        $pendingApplications = Application::where('status', 'Pending')->count();

        // Fetch recent students (limit to 5 for example)
        $recentStudents = User::where('role', 'student')
            ->select('id', 'name', 'ojtProgram as program', 'status')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Fetch program statistics
        $programStats = Program::select('id', 'programName')
            ->withCount([
                'users as totalStudents' => function ($query) {
                    $query->where('role', 'student');
                },
                'users as activeStudents' => function ($query) {
                    $query->where('role', 'student')->where('status', 'Active');
                }
            ])
            ->get();

        // Prepare response
        $response = [
            'stats' => [
                'totalStudents' => $totalStudents,
                'totalPrograms' => $totalPrograms,
                'activeStudents' => $activeStudents,
                'pendingApplications' => $pendingApplications,
            ],
            'recentStudents' => $recentStudents,
            'programStats' => $programStats,
        ];

        return response()->json($response);
    }

    public function students()
    {   
        $students = User::where('role', 'student')
            ->select('id', 'studentId', 'name', 'email', 'studentPhone as phone', 'ojtProgram as program', 'status')
            ->orderBy('created_at', 'desc')
            ->get();

        // Fetch programs
        $programs = Program::select('id', 'programName')
            ->where('status', 'Active')
            ->get();

        // Fetch stats for AdminHome (reusing from previous request)
        $totalStudents = User::where('role', 'student')->count();
        $activeStudents = User::where('role', 'student')->where('status', 'Active')->count();
        $pendingApplications = Application::where('status', 'Pending')->count();

        $response = [
            'students' => $students,
            'programs' => $programs,
            'stats' => [
                'totalStudents' => $totalStudents,
                'activeStudents' => $activeStudents,
                'inactiveStudents' => $totalStudents - $activeStudents,
            ],
        ];

        return response()->json($response);
    }

    public function storeStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'studentId' => 'required|string|max:191|unique:users,studentId',
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|max:191',
            'program' => 'required|string|exists:programs,programName',
            'status' => 'required|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $student = User::create([
            'studentId' => $request->studentId,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'studentPhone' => $request->phone,
            'ojtProgram' => $request->program,
            'status' => $request->status,
            'role' => 'student',
        ]);

        return response()->json([
            'message' => 'Student added successfully',
            'student' => [
                'id' => $student->id,
                'studentId' => $student->studentId,
                'name' => $student->name,
                'email' => $student->email,
                'phone' => $student->studentPhone,
                'program' => $student->ojtProgram,
                'status' => $student->status,
            ],
        ], 201);
    }

    public function updateStudent(Request $request, $id)
    {
        $student = User::where('role', 'student')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'studentId' => 'required|string|max:191|unique:users,studentId,' . $id,
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'phone' => 'required|string|max:191',
            'program' => 'required|string|exists:programs,programName',
            'status' => 'required|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $student->update([
            'studentId' => $request->studentId,
            'name' => $request->name,
            'email' => $request->email,
            'studentPhone' => $request->phone,
            'ojtProgram' => $request->program,
            'status' => $request->status,
            'password' => $request->password ? Hash::make($request->password) : $student->password,
        ]);

        return response()->json([
            'message' => 'Student updated successfully',
            'student' => [
                'id' => $student->id,
                'studentId' => $student->studentId,
                'name' => $student->name,
                'email' => $student->email,
                'phone' => $student->studentPhone,
                'program' => $student->ojtProgram,
                'status' => $student->status,
            ],
        ]);
    }

    // Delete a student
    public function deleteStudent($id)
    {
        $student = User::where('role', 'student')->findOrFail($id);
        $student->delete();

        return response()->json(['message' => 'Student deleted successfully']);
    }

    // Fetch programs for the programs management page
    public function programs()
    {
        // Fetch programs
        $programs = Program::select('id', 'programName', 'programDescription as description')
            ->orderBy('created_at', 'desc')
            ->get();

        // Prepare response
        return response()->json([
            'programs' => $programs,
            'stats' => [
                'totalPrograms' => $programs->count(),
            ],
        ]);
    }

    // Add a new program
    public function storeProgram(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'programName' => 'required|string|max:191|unique:programs,programName',
            'description' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $program = Program::create([
            'programName' => $request->programName,
            'programDescription' => $request->description,
            'status' => 'Active', // Default status as per your schema
        ]);

        return response()->json([
            'message' => 'Program added successfully',
            'program' => [
                'id' => $program->id,
                'programName' => $program->programName,
                'description' => $program->programDescription,
            ],
        ], 201);
    }

    // Update an existing program
    public function updateProgram(Request $request, $id)
    {
        $program = Program::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'programName' => 'required|string|max:191|unique:programs,programName,' . $id,
            'description' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $program->update([
            'programName' => $request->programName,
            'programDescription' => $request->description,
        ]);

        return response()->json([
            'message' => 'Program updated successfully',
            'program' => [
                'id' => $program->id,
                'programName' => $program->programName,
                'description' => $program->programDescription,
            ],
        ]);
    }

    // Delete a program
    public function deleteProgram($id)
    {
        $program = Program::findOrFail($id);

        // Check if any students are associated with this program
        $studentCount = User::where('ojtProgram', $program->programName)->count();
        if ($studentCount > 0) {
            return response()->json(['message' => 'Cannot delete program with associated students'], 400);
        }

        $program->delete();

        return response()->json(['message' => 'Program deleted successfully']);
    }

    // Fetch partners for the partners management page
    public function partners()
    {
        // Fetch partners
        $partners = Partner::select('id', 'partnerName', 'partnerAddress as address', 'partnerPhone as phone', 'partnerEmail as email', 'partnerContact as contactPerson', 'status')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate stats
        $totalPartners = $partners->count();
        $activePartners = $partners->where('status', 'Active')->count();
        $inactivePartners = $partners->where('status', 'Inactive')->count();

        return response()->json([
            'partners' => $partners,
            'stats' => [
                'totalPartners' => $totalPartners,
                'activePartners' => $activePartners,
                'inactivePartners' => $inactivePartners,
            ],
        ]);
    }

    // Add a new partner
    public function storePartner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'partnerName' => 'required|string|max:191|unique:partners,partnerName',
            'address' => 'required|string|max:191',
            'phone' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:partners,partnerEmail',
            'contactPerson' => 'required|string|max:191',
            'status' => 'required|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $partner = Partner::create([
            'partnerName' => $request->partnerName,
            'partnerAddress' => $request->address,
            'partnerPhone' => $request->phone,
            'partnerEmail' => $request->email,
            'partnerContact' => $request->contactPerson,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Partner added successfully',
            'partner' => [
                'id' => $partner->id,
                'partnerName' => $partner->partnerName,
                'address' => $partner->partnerAddress,
                'phone' => $partner->partnerPhone,
                'email' => $partner->partnerEmail,
                'contactPerson' => $partner->partnerContact,
                'status' => $partner->status,
            ],
        ], 201);
    }

    // Update an existing partner
    public function updatePartner(Request $request, $id)
    {
        $partner = Partner::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'partnerName' => 'required|string|max:191|unique:partners,partnerName,' . $id,
            'address' => 'required|string|max:191',
            'phone' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:partners,partnerEmail,' . $id,
            'contactPerson' => 'required|string|max:191',
            'status' => 'required|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $partner->update([
            'partnerName' => $request->partnerName,
            'partnerAddress' => $request->address,
            'partnerPhone' => $request->phone,
            'partnerEmail' => $request->email,
            'partnerContact' => $request->contactPerson,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Partner updated successfully',
            'partner' => [
                'id' => $partner->id,
                'partnerName' => $partner->partnerName,
                'address' => $partner->partnerAddress,
                'phone' => $partner->partnerPhone,
                'email' => $partner->partnerEmail,
                'contactPerson' => $partner->partnerContact,
                'status' => $partner->status,
            ],
        ]);
    }

    // Delete a partner
    public function deletePartner($id)
    {
        $partner = Partner::findOrFail($id);

        // Optional: Check if partner is associated with applications or other entities
        // For now, allow deletion as no such constraints are specified
        $partner->delete();

        return response()->json(['message' => 'Partner deleted successfully']);
    }

    // Fetch applications for the applications management page
    public function applications(Request $request)
    {
        $applications = Application::with(['user:id,studentId,name,ojtProgram', 'partner:id,partnerName'])
            ->select('id', 'user_id', 'partner_id', 'status', 'resume_path', 'letter_path', 'application_date', 'start_date', 'end_date', 'required_hours', 'remarks')
            ->orderBy('application_date', 'desc')
            ->get()
            ->map(function ($application) {
                return [
                    'id' => $application->id,
                    'studentName' => $application->user->name,
                    'studentId' => $application->user->studentId,
                    'program' => $application->user->ojtProgram,
                    'applicationDate' => $application->application_date ? $application->application_date->format('Y-m-d') : null,
                    'hasResume' => !empty($application->resume_path),
                    'hasLetter' => !empty($application->letter_path),
                    'status' => $application->status,
                    'partnerId' => $application->partner_id,
                    'partnerName' => $application->partner ? $application->partner->partnerName : null,
                    'startDate' => $application->start_date ? $application->start_date->format('Y-m-d') : null,
                    'endDate' => $application->end_date ? $application->end_date->format('Y-m-d') : null,
                    'requiredHours' => $application->required_hours,
                    'remarks' => $application->remarks,
                ];
            });

        $students = User::where('role', 'student')
            ->select('id', 'studentId', 'name', 'ojtProgram as program')
            ->orderBy('name')
            ->get();

        $partners = Partner::select('id', 'partnerName')
            ->where('status', 'Active')
            ->orderBy('partnerName')
            ->get();

        $stats = [
            'totalApplications' => $applications->count(),
            'pendingApplications' => $applications->where('status', 'Pending')->count(),
            'approvedApplications' => $applications->where('status', 'Approved')->count(),
            'rejectedApplications' => $applications->where('status', 'Rejected')->count(),
        ];

        return response()->json([
            'applications' => $applications,
            'students' => $students,
            'partners' => $partners,
            'stats' => $stats,
        ]);
    }

    // Add a new application
    public function storeApplication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'studentId' => 'required|string|exists:users,studentId,role,student',
            'partnerId' => 'nullable|exists:partners,id',
            'applicationDate' => 'required|date_format:Y-m-d',
            'hasResume' => 'boolean',
            'hasLetter' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('studentId', $request->studentId)->where('role', 'student')->firstOrFail();

        $application = Application::create([
            'user_id' => $user->id,
            'partner_id' => $request->partnerId ?: null,
            'application_date' => $request->applicationDate,
            'status' => 'Pending',
            'resume_path' => $request->hasResume ? 'resumes/placeholder.pdf' : null, // Replace with actual file upload logic
            'letter_path' => $request->hasLetter ? 'letters/placeholder.pdf' : null, // Replace with actual file upload logic
        ]);

        return response()->json([
            'message' => 'Application added successfully',
            'application' => [
                'id' => $application->id,
                'studentName' => $user->name,
                'studentId' => $user->studentId,
                'program' => $user->ojtProgram,
                'applicationDate' => $application->application_date->format('Y-m-d'),
                'hasResume' => !empty($application->resume_path),
                'hasLetter' => !empty($application->letter_path),
                'status' => $application->status,
                'partnerId' => $application->partner_id,
            ],
        ], 201);
    }

    // Review/update an application
    public function updateApplication(Request $request, $id)
    {
        $application = Application::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Pending,Approved,Rejected',
            'partnerId' => 'nullable|exists:partners,id',
            'startDate' => 'nullable|date_format:Y-m-d',
            'endDate' => 'nullable|date_format:Y-m-d|after_or_equal:startDate',
            'requiredHours' => 'nullable|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Ensure requiredHours is provided for Approved status
        if ($request->status === 'Approved' && !$request->requiredHours) {
            return response()->json(['errors' => ['requiredHours' => 'Required Hours is required for Approved status']], 422);
        }

        $application->update([
            'status' => $request->status,
            'partner_id' => $request->partnerId ?: null,
            'start_date' => $request->startDate ?: null,
            'end_date' => $request->endDate ?: null,
            'required_hours' => $request->requiredHours ?: 0,
            'remarks' => $request->remarks,
        ]);

        $user = $application->user;

        return response()->json([
            'message' => 'Application reviewed successfully',
            'application' => [
                'id' => $application->id,
                'studentName' => $user->name,
                'studentId' => $user->studentId,
                'program' => $user->ojtProgram,
                'applicationDate' => $application->application_date ? $application->application_date->format('Y-m-d') : null,
                'hasResume' => !empty($application->resume_path),
                'hasLetter' => !empty($application->letter_path),
                'status' => $application->status,
                'partnerId' => $application->partner_id,
                'startDate' => $application->start_date ? $application->start_date->format('Y-m-d') : null,
                'endDate' => $application->end_date ? $application->end_date->format('Y-m-d') : null,
                'requiredHours' => $application->required_hours,
                'remarks' => $application->remarks,
            ],
        ]);
    }

    // Delete an application
    public function deleteApplication($id)
    {
        $application = Application::findOrFail($id);
        $application->delete();

        return response()->json(['message' => 'Application deleted successfully']);
    }

    // Download resume
    public function downloadResume($id)
    {
        $application = Application::findOrFail($id);

        if (!$application->resume_path) {
            return response()->json(['message' => 'Resume not found'], 404);
        }

        $filePath = storage_path('app/' . $application->resume_path);
        if (!file_exists($filePath)) {
            return response()->json(['message' => 'Resume file not found'], 404);
        }

        return response()->download($filePath, 'resume.pdf');
    }

    // Download letter
    public function downloadLetter($id)
    {
        $application = Application::findOrFail($id);

        if (!$application->letter_path) {
            return response()->json(['message' => 'Letter not found'], 404);
        }

        $filePath = storage_path('app/' . $application->letter_path);
        if (!file_exists($filePath)) {
            return response()->json(['message' => 'Letter file not found'], 404);
        }

        return response()->download($filePath, 'letter.pdf');
    }

public function report(Request $request)
{
    $semester = $request->query('semester');

    // Application stats
    $applicationStats = [
        'total' => DB::table('applications')->count(),
        'pending' => DB::table('applications')->where('status', 'Pending')->count(),
        'approved' => DB::table('applications')->where('status', 'Approved')->count(),
        'rejected' => DB::table('applications')->where('status', 'Rejected')->count(),
    ];

    // Partner stats (using programs table)
    $partnerStats = [
        'total' => DB::table('programs')->count(),
        'active' => DB::table('programs')->where('status', 'Active')->count(),
        'inactive' => DB::table('programs')->where('status', 'Inactive')->count(),
    ];

    // Student stats
    $studentStats = [
        'total' => DB::table('users')->where('role', 'student')->count(),
        'onOJT' => DB::table('users')->where('role', 'student')->where('status', 'On OJT')->count(),
        'completed' => DB::table('users')->where('role', 'student')->where('status', 'Completed')->count(),
    ];

    // Monthly applications (last 6 months)
    $monthlyApplications = [];
    for ($i = 5; $i >= 0; $i--) {
        $monthStart = now()->subMonths($i)->startOfMonth();
        $monthEnd = now()->subMonths($i)->endOfMonth();
        $count = DB::table('applications')
            ->whereBetween('application_date', [$monthStart, $monthEnd])
            ->count();
        $monthlyApplications[] = $count;
    }

    // Detailed data
    $applications = DB::table('applications')
        ->select([
            'id',
            'user_id',
            'partner_id',
            'status',
            'resume_path',
            'letter_path',
            'preferred_company',
            'start_date',
            'end_date',
            'completed_hours',
            'required_hours',
            'remarks',
            'application_date',
        ])
        ->get()
        ->map(function ($app) {
            return [
                'id' => $app->id,
                'user_id' => $app->user_id,
                'partner_id' => $app->partner_id,
                'status' => $app->status,
                'resume_path' => $app->resume_path,
                'letter_path' => $app->letter_path,
                'preferred_company' => $app->preferred_company,
                'start_date' => $app->start_date,
                'end_date' => $app->end_date,
                'completed_hours' => $app->completed_hours,
                'required_hours' => $app->required_hours,
                'remarks' => $app->remarks,
                'application_date' => $app->application_date,
            ];
        })
        ->toArray();

    $programs = DB::table('programs')
        ->select(['id', 'programName', 'programDescription', 'status'])
        ->get()
        ->map(function ($program) {
            return [
                'id' => $program->id,
                'programName' => $program->programName,
                'programDescription' => $program->programDescription,
                'status' => $program->status,
            ];
        })
        ->toArray();

    $users = DB::table('users')
        ->select(['id', 'name', 'email', 'studentId', 'studentPhone', 'ojtProgram', 'status', 'role'])
        ->where('role', 'student')
        ->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'studentId' => $user->studentId,
                'studentPhone' => $user->studentPhone,
                'ojtProgram' => $user->ojtProgram,
                'status' => $user->status,
                'role' => $user->role,
            ];
        })
        ->toArray();

    return response()->json([
        'applicationStats' => $applicationStats,
        'partnerStats' => $partnerStats,
        'studentStats' => $studentStats,
        'monthlyApplications' => $monthlyApplications,
        'applications' => $applications,
        'programs' => $programs,
        'users' => $users,
    ]);
}
}