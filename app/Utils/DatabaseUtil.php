<?php

namespace App\Utils;

use App\Models\Choice;
use App\Models\Question;
use Exception;
use Illuminate\Support\Facades\Log;

class DatabaseUtil
{
    protected $question;

    protected $choice;

    public function __construct(Question $question, Choice $choice)
    {
        $this->question = $question;

        $this->choice = $choice;
    }

    public function storeQuestion($data)
    {
        try {
         // Create a question
            $response = $this->question->create([
                'questions' => $data['questions'],
                'is_general' => $data['is_general'],
                'category' => $data['category'],
                'point' => $data['point'],
                'icon_url' => $data['icon_url'],
                'duration' => $data['duration']
            ]);
        } catch (Exception $e) {
            Log::emergency("File: " . $e->getFile() . PHP_EOL .
                "Line: " . $e->getLine() . PHP_EOL .
                "Message: " . $e->getMessage());

            $response = response()->json(['error' => $e->getMessage(), 'status' => 400], 400);
        }

        return $response;
    }

    public function storeChoice($data)
    {
        try {
         // Create a choice
            $response = $this->choice->create([
               'choice' => $data['choice'],
               'question_id' => $data['question_id'],
               'is_correct_choice' => $data['is_correct_choice'],
               'icon_url' => $data['icon_url']
            ]);
        } catch (Exception $e) {
            Log::emergency("File: " . $e->getFile() . PHP_EOL .
                "Line: " . $e->getLine() . PHP_EOL .
                "Message: " . $e->getMessage());

            $response = response()->json(['error' => $e->getMessage(), 'status' => 400], 400);
        }

        return $response;
    }
}