<?php
declare(strict_types=1);
namespace App\Http\Requests\Question;

use Illuminate\Http\Request;

trait ValidateQuestion
{
    public function validateQuestionRequest(Request $request)
    {
       $request->validate([
            'question' => 'required|string|min:3|max:256',
            'answer' => 'required|string|min:5|max:256',
        ],);
    }
}
