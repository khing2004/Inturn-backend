<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Controller;
use App\Models\Intern;
use App\Models\Submission;
use App\Models\DailyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class InternController extends Controller
{
    /**
     * get intern's tasks/submissions
     * GET /api/intern/tasks
     */
    public function getMyTasks(Request $request)
    {
        if (!$request->user()->isIntern()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $intern = $request->user()->intern;

        $submissions = $intern->submissions()
            ->with('dailyReport')
            ->latest('date_submitted')
            ->get()
            ->map(function ($submission) {
                return [
                    'id' => $submission->document_id,
                    'type' => $submission->type,
                    'file_name' => $submission->file_name,
                    'file_path' => $submission->file_path,
                    'date_submitted' => $submission->date_submitted->format('Y-m-d H:i:s'),
                    'status' => $submission->status,
                    'admin_remarks' => $submission->admin_remarks,
                    'daily_report' => $submission->dailyReport ? [
                        'report_title' => $submission->dailyReport->report_title,
                        'accomplishments' => $submission->dailyReport->accomplishments,
                        'tasks_completed' => $submission->dailyReport->tasks_completed,
                        'challenges' => $submission->dailyReport->challenges,
                    ] : null,
                ];
            });

        return response()->json([
            'tasks' => $submissions,
        ], 200);
    }

    /**
     * submit a task/daily report
     * POST /api/intern/tasks/{taskId}/submit
     * POST /api/intern/tasks/submit
     */
    public function submitTask(Request $request, $taskId = null)
    {
        if (!$request->user()->isIntern()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'type' => 'required|in:daily_report,document',
            'file' => 'required|file|max:10240', // 10MB max
            
            // Daily report fields (required if type is daily_report)
            'report_title' => 'required_if:type,daily_report|string|max:255',
            'accomplishments' => 'required_if:type,daily_report|string',
            'tasks_completed' => 'required_if:type,daily_report|string',
            'challenges' => 'required_if:type,daily_report|string',
        ]);

        $intern = $request->user()->intern;

        // store file
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('submissions', $fileName, 'public');

        // sreate submission
        $submission = Submission::create([
            'intern_id' => $intern->intern_id,
            'type' => $validated['type'],
            'file_name' => $fileName,
            'file_path' => $filePath,
            'date_submitted' => now(),
            'status' => 'pending',
        ]);

        // create daily report if type is daily_report
        if ($validated['type'] === 'daily_report') {
            $submission->dailyReport()->create([
                'report_title' => $validated['report_title'],
                'accomplishments' => $validated['accomplishments'],
                'tasks_completed' => $validated['tasks_completed'],
                'challenges' => $validated['challenges'],
            ]);
        }

        $submission->load('dailyReport');

        return response()->json([
            'message' => 'Task submitted successfully',
            'submission' => [
                'id' => $submission->document_id,
                'type' => $submission->type,
                'file_name' => $submission->file_name,
                'file_path' => $submission->file_path,
                'date_submitted' => $submission->date_submitted->format('Y-m-d H:i:s'),
                'status' => $submission->status,
                'admin_remarks' => $submission->admin_remarks,
                'daily_report' => $submission->dailyReport ? [
                    'report_title' => $submission->dailyReport->report_title,
                    'accomplishments' => $submission->dailyReport->accomplishments,
                    'tasks_completed' => $submission->dailyReport->tasks_completed,
                    'challenges' => $submission->dailyReport->challenges,
                ] : null,
            ],
        ], 201);
    }

    /**
     * get intern's profile
     * GET /api/intern/profile
     */
    public function getMyProfile(Request $request)
    {
        if (!$request->user()->isIntern()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $intern = $request->user()->intern;
        $intern->load(['user', 'admin.user']);

        // get statistics
        $totalSubmissions = $intern->submissions()->count();
        $pendingSubmissions = $intern->submissions()->pending()->count();
        $verifiedSubmissions = $intern->submissions()->verified()->count();
        $rejectedSubmissions = $intern->submissions()->rejected()->count();
        
        $totalHours = $intern->attendance()->sum('total_hours');
        $lateDays = $intern->attendance()->late()->count();
        $absentDays = $intern->attendance()->absent()->count();
        
        $averageRating = $intern->evaluations()->avg(
            \DB::raw('(technical_skills_rating + communication_rating) / 2')
        );

        return response()->json([
            'profile' => [
                'id' => $intern->intern_id,
                'name' => $intern->user->name,
                'email' => $intern->user->email,
                'gender' => $intern->user->gender,
                'university' => $intern->university,
                'department' => $intern->department,
                'supervisor' => $intern->supervisor,
                'start_date' => $intern->start_date->format('Y-m-d'),
                'phone_number' => $intern->phone_number,
                'emergency_contact' => $intern->emergency_contact,
                'emergency_contact_name' => $intern->emergency_contact_name,
                'address' => $intern->address,
                'status' => $intern->status,
                'admin_name' => $intern->admin->user->name,
            ],
            'statistics' => [
                'submissions' => [
                    'total' => $totalSubmissions,
                    'pending' => $pendingSubmissions,
                    'verified' => $verifiedSubmissions,
                    'rejected' => $rejectedSubmissions,
                ],
                'attendance' => [
                    'total_hours' => (float) $totalHours,
                    'late_days' => $lateDays,
                    'absent_days' => $absentDays,
                ],
                'performance' => [
                    'average_rating' => $averageRating ? round($averageRating, 2) : null,
                ],
            ],
        ], 200);
    }
}