<?php

namespace App\Http\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class OpenViduService
{
    protected $client;
    protected $apiUrl;
    protected $apiToken;

    public function __construct()
    {
        $this->apiUrl = env('OPENVIDU_SERVER') . '/openvidu/api/sessions';
        $this->apiToken = env('OPENVIDU_SECRET');
        $this->client = new Client([
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode('OPENVIDUAPP:' . $this->apiToken),
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    // Метод для создания сессии конференции
    public function createSession($session)
    {
        try {
            $response = $this->client->post($this->apiUrl, [
                'json' => [
                    'customSessionId' => $session
                ],
            ]);

            return $this->handleResponse($response);
        } catch (RequestException $e) {
            return $this->handleException($e, 'Error creating session');
        }
    }

    // Метод для подключения к сессии конференции
    public function connectToSession($sessionId, $connectionData = [])
    {
        $url = "{$this->apiUrl}/{$sessionId}/connection";

        try {
            $response = $this->client->post($url, [
                'json' => $connectionData,
            ]);

            return $this->handleResponse($response);
        } catch (RequestException $e) {
            return $this->handleException($e, 'Error connecting to session');
        }
    }

    // Метод получения сессии по sessionId
    public function getSession($sessionId)
    {
        $url = "{$this->apiUrl}/{$sessionId}";

        try {
            $response = $this->client->get($url);

            return $this->handleResponse($response);
        } catch (RequestException $e) {
            return $this->handleException($e, 'Error fetching session');
        }
    }

    // Метод получения подключений сессии по sessionId
    public function getSessionConnections($sessionId)
    {
        $url = "{$this->apiUrl}/{$sessionId}/connection";

        try {
            $response = $this->client->get($url);

            return $this->handleResponse($response);
        } catch (RequestException $e) {
            return $this->handleException($e, 'Error fetching session connections');
        }
    }

    // Метод для обработки ответа
    protected function handleResponse($response)
    {
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }

    // Метод для обработки исключений
    protected function handleException(RequestException $e, $message)
    {
        $response = $e->getResponse();
        $statusCode = $response ? $response->getStatusCode() : 'N/A';
        $errorBody = $response ? $response->getBody()->getContents() : 'N/A';

        throw new \Exception("{$message}: [HTTP {$statusCode}] {$errorBody}");
    }
}
