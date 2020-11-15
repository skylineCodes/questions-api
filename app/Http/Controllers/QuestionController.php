<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuestionDetailsResource;
use App\Http\Resources\QuestionResource;
use App\Models\Choice;
use App\Models\Question;
use App\Utils\DatabaseUtil;
use App\Utils\Util;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuestionController extends Controller
{
    protected $choice;

    protected $util;

    protected $question;

    protected $databaseUtil;

    public function __construct(Question $question, Util $util, DatabaseUtil $databaseUtil, Choice $choice)
    {
        $this->choice = $choice;

        $this->util = $util;

        $this->question = $question;

        $this->databaseUtil = $databaseUtil;
    }

    public function download()
    {
        $zip_loaded = extension_loaded('zip') ? true : false;
        
        $pathToFile = public_path('files/question_excel.xlsx');

        // Check if zip extension is loaded or not
        if ($zip_loaded === false) {
            return response()->json(['status' => '400', 'error' => 'Please install/enable PHP Zip archive for import'], 400);
        }

        return response()->download($pathToFile, 'question_excel.xlsx', ['content-type' => ' application/vnd.ms-excel']);
    }

    public function store(Request $request)
    {
        try {
            ini_set('max_execution_time', 0);
            $mimes = array('application/vnd.ms-excel', 'text/xls', 'text/xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            if ($request->hasFile('question_excel') && in_array($_FILES['question_excel']['type'], $mimes)) {
                $imported_data = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('question_excel'));
                
                $is_valid = true;
                $error_msg = '';

                DB::beginTransaction();

                foreach ($imported_data->getWorksheetIterator() as $key => $value) {
                    $valueToArray = $value->toArray();
                    $highestRow = $value->getHighestRow();

                    for ($row = 2; $row <= $highestRow; ++$row) {
                        // Check if any column is missing
                        if (count($valueToArray[0]) < 18) {
                            $is_valid = false;
                            $error_msg = 'Some of the columns are missing. Please, use latest Excel file template.';

                            break;
                        }

                         $data_array = [];

                         // Question
                        $question = trim($value->getCellByColumnAndRow(1, $row)->getValue());                        
                        if (!empty($question) && gettype($question) == 'string') {
                            $data_array['questions'] = $question;
                        } else {
                            $data_array['questions'] = null;
                            $is_valid = false;
                            $error_msg = "Question column at row " . $row . "  is required";
                            break;
                        }

                        // is_general
                        $is_general = trim($value->getCellByColumnAndRow(2, $row)->getValue());
                        if (!empty($is_general) && gettype(boolval($is_general)) == 'boolean') {
                            $data_array['is_general'] = $is_general === "" ? $is_general : boolval($is_general);
                        } else {
                            $data_array['is_general'] = null;
                            $is_valid = false;
                            $error_msg = "is_general column at row " . $row . "  is required";
                            break;
                        }

                        // categories
                        $categories = trim($value->getCellByColumnAndRow(3, $row)->getValue());
                        if (!empty($categories) && gettype($categories) == 'string') {
                            $data_array['category'] = $categories;
                        } else {
                            $data_array['category'] = null;
                            $is_valid = false;
                            $error_msg = "categories column at row " . $row . "  is required";
                            break;
                        }

                        // point
                        $point = trim($value->getCellByColumnAndRow(4, $row)->getValue());
                        if (!empty($point) && gettype(intval($point)) == 'integer') {
                            $data_array['point'] = intval($point);
                        } else {
                            $data_array['point'] = null;
                            $is_valid = false;
                            $error_msg = "point column at row " . $row . "  is required";
                            break;
                        }

                        // icon_url
                        $icon_url = trim($value->getCellByColumnAndRow(5, $row)->getValue());
                        if (!empty($icon_url) && gettype($icon_url) == 'string') {
                            $data_array['icon_url'] = $icon_url;
                        } else {
                            $data_array['icon_url'] = null;
                        }

                        // duration
                        $duration = trim($value->getCellByColumnAndRow(6, $row)->getValue());
                        if (!empty($duration) && gettype(intval($duration)) == 'integer') {
                            $data_array['duration'] = intval($duration);
                        } else {
                            $data_array['duration'] = null;
                            $is_valid = false;
                            $error_msg = "duration column at row " . $row . "  is required";
                            break;
                        }
                        
                        // Choice One
                        $choice_one = trim($value->getCellByColumnAndRow(7, $row)->getValue());
                        if (!empty($choice_one) && gettype($choice_one) == 'string') {
                            $data_array['choice'][$key]['choice'] = $choice_one;
                        } else {
                            $data_array['choice'][$key]['choice'] = null;
                            $is_valid = false;
                            $error_msg = "Choice_1 column at row " . $row . " is required";
                            break;
                        }

                        // is_correct_choice_1
                        $is_correct_choice_1 = trim($value->getCellByColumnAndRow(8, $row)->getValue());
                        if ($is_correct_choice_1 != "" && gettype(boolval($is_correct_choice_1)) == 'boolean') {
                            $data_array['choice'][$key]['is_correct_choice'] = $is_correct_choice_1 === "" ? $is_correct_choice_1 : boolval($is_correct_choice_1);
                        } else {
                            $data_array['choice'][$key]['is_correct_choice'] = null;
                            $is_valid = false;
                            $error_msg = "is_correct_choice_1 column at row " . $row . " is required";
                            break;
                        }

                        // icon_url_1
                        $icon_url_1 = trim($value->getCellByColumnAndRow(9, $row)->getValue());
                        if (!empty($icon_url_1) && gettype($icon_url_1) == 'string') {
                            $data_array['choice'][$key]['icon_url'] = $icon_url_1;
                        } else {
                            $data_array['choice'][$key]['icon_url'] = null;
                        }

                        // Choice Two
                        $choice_two = trim($value->getCellByColumnAndRow(10, $row)->getValue());
                        if (!empty($choice_two) && gettype($choice_two) == 'string') {
                            $data_array['choice'][$key + 1]['choice'] = $choice_two;
                        } else {
                            $data_array['choice'][$key + 1]['choice'] = null;
                            $is_valid = false;
                            $error_msg = "Choice_2 column at row " . $row . " is required";
                            break;
                        }

                        // is_correct_choice_2
                        $is_correct_choice_2 = trim($value->getCellByColumnAndRow(11, $row)->getValue());
                        if ($is_correct_choice_2 != "" && gettype(boolval($is_correct_choice_2)) == 'boolean') {
                            $data_array['choice'][$key + 1]['is_correct_choice'] = $is_correct_choice_2 === "" ? $is_correct_choice_2 : boolval($is_correct_choice_2);
                        } else {
                            $data_array['choice'][$key + 1]['is_correct_choice'] = null;
                            $is_valid = false;
                            $error_msg = "is_correct_choice_2 column at row " . $row . " is required";
                            break;
                        }

                        // icon_url_2
                        $icon_url_2 = trim($value->getCellByColumnAndRow(12, $row)->getValue());
                        if (!empty($icon_url_2) && gettype($icon_url_2) == 'string') {
                            $data_array['choice'][$key + 1]['icon_url'] = $icon_url_2;
                        } else {
                            $data_array['choice'][$key + 1]['icon_url'] = null;
                        }

                        // Choice Three
                        $choice_three = trim($value->getCellByColumnAndRow(13, $row)->getValue());
                        if (!empty($choice_three) && gettype($choice_three) == 'string') {
                            $data_array['choice'][$key + 2]['choice'] = $choice_three;
                        } else {
                            $data_array['choice'][$key + 2]['choice'] = null;
                            $is_valid = false;
                            $error_msg = "Choice_3 column at row " . $row . " is required";
                            break;
                        }

                        // is_correct_choice_3
                        $is_correct_choice_3 = trim($value->getCellByColumnAndRow(14, $row)->getValue());
                        if ($is_correct_choice_3 != "" && gettype(boolval($is_correct_choice_3)) == 'boolean') {
                            $data_array['choice'][$key + 2]['is_correct_choice'] = $is_correct_choice_3 === "" ? $is_correct_choice_3 : boolval($is_correct_choice_3);
                        } else {
                            $data_array['choice'][$key + 2]['is_correct_choice'] = null;
                            $is_valid = false;
                            $error_msg = "is_correct_choice_3 column at row " . $row . " is required";
                            break;
                        }

                        // icon_url_3
                        $icon_url_3 = trim($value->getCellByColumnAndRow(15, $row)->getValue());
                        if (!empty($icon_url_3) && gettype($icon_url_3) == 'string') {
                            $data_array['choice'][$key + 2]['icon_url'] = $icon_url_3;
                        } else {
                            $data_array['choice'][$key + 2]['icon_url'] = null;
                        }

                        // Choice Four
                        $choice_four = trim($value->getCellByColumnAndRow(16, $row)->getValue());
                        if (!empty($choice_four) && gettype($choice_four) == 'string') {
                            $data_array['choice'][$key + 3]['choice'] = $choice_four;
                        } else {
                            $data_array['choice'][$key + 3]['choice'] = null;
                        }

                        // is_correct_choice_4
                        $is_correct_choice_4 = trim($value->getCellByColumnAndRow(17, $row)->getValue());
                        if ($is_correct_choice_1 != "" && gettype(boolval($is_correct_choice_1)) == 'boolean') {
                            $data_array['choice'][$key + 3]['is_correct_choice'] = $is_correct_choice_4 === "" ? $is_correct_choice_4 : boolval($is_correct_choice_4);
                        } else {
                            $data_array['choice'][$key + 3]['is_correct_choice'] = null;
                        }

                        // icon_url_4
                        $icon_url_4 = trim($value->getCellByColumnAndRow(18, $row)->getValue());
                        if (!empty($icon_url_4) && gettype($icon_url_4) == 'string') {
                            $data_array['choice'][$key + 3]['icon_url'] = $icon_url_4;
                        } else {
                            $data_array['choice'][$key + 3]['icon_url'] = null;
                        }

                        $question_array[] = $data_array;
                    }
                }

                if (!$is_valid) {
                    return response()->json(['status' => 400, 'error' => $error_msg], 400);
                }

                if (!empty($question_array)) {
                    foreach ($question_array as $key => $question) {
                        $questionRes = $this->databaseUtil->storeQuestion($question);
                        foreach ($question['choice'] as $key => $choice) {
                            $choice['question_id'] = $questionRes->id;

                            $choiceRes = $this->databaseUtil->storeChoice($choice);
                        }
                    }
                }

                $response = response()->json(['message' => 'File uploaded successfully!']);

                DB::commit();
            } else {
                return response()->json(['status' => 400, 'error' => 'Please attach excel sheet document!'], 400);
            }
        } catch (Exception $e) {
            DB::rollBack();
            Log::emergency("File: " . $e->getFile() . PHP_EOL .
                "Line: " . $e->getLine() . PHP_EOL .
                "Message: " . $e->getMessage());

             $response = response()->json(['error' => $e->getMessage(), 'status' => 400], 400);
        }

        return $response;
    }

    /**
     * Get all questions from storage
     */
    public function getQuestions()
    {
        try {
            $questions = $this->question->withScopes($this->util->scopes())->paginate(10);

            $response = QuestionResource::collection($questions);
        } catch (Exception $e) {
            Log::emergency("File: " . $e->getFile() . PHP_EOL .
                "Line: " . $e->getLine() . PHP_EOL .
                "Message: " . $e->getMessage());

             $response = response()->json(['error' => $e->getMessage(), 'status' => 400], 400);
        }

        return $response;
    }

    /**
     * Get Single Question & its choices
     */
    public function singleQuestion($id)
    {
        try {
            $question = $this->question->where('id', $id)->first();

            if (!$question) {
                return $response = response()->json([
                    'status' => 404,
                    'error' => 'Question not found'
                ], 404);
            }

            $response = new QuestionDetailsResource($question);
        } catch (Exception $e) {
        Log::emergency("File: " . $e->getFile() . PHP_EOL .
            "Line: " . $e->getLine() . PHP_EOL .
            "Message: " . $e->getMessage());

            $response = response()->json(['error' => $e->getMessage(), 'status' => 400], 400);
        }

        return $response;
    }

    /**
     * Update Question By ID
     */
    public function updateQuestion(Request $request, $id)
    {
        try {
            $question = $this->question->where('id', $id)->first();

            if (!$question) {
                return $response = response()->json([
                    'status' => 404,
                    'error' => 'Question not found!'
                ], 404);
            }

            $question->questions = $request->get('questions', $question->questions);
            $question->is_general = $request->get('is_general', intval($question->is_general));
            $question->category = $request->get('category', $question->category);
            $question->point = $request->get('point', $question->point);
            $question->icon_url = $request->get('icon_url', $question->icon_url);
            $question->duration = $request->get('duration', $question->duration);

            $question->save();

            $response = response()->json([
                'status' => 200,
                'data' => new QuestionResource($question)
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
    public function deleteQuestion($id)
    {
        try {
            $question = $this->question->where('id', $id)->first();

            if (!$question) {
                return $response = response()->json([
                    'status' => 404,
                    'error' => 'Question not found!'
                ], 404);
            }

            $question->delete();

            $response = response()->json([
                'status' => 200,
                'message' => 'Question deleted successfully!'
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
