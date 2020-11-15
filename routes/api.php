<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Questions
Route::get('/download', 'App\Http\Controllers\QuestionController@download');
Route::get('/questions', 'App\Http\Controllers\QuestionController@getQuestions');
Route::get('/question/{id}', 'App\Http\Controllers\QuestionController@singleQuestion');
Route::patch('/question/{id}', 'App\Http\Controllers\QuestionController@updateQuestion');
Route::delete('/question/{id}', 'App\Http\Controllers\QuestionController@deleteQuestion');
Route::post('/store', 'App\Http\Controllers\QuestionController@store');

// Choice
Route::patch('/choice/{id}', 'App\Http\Controllers\ChoiceController@updateChoice');
Route::delete('/choice/{id}', 'App\Http\Controllers\ChoiceController@deleteChoice');
Route::post('/choice', 'App\Http\Controllers\ChoiceController@store');
