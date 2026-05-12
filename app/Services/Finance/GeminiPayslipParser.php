<?php

namespace App\Services\Finance;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GeminiPayslipParser
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model  = config('services.gemini.model', 'gemini-2.5-flash');
    }

    public function parse(string $storagePath): array
    {
        $fileContent = Storage::disk('local')->get($storagePath);

        if ($fileContent === null) {
            throw new RuntimeException("No se pudo leer el archivo: {$storagePath}");
        }

        $base64 = base64_encode($fileContent);

        $prompt = <<<'PROMPT'
Sos un asistente especializado en análisis de recibos de sueldo argentinos.
Extraé los siguientes datos del recibo y devolvé ÚNICAMENTE un objeto JSON válido, sin markdown, sin explicaciones, sin bloques de código.

El JSON debe tener exactamente esta estructura:
{
  "periodo": "YYYY-MM",
  "empresa": "nombre del empleador",
  "fecha_pago": "YYYY-MM-DD",
  "banco": "nombre del banco",
  "sueldo_basico": 0,
  "total_bruto": 0,
  "total_sin_aporte": 0,
  "total_descuentos": 0,
  "total_neto": 0,
  "conceptos": [
    {"codigo": "01100", "descripcion": "Sueldo Basico", "tipo": "haber_con_aporte", "monto": 0}
  ]
}

Tipos válidos de concepto: "haber_con_aporte", "haber_sin_aporte", "descuento".
Los montos son siempre positivos (incluso descuentos y días no trabajados).
Si un dato no aparece en el recibo, usá null para strings y 0 para números.
PROMPT;

        $response = Http::timeout(60)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}",
            [
                'contents' => [[
                    'parts' => [
                        [
                            'inlineData' => [
                                'mimeType' => 'application/pdf',
                                'data'     => $base64,
                            ],
                        ],
                        ['text' => $prompt],
                    ],
                ]],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature'      => 0,
                ],
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException('Gemini API error: '.$response->status().' — '.$response->body());
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (! $text) {
            throw new RuntimeException('Gemini no devolvió contenido parseable.');
        }

        $data = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('JSON inválido devuelto por Gemini: '.$text);
        }

        return $data;
    }
}
