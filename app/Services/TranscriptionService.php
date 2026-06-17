<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Meeting;
use App\Models\MeetingTranscript;
use Illuminate\Support\Facades\Log;

class TranscriptionService
{
    /**
     * Simulate an AI-powered transcription process.
     * In a real application, this would dispatch a job to process the audio/video file
     * using a service like OpenAI Whisper or AWS Transcribe.
     */
    public function processRecording(Meeting $meeting, $filePath)
    {
        Log::info("Starting AI transcription simulation for {$filePath}");

        $participants = $meeting->employees()->get();
        if ($participants->isEmpty()) {
            return;
        }

        // Generate some realistic dummy transcripts based on the participants
        $dummyTopics = [
            "The API integration is slightly delayed due to authentication issues.",
            "I've pushed the frontend changes to staging. Please review.",
            "We need to optimize the database queries, they are taking too long.",
            "The new dark mode design looks fantastic.",
            "Can we schedule a follow-up meeting with the client next week?",
            "I fixed the bugs reported in the last sprint.",
            "Authentication module is completed.",
            "Let's focus on the user dashboard features next.",
        ];

        $sequence = $meeting->transcripts()->max('sequence') ?? 0;
        $sequence++;

        $numEntries = rand(3, 8);
        $currentTime = rand(0, 120); // start within first 2 minutes

        for ($i = 0; $i < $numEntries; $i++) {
            // 80% chance to identify speaker, 20% "Unknown Speaker"
            $isKnown = rand(1, 100) <= 80;
            
            if ($isKnown) {
                $speaker = $participants->random();
                $speakerName = $speaker->name;
                $employeeId = $speaker->id;
            } else {
                $speakerName = "Unknown Speaker";
                $employeeId = null;
            }

            $text = $dummyTopics[array_rand($dummyTopics)];

            // Format timestamp (e.g., 00:03:12)
            $hours = floor($currentTime / 3600);
            $minutes = floor(($currentTime / 60) % 60);
            $seconds = $currentTime % 60;
            $timestamp = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);

            MeetingTranscript::create([
                'meeting_id' => $meeting->id,
                'employee_id' => $employeeId,
                'speaker_name' => $speakerName,
                'spoken_text' => $text,
                'timestamp' => $timestamp,
                'sequence' => $sequence++,
            ]);

            // increment time by 15-90 seconds for next speaker
            $currentTime += rand(15, 90);
        }

        Log::info("Transcription completed for {$filePath}. Added {$numEntries} entries.");
    }
}
