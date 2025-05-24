<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Report', [
            'applicationStats' => $this->getApplicationStats(),
            'partnerStats' => $this->getPartnerStats(),
            'studentStats' => $this->getStudentStats(),
            'monthlyApplications' => $this->getMonthlyApplications(),
            'topPartners' => $this->getTopPartners(),
            'completionRates' => $this->getCompletionRates(),
        ]);
    }

    public function generateReport(Request $request)
    {
        try {
            $reportType = $request->input('reportType');
            $dateRange = $request->input('dateRange');
            $status = $request->input('status');

            $data = [];
            switch ($reportType) {
                case 'applications':
                    $data = $this->getApplicationReport($dateRange, $status);
                    break;
                case 'partners':
                    $data = $this->getPartnerReport($dateRange);
                    break;
                case 'students':
                    $data = $this->getStudentReport($dateRange);
                    break;
                default:
                    return back()->with('error', 'Invalid report type');
            }

            return Inertia::render('Admin/Report', [
                'applicationStats' => $this->getApplicationStats(),
                'partnerStats' => $this->getPartnerStats(),
                'studentStats' => $this->getStudentStats(),
                'monthlyApplications' => $this->getMonthlyApplications(),
                'topPartners' => $this->getTopPartners(),
                'completionRates' => $this->getCompletionRates(),
                'reportData' => $data,
                'flash' => [
                    'success' => 'Report generated successfully'
                ]
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate report: ' . $e->getMessage());
        }
    }

    private function getApplicationStats()
    {
        return [
            'total' => Application::count(),
            'pending' => Application::where('status', 'Pending')->count(),
            'approved' => Application::where('status', 'Approved')->count(),
            'rejected' => Application::where('status', 'Rejected')->count(),
        ];
    }

    private function getPartnerStats()
    {
        return [
            'total' => Partner::count(),
            'active' => Partner::where('status', 'Active')->count(),
            'inactive' => Partner::where('status', 'Inactive')->count(),
        ];
    }

    private function getStudentStats()
    {
        return [
            'total' => User::where('role', 'student')->count(),
            'onOJT' => Application::where('status', 'Approved')
                ->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->where('completed_hours', '<', DB::raw('required_hours'))
                ->count(),
            'completed' => Application::where('status', 'Approved')
                ->where('completed_hours', '>=', DB::raw('required_hours'))
                ->count(),
        ];
    }

    private function getMonthlyApplications()
    {
        return Application::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => date('F', mktime(0, 0, 0, $item->month, 1)),
                    'count' => $item->count,
                ];
            });
    }

    private function getTopPartners()
    {
        return Partner::withCount(['applications' => function ($query) {
            $query->where('status', 'Approved');
        }])
        ->orderByDesc('applications_count')
        ->take(5)
        ->get();
    }

    private function getCompletionRates()
    {
        return Application::where('status', 'Approved')
            ->select(
                DB::raw('CASE 
                    WHEN (completed_hours / required_hours * 100) >= 100 THEN "100%"
                    WHEN (completed_hours / required_hours * 100) >= 75 THEN "75-99%"
                    WHEN (completed_hours / required_hours * 100) >= 50 THEN "50-74%"
                    WHEN (completed_hours / required_hours * 100) >= 25 THEN "25-49%"
                    ELSE "0-24%"
                END as completion_range'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('completion_range')
            ->get();
    }

    private function getApplicationReport($dateRange, $status)
    {
        $query = Application::with(['user', 'partner'])
            ->select([
                'applications.id',
                'applications.status',
                'applications.created_at',
                'applications.start_date',
                'applications.completed_hours',
                'applications.required_hours',
                'users.name as student_name',
                'partners.partnerName as company_name'
            ])
            ->join('users', 'applications.user_id', '=', 'users.id')
            ->join('partners', 'applications.partner_id', '=', 'partners.id');

        if ($dateRange !== 'all') {
            $query->where('applications.created_at', '>=', match ($dateRange) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'quarter' => now()->subQuarter(),
                'year' => now()->subYear(),
            });
        }

        if ($status) {
            $query->where('applications.status', $status);
        }

        return $query->get()->map(function ($application) {
            return [
                'ID' => $application->id,
                'Student Name' => $application->student_name,
                'Company' => $application->company_name,
                'Status' => $application->status,
                'Start Date' => $application->start_date ? $application->start_date->format('Y-m-d') : '-',
                'Progress' => $application->required_hours > 0 
                    ? round(($application->completed_hours / $application->required_hours) * 100, 1) . '%'
                    : '0%',
                'Created At' => $application->created_at->format('Y-m-d'),
            ];
        });
    }

    private function getPartnerReport($dateRange)
    {
        $query = Partner::withCount(['applications as total_applications', 'applications as approved_applications' => function ($query) {
            $query->where('status', 'Approved');
        }])
        ->select([
            'id',
            'partnerName',
            'partnerAddress',
            'partnerPhone',
            'partnerEmail',
            'status',
            'created_at'
        ]);

        if ($dateRange !== 'all') {
            $query->where('created_at', '>=', match ($dateRange) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'quarter' => now()->subQuarter(),
                'year' => now()->subYear(),
            });
        }

        return $query->get()->map(function ($partner) {
            return [
                'ID' => $partner->id,
                'Company Name' => $partner->partnerName,
                'Address' => $partner->partnerAddress,
                'Phone' => $partner->partnerPhone,
                'Email' => $partner->partnerEmail,
                'Status' => $partner->status,
                'Total Applications' => $partner->total_applications,
                'Approved Applications' => $partner->approved_applications,
                'Created At' => $partner->created_at->format('Y-m-d'),
            ];
        });
    }

    private function getStudentReport($dateRange)
    {
        $query = User::where('role', 'student')
            ->withCount(['applications as total_applications', 'applications as approved_applications' => function ($query) {
                $query->where('status', 'Approved');
            }])
            ->select([
                'id',
                'name',
                'email',
                'created_at'
            ]);

        if ($dateRange !== 'all') {
            $query->where('created_at', '>=', match ($dateRange) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'quarter' => now()->subQuarter(),
                'year' => now()->subYear(),
            });
        }

        return $query->get()->map(function ($student) {
            return [
                'ID' => $student->id,
                'Name' => $student->name,
                'Email' => $student->email,
                'Total Applications' => $student->total_applications,
                'Approved Applications' => $student->approved_applications,
                'Created At' => $student->created_at->format('Y-m-d'),
            ];
        });
    }
}
