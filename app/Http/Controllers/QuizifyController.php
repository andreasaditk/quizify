<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class QuizifyController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'materi' => 'required|string|min:50'
        ]);

        $prompt = "Kamu adalah asisten dosen. Berdasarkan materi berikut, buatkan ringkasan singkat (3-5 poin) dan 3 soal pilihan ganda. 
        MATERI: \"{$request->materi}\"
        
        WAJIB balas HANYA dengan format JSON persis seperti ini, tanpa markdown, tanpa teks penjelasan apa pun di luar JSON:
        {
            \"rangkuman\": [\"poin 1\", \"poin 2\"],
            \"kuis\": [
                {
                    \"soal\": \"Pertanyaan?\",
                    \"opsi\": {\"A\": \"...\", \"B\": \"...\", \"C\": \"...\", \"D\": \"...\"},
                    \"jawaban_benar\": \"A\",
                    \"penjelasan\": \"...\"
                }
            ]
        }";

        try {
            // Ditambahkan withoutVerifying() untuk mengantisipasi error SSL cURL di Laragon/XAMPP lokal Windows
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.1-8b-instant', // <-- Ubah bagian ini
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.2,
            ]);

            $result = $response->json();
            
            if (!isset($result['choices'][0]['message']['content'])) {
                // Tangkap pesan error asli dari Groq
                $groqError = $result['error']['message'] ?? json_encode($result);
                return response()->json(['success' => false, 'message' => 'Detail Error Groq: ' . $groqError], 500);
            }

            $rawContent = $result['choices'][0]['message']['content'];
            $cleanJson = preg_replace('/^```json\s*|\s*```$/i', '', trim($rawContent));
            $content = json_decode($cleanJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal melakukan parsing struktur JSON dari AI: ' . json_last_error_msg()
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $content
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Koneksi ke Groq gagal: ' . $e->getMessage()
            ], 500);
        }
    }
}