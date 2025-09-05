<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ViaCepController extends Controller
{
    public function search(string $cep)
    {
        $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

        if ($response->failed() || isset($response->json()['erro'])) {
            return response()->json(['message' => 'CEP not found.'], 404);
        }

        return response()->json($response->json());
    }
}
