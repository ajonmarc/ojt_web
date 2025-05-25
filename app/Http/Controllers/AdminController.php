<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;

class AdminController extends Controller
{


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
