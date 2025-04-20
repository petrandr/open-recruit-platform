<?php
// app/Http/Controllers/ScreeningQuestionController.php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\JobScreeningQuestion;
use Illuminate\Http\Request;

class ScreeningQuestionController extends Controller
{
    /**
     * Search for similar screening questions.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = (string) $request->get('query', '');
        $results = JobScreeningQuestion::query()
            ->where('question_text', 'like', "%{$query}%")
            ->distinct()
            ->limit(10)
            ->pluck('question_text');

        return response()->json($results);
    }
}