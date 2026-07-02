<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PagoFacilService
{
    protected $baseUrl;
    protected $apiUrl;
    protected $tcTokenService;
    protected $tcTokenSecret;

    public function __construct()
    {
        $this->baseUrl = config('services.pagofacil.base_url', 'https://masterqr.pagofacil.com.bo');
        $this->apiUrl = config('services.pagofacil.api_url', 'https://masterqr.pagofacil.com.bo/api/services/v2');
        $this->tcTokenService = config('services.pagofacil.tc_token_service');
        $this->tcTokenSecret = config('services.pagofacil.tc_token_secret');
    }

    /**
     * Autenticar y obtener Bearer token
     */
    protected function obtenerBearerToken(): string
    {
        // Verificar si hay un token en caché (válido por 1 hora)
        $tokenCacheKey = 'pagofacil_bearer_token';
        $cachedToken = Cache::get($tokenCacheKey);

        if ($cachedToken) {
            Log::info('🔑 [PagoFácil] Usando token en caché');
            return $cachedToken;
        }

        if (!$this->tcTokenService || !$this->tcTokenSecret) {
            throw new \Exception('Las credenciales de PagoFácil no están configuradas. Verifica PAGOFACIL_TC_TOKEN_SERVICE y PAGOFACIL_TC_TOKEN_SECRET en .env');
        }

        try {
            Log::info('🔐 [PagoFácil] Autenticando para obtener Bearer token');

            // Endpoint correcto de autenticación
            $endpoint = "{$this->apiUrl}/login";

            Log::info("🔍 [PagoFácil] Intentando autenticación en: {$endpoint}");

            // Las credenciales van en el Header, no en el body
            $response = Http::timeout(10)
                ->withHeaders([
                    'tcTokenService' => $this->tcTokenService,
                    'tcTokenSecret' => $this->tcTokenSecret,
                ])
                ->post($endpoint);

            if ($response->successful()) {
                $data = $response->json();
                // El token está en values.accessToken según la respuesta de PagoFácil
                $token = $data['values']['accessToken'] ?? $data['accessToken'] ?? $data['token'] ?? $data['access_token'] ?? $data['data']['token'] ?? null;

                if ($token) {
                    // Guardar en caché por 1 hora
                    Cache::put($tokenCacheKey, $token, now()->addHour());
                    Log::info('✅ [PagoFácil] Token obtenido exitosamente');
                    return $token;
                }

                throw new \Exception('No se encontró el token en la respuesta: ' . json_encode($data));
            }

            throw new \Exception("Error al autenticar. Status {$response->status()}: {$response->body()}");
        } catch (\Exception $e) {
            Log::error('❌ [PagoFácil] Error al autenticar', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Obtener headers con autenticación
     */
    protected function obtenerHeaders(): array
    {
        $token = $this->obtenerBearerToken();

        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];
    }

    /**
     * Consultar estado de transacción con timeout corto (para polling del frontend sin 500).
     * Devuelve el JSON decodificado (array) o lanza excepción si falla.
     */
    public function consultarTransaccionRapida(string $transactionId, int $timeoutSeconds = 8, int $connectTimeoutSeconds = 5): array
    {
        $headers = $this->obtenerHeaders();

        $body = [
            'pagofacilTransactionId' => (int) $transactionId,
        ];

        Log::info("📤 [PagoFácil] Enviando consulta RÁPIDA", [
            'endpoint' => "{$this->apiUrl}/query-transaction",
            'timeout' => $timeoutSeconds,
            'connect_timeout' => $connectTimeoutSeconds,
            'body' => $body,
        ]);

        $response = Http::withHeaders($headers)
            ->timeout($timeoutSeconds)
            ->connectTimeout($connectTimeoutSeconds)
            ->post("{$this->apiUrl}/query-transaction", $body);

        $result = $response->json();

        Log::info("📥 [PagoFácil] Respuesta RÁPIDA recibida", [
            'status' => $response->status(),
            'body' => $result,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Respuesta no exitosa ({$response->status()}): " . $response->body());
        }

        if (isset($result['error']) && $result['error'] != 0) {
            throw new \Exception($result['message'] ?? 'Error en la transacción');
        }

        return $result;
    }

    /**
     * Generar QR para pago
     */
    public function generateQr(array $datos): array
    {
        try {
            Log::info('🌐 [PagoFácil] Generando QR', ['datos' => $datos]);

            $headers = $this->obtenerHeaders();

            $response = Http::withHeaders($headers)
                ->post("{$this->apiUrl}/generate-qr", $datos);

            Log::info('📥 [PagoFácil] Respuesta recibida', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('✅ [PagoFácil] Respuesta exitosa de generate-qr', ['data' => $data]);

                // La respuesta puede estar en values según la estructura de PagoFácil
                $responseData = $data['values'] ?? $data;

                $result = [
                    'transactionId' => $responseData['transactionId'] ?? $responseData['transaction_id'] ?? null,
                    'qrBase64' => $responseData['qrBase64'] ?? $responseData['qr_base64'] ?? null,
                    'expirationDate' => $responseData['expirationDate'] ?? $responseData['expiration_date'] ?? null,
                    // ✅ Guardar también el companyTransactionId que devuelve la API (es el que llega como PedidoID en callbacks)
                    'companyTransactionId' => $responseData['companyTransactionId'] ?? $responseData['company_transaction_id'] ?? null,
                    'paymentMethodTransactionId' => $responseData['paymentMethodTransactionId'] ?? $responseData['payment_method_transaction_id'] ?? null,
                    'status' => $responseData['status'] ?? null,
                ];

                Log::info('📊 [PagoFácil] Datos extraídos del QR', ['result' => $result]);

                return $result;
            }

            throw new \Exception('Error al generar QR: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('❌ [PagoFácil] Error al generar QR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Consultar estado de transacción
     */
    /**
     * Consultar estado de transacción
     * 
     * @param string $transactionId ID de transacción de PagoFácil (pagofacilTransactionId)
     * @param string|null $companyTransactionId ID de transacción de la empresa (companyTransactionId) (opcional)
     */
    public function consultarTransaccion(string $transactionId, ?string $companyTransactionId = null): array
    {
        try {
            Log::info('🔍 [PagoFácil] Consultando transacción', [
                'pagofacil_transaction_id' => $transactionId,
                'company_transaction_id' => $companyTransactionId,
            ]);

            $headers = $this->obtenerHeaders();

            // Body: se puede consultar por pagofacilTransactionId (int) o por companyTransactionId (string).
            // Priorizamos pagofacilTransactionId si viene.
            $body = [];
            if (!empty($transactionId)) {
                // ✅ CRÍTICO: Convertir a entero según documentación de PagoFácil
                $body['pagofacilTransactionId'] = (int) $transactionId;
            } elseif (!empty($companyTransactionId)) {
                $body['companyTransactionId'] = $companyTransactionId;
            } else {
                throw new \Exception('Se requiere pagofacilTransactionId o companyTransactionId para consultar la transacción');
            }

            Log::info("📤 [PagoFácil] Enviando consulta", [
                'endpoint' => "{$this->apiUrl}/query-transaction",
                'body' => $body
            ]);

            // ✅ AUMENTADO: Timeout de 90s y connect_timeout de 10s
            $response = Http::withHeaders($headers)
                ->timeout(90)
                ->connectTimeout(10)
                ->post("{$this->apiUrl}/query-transaction", $body);

            $responseContent = $response->body();
            $result = json_decode($responseContent, true);

            Log::info("📥 [PagoFácil] Respuesta recibida", [
                'status' => $response->status(),
                'body' => $result
            ]);

            // ✅ Validar si la respuesta es válida (JSON mal formado)
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Respuesta inválida del proveedor (JSON mal formado)');
            }

            // ✅ Validar errores lógicos de la API
            if (isset($result['error']) && $result['error'] != 0) {
                throw new \Exception($result['message'] ?? 'Error en la transacción');
            }

            if (!isset($result['values'])) {
                throw new \Exception('Datos no encontrados en la respuesta');
            }

            Log::info('✅ [PagoFácil] Consulta exitosa', ['data' => $result]);
            return $result;

        } catch (\Exception $e) {
            Log::error('❌ [PagoFácil] Error al consultar transacción', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Procesar pago con tarjeta
     */
    public function procesarTarjeta(array $datos): array
    {
        try {
            Log::info('💳 [PagoFácil] Procesando pago con tarjeta', ['datos' => array_merge($datos, ['cardNumber' => '****', 'cvv' => '***'])]);

            $headers = $this->obtenerHeaders();

            $response = Http::withHeaders($headers)
                ->post("{$this->apiUrl}/card/process", $datos);

            Log::info('📥 [PagoFácil] Respuesta tarjeta recibida', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('Error al procesar tarjeta: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('❌ [PagoFácil] Error al procesar tarjeta', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
