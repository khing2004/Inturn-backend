<?php

use App\Models\Intern;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Attendance;
use App\Models\Notification;

# Notifies users at 10 PM that they have missing timeout.
Schedule::call(function () {
    if (today()->isWeekday()) {
        // Eager load relationships to avoid errors and improve speed
        $lateInterns = Attendance::with(['intern.user', 'intern.admin'])
            ->whereDate('work_date', today())
            ->whereNull('time_out')
            ->get();
        
            // Log how many records were found
        Log::info("Scheduler check: Found " . $lateInterns->count() . " interns missing timeout.");
        
        foreach ($lateInterns as $attendance) {
            // Ensure the intern exists and has an admin assigned
            if ($attendance->intern && $attendance->intern->admin) {
                
                // Check if notification already exists for today to avoid spamming every minute
                $exists = Notification::where('user_id', $attendance->intern->admin->user_id)
                    ->where('type', 'missing_timeout')
                    ->whereDate('created_at', today())
                    ->exists();

                if (!$exists) {
                    Notification::create([
                        'user_id' => $attendance->intern->admin->user_id, // Send to Admin's User ID
                        'reference_id' => $attendance->attendance_id,
                        'reference_type' => 'attendance',
                        'type' => 'Warning',
                        'message' => "{$attendance->intern->user->name} has not recorded time-out for " . today()->format('F d, Y'),
                        'is_read' => false,
                    ]);
                }
            }
        }
    }
})->dailyAt('22:00');

// Interns who do not time-in for a day are marked absent
Schedule:: call(function() {

    if (today()->subDay()->isWeekday()){
    // Get interns that are present today
        $presentInternsId = Attendance::whereDate('work_date', today()->subDay())
            ->pluck('intern_id')
            ->toArray();
        
        // Find interns in intern table that are active but not in presentInternsId
        $absentInternIds = Intern::where('status', 'Active')
            ->whereNotIn('intern_id', $presentInternsId)
            ->get();

        foreach ($absentInternIds as $intern){
            // Create absent record
            Attendance::firstOrCreate([
                'intern_id' => $intern->intern_id,
                'work_date' => today()->subDay(),
                'time_in' => Null,
                'time_out' => Null,
                'status' => 'Absent',
                'total_hours' => 0
                ]);
        

            //Notify intern 
            Notification::create([
                'user_id' => $intern->user_id,
                'type' => 'Warning',
                'message' => "You have been marked Absent for " . today()->subDay()->format('M d, Y') . '.',
            ]);
        }
    }
    
})->everyMinute();
//->dailyAt('00:00');

// Reset time-in button for Attendance Tracker
Schedule::call(function() {

});