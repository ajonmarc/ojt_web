<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
class AdminController extends Controller
{

  

    // API: Fetch students and programs for mobile
    public function apiAdminStudent()
    {
        $users = User::where('role', 'student')->get(['id', 'studentId as student_id', 'name', 'email', 'studentPhone as phone', 'ojtProgram as program', 'status']);
        $programs = Program::all(['id', 'program_name']);

        return response()->json([
            'users' => $users,
            'programs' => $programs->map(function ($program) {
                return [
                    'id' => $program->id,
                    'programName' => $program->program_name,
                ];
            }),
        ], 200);
    }


    // API: Create a new student for mobile
    public function apiSaveUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'studentId' => 'required|string|unique:users,studentId',
            'studentPhone' => 'required|string',
            'ojtProgram' => 'required|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $student = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'studentId' => $request->studentId,
            'studentPhone' => $request->studentPhone,
            'ojtProgram' => $request->ojtProgram,
            'status' => $request->status,
            'role' => 'student',
        ]);

        return response()->json([
            'message' => 'Student created successfully',
            'student' => [
                'id' => $student->id,
                'student_id' => $student->studentId,
                'name' => $student->name,
                'email' => $student->email,
                'phone' => $student->studentPhone,
                'program' => $student->ojtProgram,
                'status' => $student->status,
            ],
        ], 201);
    }

  

    // API: Update a student for mobile
    public function apiUpdateUser(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'studentId' => ['required', 'string', Rule::unique('users', 'studentId')->ignore($user->id)],
            'studentPhone' => 'required|string',
            'ojtProgram' => 'required|string',
            'status' => 'required|in:Active,Inactive',
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'studentId' => $request->studentId,
            'studentPhone' => $request->studentPhone,
            'ojtProgram' => $request->ojtProgram,
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Student updated successfully',
            'student' => [
                'id' => $user->id,
                'student_id' => $user->studentId,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->studentPhone,
                'program' => $user->ojtProgram,
                'status' => $user->status,
            ],
        ], 200);
    }



    // API: Delete a student for mobile
    public function apiDestroyUser(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'Student deleted successfully'], 200);
    }

    public function __construct()
    {
        $this->middleware('auth:sanctum')->only([
            'apiAdminStudent',
            'apiSaveUser',
            'apiUpdateUser',
            'apiDestroyUser'
        ]);
        $this->middleware('role:admin')->only([
            'adminStudent',
            'saveUser',
            'updateUser',
            'destroyUser',
            'apiAdminStudent',
            'apiSaveUser',
            'apiUpdateUser',
            'apiDestroyUser'
        ]);
    }





    public function adminDashboard(): Response
    {
        $users = User::all();
        $programs = Program::all();
        
        $stats = [
            'totalStudents' => $users->where('role', 'student')->count(),
            'totalPrograms' => $programs->count(),
            'activeStudents' => $users->where('role', 'student')->where('status', 'Active')->count(),
            'pendingApplications' => $users->where('role', 'student')->where('status', 'Pending')->count(),
        ];

        $recentStudents = User::where('role', 'student')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentStudents' => $recentStudents,
            'programStats' => $programs,
            'users' => $users
        ]);
    }

    public function adminProfile(): Response
    {
        return Inertia::render('Admin/Profile');
    }

    public function adminStudent(): Response
    {
        $users = User::all();
        $programs = Program::all();

        
        return inertia('Admin/Student', [
            'users' => $users,
            'programs' => $programs,
       
        ]);
       
    }

    public function saveUser(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => 'required',
            
            'studentId' => 'required',
            'studentPhone' => 'required',
            'ojtProgram' => 'required',
            'status' => 'required',
        
           
        ]);


        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),

            'studentId' => $request->studentId,
            'studentPhone' => $request->studentPhone,
            'ojtProgram' => $request->ojtProgram,
            'status' => $request->status,
            'role' => 'student',
       
        ]);


        return redirect()->route('admin.student');
    }


    
    public function updateUser(Request $request, User $user): RedirectResponse
    {
        // Validate the request inputs
        $validated = request()->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|unique:users,email,' . $user->id,
            'studentId' => 'required',
            'studentPhone' => 'required',
            'ojtProgram' => 'required',
            'status' => 'required',
        ]);

        // Create update data array
        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'studentId' => $validated['studentId'],
            'studentPhone' => $validated['studentPhone'],
            'ojtProgram' => $validated['ojtProgram'],
            'status' => $validated['status'],
        ];

        // Only update password if it's provided and not empty
        if ($request->has('password') && !empty($request->password)) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Update user with the filtered data
        $user->update($updateData);

        return redirect()->route('admin.student');
    }

    public function destroyUser(User $user): RedirectResponse
    {

        $user->delete();


        return to_route('admin.student');
    }










    public function adminProgram(): Response
    {
        $programs = Program::all();

        
        return inertia('Admin/Program', [
            'programs' => $programs,
       
        ]);
        
    }
    public function saveProgram(Request $request)
    {

        $request->validate([
            'programName' => 'required|string|max:255',
           
            'programDescription' => 'required',
            
        ]);


        Program::create([
            'programName' => $request->programName,
            'programDescription' => $request->programDescription,
          
       
        ]);


        return redirect()->route('admin.program');
    }

    public function updateProgram(Request $request, Program $program): RedirectResponse
    {
        // Validate the request inputs
        request()->validate([
        
        
            
            'programName' => 'required',
            'programDescription' => 'required',
    

        ]);


        $program->update([
            'programName' => request('programName'),
            'programDescription' => request('programDescription'),
         
        ]);


        // Redirect back to the admin room page with a success message

        return redirect()->route('admin.program');
    }

    public function destroyProgram(Program $program): RedirectResponse
    {

        $program->delete();


        return to_route('admin.program');
    }








    public function adminSchedule(): Response
    {
        return Inertia::render('Admin/Schedule');
    }

    public function adminEvaluation(): Response
    {
        return Inertia::render('Admin/Evaluation');
    }

    public function adminPartner(): Response
    {
        return Inertia::render('Admin/Partner');
    }


    public function adminApplication(): Response
    {
        return Inertia::render('Admin/Application');
    }

    public function adminReport(): Response
    {
        return Inertia::render('Admin/Report');
    }



    

}
