<?php

namespace App\Service;

class ContentWatchApi
{
    private const API_URL = 'https://content-watch.ru/public/api/';

    public function __construct(private readonly string $key)
    {
    }

    /**
     * @throws \Exception
     */
    public function checkText(string $text): int
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, [
            'key' => $this->key,
            'text' => $text,
            'test' => 0
        ]);
        curl_setopt($curl, CURLOPT_URL, self::API_URL);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            curl_close($curl);
            throw new \Exception('cURL Error: ' . curl_error($curl));
        }

        if (!$response) {
            curl_close($curl);
            throw new \Exception('No response from API or invalid data.');
        }

        $data = json_decode(trim($response), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            curl_close($curl);
            throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
        }

        curl_close($curl);

        if (empty($data)) {
            throw new \Exception('API response is empty or not in the expected format: ' . print_r($response, true));
        }

        if (isset($data['percent'])) {
            return $data['percent'];
        }

        throw new \Exception('API response does not contain a valid "percent" field. Response: ' . print_r($data, true));
    }
}
