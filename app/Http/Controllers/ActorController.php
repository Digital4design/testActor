<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Actor;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;


class ActorController extends Controller
{
    public function checkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'available' => false,
                'message' => $validator->errors()->first('email')
            ], 422);
        }

        $exists = Actor::where('email', $request->email)->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Email already used' : 'Email available'
        ]);
    }

    public function checkDescription(Request $request)
    {
        $request->validate(['description' => 'required|string']);
        $desc = $request->description;
        $keywords = ['first name', 'last name', 'address', 'height', 'weight', 'gender', 'age'];

        $missing = [];
        foreach ($keywords as $k) {
            if (!preg_match('/\b' . preg_quote($k, '/') . '\b/i', $desc)) {
                $missing[] = ucwords($k);
            }
        }

        return response()->json([
            'valid' => empty($missing),
            'missing' => $missing
        ]);
    }
    public function store(Request $request)
    {

         
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:actors,email',
            'description' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 422);
        }

        $desc = $request->description;

        
        $keywords = ['first name', 'last name', 'address', 'height', 'weight', 'gender', 'age'];
        $missing = [];
        foreach ($keywords as $k) {
            if (!preg_match('/\b' . preg_quote($k, '/') . '\b/i', $desc)) {
                $missing[] = ucwords($k);
            }
        }

        $must = ['First Name', 'Last Name', 'Address'];
        if (count(array_intersect($must, $missing)) > 0) {
            return response()->json([
                'message' => 'Please add first name, last name, and address to your description.',
                'missing' => $missing
            ], 422);
        }

        if (!empty($missing)) {
            return response()->json([
                'message' => 'Please include the following fields in description: ' . implode(', ', $missing),
                'missing' => $missing
            ], 422);
        }

        
        $prompt = "Extract this actor's details in JSON with keys: first_name, last_name, address, height, weight, gender, age. Return only JSON.\n\nDescription:\n" . $desc;

        
        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/responses', [
                    'model' => 'gpt-5', 
                    'input' => $prompt
                ]);

        if ($resp->failed()) {
            return response()->json(['message' => 'Failed to process description'], 500);
        }

        $body = $resp->json();

         
        $content = $body['output'][1]['content'][0]['text'] ?? null;
        $extracted = json_decode($content, true);

        if (!is_array($extracted)) {
            return response()->json(['message' => 'Failed to parse AI response.'], 500);
        }

         
        if (empty($extracted['first_name']) || empty($extracted['last_name']) || empty($extracted['address'])) {
            return response()->json([
                'message' => 'Please add first name, last name, and address to your description.'
            ], 422);
        }
 
        $actor = Actor::create([
            'email' => $request->email,
            'first_name' => $extracted['first_name'],
            'last_name' => $extracted['last_name'],
            'address' => $extracted['address'],
            'height' => $extracted['height'] ?? null,
            'weight' => $extracted['weight'] ?? null,
            'gender' => $extracted['gender'] ?? null,
            'age' => isset($extracted['age']) ? (int) $extracted['age'] : null,
            'description' => $desc,
        ]);
        return response()->json(['message' => 'Saved', 'actor' => $actor], 201);
    }

    public function index()
    {
        $actors = Actor::latest()->get();
        return view('list', compact('actors'));
    }

}
