<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChoiceResource;
use App\Models\Choice;
use App\Utils\DatabaseUtil;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChoiceController extends Controller
{
    protected $choice;

    protected $databaseUtil;

    public function __construct(Choice $choice, DatabaseUtil $databaseUtil)
    {
        $this->choice = $choice;

        $this->databaseUtil = $databaseUtil;
    }

    /**
     * Add Choice to storage
     */
    public function store(Request $request)
    {
        try {
            $choice = $this->databaseUtil->storeChoice($request);

            $response = response()->json([
                'status' => 201,
                'data' => new ChoiceResource($choice)
            ], 201);
        } catch (Exception $e) {
        Log::emergency("File: " . $e->getFile() . PHP_EOL .
            "Line: " . $e->getLine() . PHP_EOL .
            "Message: " . $e->getMessage());

            $response = response()->json(['error' => $e->getMessage(), 'status' => 400], 400);
        }

        return $response;
    }

    public function updateChoice(Request $request, $id)
    {
        try {
            $choice = $this->choice->where('id', $id)->first();

            if (!$choice) {
                return $response = response()->json([
                    'status' => 404,
                    'error' => 'Choice not found!'
                ], 404);
            }

            $choice->choice = $request->get('choice', $choice->choice);
            $choice->question_id = $request->get('question_id', intval($choice->question_id));
            $choice->is_correct_choice = $request->get('is_correct_choice', $choice->is_correct_choice);
            $choice->icon_url = $request->get('icon_url', $choice->icon_url);

            $choice->save();

            $response = response()->json([
                'status' => 200,
                'data' => new ChoiceResource($choice)
            ], 200);
        } catch (Exception $e) {
        Log::emergency("File: " . $e->getFile() . PHP_EOL .
            "Line: " . $e->getLine() . PHP_EOL .
            "Message: " . $e->getMessage());

            $response = response()->json(['error' => $e->getMessage(), 'status' => 400], 400);
        }

        return $response;
    }

    /**
     * Delete Question By ID
     */
    public function deleteChoice($id)
    {
        try {
            $choice = $this->choice->where('id', $id)->first();

            if (!$choice) {
                return $response = response()->json([
                    'status' => 404,
                    'error' => 'Choice not found!'
                ], 404);
            }

            $choice->delete();

            $response = response()->json([
                'status' => 200,
                'message' => 'Choice deleted successfully!'
            ], 200);
        } catch (Exception $e) {
        Log::emergency("File: " . $e->getFile() . PHP_EOL .
            "Line: " . $e->getLine() . PHP_EOL .
            "Message: " . $e->getMessage());

            $response = response()->json(['error' => $e->getMessage(), 'status' => 400], 400);
        }

        return $response;
    }
}
