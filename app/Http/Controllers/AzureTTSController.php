<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AzureTTSController extends Controller
{
    public function generate(Request $request)
    {
        $ssml = $request->input('input.ssml', "<speak version='1.0' xml:lang='th-TH'><voice xml:lang='th-TH' xml:gender='Female' name='th-TH-NiwatNeural'>สวัสดีครับท่าน <lang xml:lang='en-US'>this is Ava speaking</lang> พร้อมปฏิบัติงานแล้ว</voice></speak>");

        $subscriptionKey = env('AZURE_SPEECH_KEY');
        $region = env('AZURE_SPEECH_REGION');

        try {
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $subscriptionKey,
                'Content-Type' => 'application/ssml+xml',
                'X-Microsoft-OutputFormat' => 'audio-16khz-32kbitrate-mono-mp3'
            ])->post("https://{$region}.tts.speech.microsoft.com/cognitiveservices/v1", $ssml);

            if ($response->failed()) {
                return response()->json(['error' => 'ไม่สามารถรับเสียงจาก Azure ได้'], 500);
            }

            return response($response->body(), 200)->header('Content-Type', 'audio/mpeg');

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
