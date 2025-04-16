<?php

// app/Http/Controllers/WebhookController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Log the entire request payload for debugging
        Log::info('Request payload: ' . json_encode($request->all()));

        // Extract the queryResult from the request
        $queryResult = $request->input('queryResult', null);

        if ($queryResult) {
            $intent = $queryResult['intent']['displayName'] ?? 'Default Intent';
            $parameters = $queryResult['parameters'] ?? [];

            // Log extracted values
            Log::info('Extracted queryResult: ' . json_encode($queryResult));
            Log::info('Extracted intent: ' . json_encode($intent));
            Log::info('Extracted parameters: ' . json_encode($parameters));

            // Generate a response based on the intent
            $responseText = $this->generateResponse($intent, $parameters);

            $response = [
                'fulfillmentText' => $responseText,
            ];

            return response()->json($response);
        } else {
            // Log the error
            Log::error('Query result is null');

            return response()->json([
                'fulfillmentText' => 'There was an error processing your request.'
            ]);
        }
    }

    private function generateResponse($intent, $parameters)
    {
        switch ($intent) {
            case 'Welcome Intent':
                return 'Hi there! Welcome to our project management tool. How can I assist you today?';
            case 'Some Other Intent':
                return 'This is a response to some other intent.';
            // Add more cases for different intents
            default:
                return 'I am not sure how to respond to that.';
        }
    }
}
