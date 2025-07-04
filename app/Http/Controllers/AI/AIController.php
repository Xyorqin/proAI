<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\AI\AiService;
use Illuminate\Http\Request;

class AIController extends Controller
{

    public function __construct(protected AiService $service) {}

    public function ask(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
        ]);

        $answer = $this->service->generateText($request->prompt);

        return response()->json([
            'response' => $answer,
        ]);
    }
}
