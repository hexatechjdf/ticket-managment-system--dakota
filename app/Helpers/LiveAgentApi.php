<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class LiveAgentApi
{
    protected static $baseUrl;
    protected static $apiKey;

    /**
     * Initialize config from DB
     */
    protected static function init()
    {
        if (!self::$baseUrl || !self::$apiKey) {
            self::$baseUrl = rtrim(get_default_settings('la_api_url'), '/');
            self::$apiKey  = get_default_settings('la_api_key');
        }
    }

    public static function request($method, $endpoint, $data = [])
    {
        self::init();

        $url = self::$baseUrl . '/' . ltrim($endpoint, '/');

        $options = strtoupper($method) === 'GET'
            ? ['query' => $data]
            : ['json' => $data];

        // dd($url, self::$apiKey, $method, $endpoint, $data);
        $response = Http::withHeaders([
            'apiKey' => self::$apiKey,
            'Accept' => 'application/json',
        ])->send($method, $url, $options);

        if ($response->failed()) {
            return [
                'success' => false,
                'status'  => $response->status(),
                'error'   => $response->body(),
            ];
        }

        return [
            'success' => true,
            'status'  => $response->status(),
            'data'    => $response->json(),
        ];
    }
}
