<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Controller;
use App\Models\Intern;
use App\Models\Submission;
use App\Models\Daily_Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class InternController extends Controller
{
    /**
     * get intern's tasks/submissions
     * GET /api/intern/documents
     */
    public function getMyDocuments(Request $request)
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
                    'daily_reports' => $submission->dailyReport ? [
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
     * submit a document/daily report
     * POST /api/intern/tasks/{taskId}/submit
     * POST /api/intern/documents/submit
     */
    public function submitDocument(Request $request)
    {
        if (!$request->user()->isIntern()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'type' => 'required|in:Daily Report,Document,Other', # Has to match with migrations table enum values
            'file' => 'required|file|max:10240', // 10MB max
            
            // Daily report fields (required if type is Daily Report)
            'report_title' => 'required_if:type,Daily Report|string|max:255',
            'accomplishments' => 'required_if:type,Daily Report|string',
            'tasks_completed' => 'required_if:type,Daily Report|string',
            'challenges' => 'required_if:type,Daily Report|string',
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
            'status' => 'Pending', //default status
            'description' => $validated['report_title'] ?? 'Task submission' //default description 
        ]);

        // create daily report if type is Daily Report
        if ($validated['type'] === 'Daily Report') {
            $submission->dailyReport()->create([
                'report_title' => $validated['report_title'],
                'accomplishments' => $validated['accomplishments'],
                'tasks_completed' => $validated['tasks_completed'],
                'challenges' => $validated['challenges'],
            ]);
        }

        $submission->load('dailyReport');

        return response()->json([
            'message' => 'Submission submitted successfully',
            'submission' => [
                'id' => $submission->document_id,
                'type' => $submission->type,
                'file_name' => $submission->file_name,
                'file_path' => $submission->file_path,
                'date_submitted' => $submission->date_submitted->format('Y-m-d H:i:s'),
                'status' => $submission->status,
                'daily_reports' => $submission->dailyReport ? [
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
        $presentDays = $intern->attendance()->Present()->count();
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
                    'present_days' => $presentDays,
                    'late_days' => $lateDays,
                    'absent_days' => $absentDays,
                ],
                'performance' => [
                    'average_rating' => $averageRating ? round($averageRating, 2) : null,
                ],
            ],
        ], 200);
    }

    public function attendanceTimeIn(Request $request)
    {
        if (!$request->user()->isIntern()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $intern = $request->user()->intern;

        // Check if there's already an active attendance record for today
        $existingAttendance = $intern->attendance()
            ->whereDate('work_date', now()->toDateString())
            ->whereNull('time_out')
            ->first();

        if ($existingAttendance) {
            return response()->json(['message' => 'You have already timed in for today. Please time out before timing in again.'], 400);
        }

        // Create new attendance record
        $attendance = $intern->attendance()->create([
            'work_date' => now()->toDateString(),
            'time_in' => now(),
        ]);

        return response()->json([
            'message' => 'Time in recorded successfully',
            'attendance' => [
                'id' => $attendance->id,
                'work_date' => $attendance->date,
                'time_in' => $attendance->time_in->format('Y-m-d H:i:s'),
                'time_out' => null,
                'total_hours' => null,
                'status' => 'Present',
            ],
        ], 200);
    }

    public function attendanceTimeOut(Request $request)
    {
        if (!$request->user()->isIntern()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $intern = $request->user()->intern;

        // Find the active attendance record for today
        $attendance = $intern->attendance()
            ->whereDate('work_date', now()->toDateString())
            ->whereNull('time_out')
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'No active time in record found for today. Please time in first.'], 400);
        }

        // Update attendance record with time out and calculate total hours
        $attendance->update([
            'time_out' => now(),
            'total_hours' => $attendance->time_in->diffInHours(now()),
        ]);

        return response()->json([
            'message' => 'Time out recorded successfully',
            'attendance' => [
                'id' => $attendance->id,
                'work_date' => $attendance->date,
                'time_in' => $attendance->time_in->format('Y-m-d H:i:s'),
                'time_out' => $attendance->time_out->format('Y-m-d H:i:s'),
                'total_hours' => (float) $attendance->total_hours,
                'status' => 'Present',
            ],
        ], 200);
    }

    public function getMyAttendanceOverallSummary(Request $request)
    {
        if (!$request->user()->isIntern()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $intern = $request->user()->intern;

        // Get attendance records for the intern
        $attendanceRecords = $intern->attendance()->get();

        // Calculate summary statistics
        $totalHours = 0;
        $presentDays = 0;
        $lateDays = 0;
        $absentDays = 0;
        $undertimeDays = 0;

        foreach ($attendanceRecords as $record) {
            if ($record->time_out && $record->time_in) {
                // Calculate total hours for each record
                $totalHours += $record->total_hours ?? 0;
                $presentDays++;
            } else {
                // If no time_out, it's an absent day
                $absentDays++;
            }
        }

        return response()->json([
            'summary' => [
                'total_hours' => (float) $totalHours,
                'present_days' => $presentDays,
                'late_days' => $lateDays,
                'absent_days' => $absentDays,
                'undertime_days' => $undertimeDays,
            ],
        ], 200);
    }
}