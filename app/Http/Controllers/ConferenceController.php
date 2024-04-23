<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ConferenceController extends Controller
{
    // Метод для создания сессии конференции
    public function createSession(Request $request)
    {
        // Адрес API OpenVidu для создания сессии
        $openviduApiUrl = env('OPENVIDU_SERVER') . '/openvidu/api/sessions';

        // Токен аутентификации для доступа к OpenVidu API
        $openviduApiToken = env('OPENVIDU_SECRET');

        // Создаем HTTP клиент Guzzle
        $client = new Client();

        try {
            // Отправляем POST запрос к API OpenVidu для создания сессии
            $response = $client->post($openviduApiUrl, [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode('OPENVIDUAPP:' . $openviduApiToken),
                    'Content-Type' => 'application/json',
                ],
                // 'json' => [
                //     'customSessionId' => 'your-custom-session-id', ses_Ck1O71uur2 , ses_PdAINd7Wib // Необязательно, можно указать свой ID для сессии
                // ],
            ]);

            // Получаем тело ответа
            $body = $response->getBody()->getContents();

            // Преобразуем JSON ответа в массив
            $responseData = json_decode($body, true);

            // Возвращаем успешный ответ с данными о созданной сессии
            return response()->json($responseData);
        } catch (\Exception $e) {
            // В случае ошибки возвращаем ошибочный ответ
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Метод для подключения к сессии конференции
    public function connectToSession(Request $request, $sessionId)
    {
        // Адрес API OpenVidu для подключения к сессии
        $openviduApiUrl = env('OPENVIDU_SERVER') . '/openvidu/api/sessions/' . $sessionId . '/connection';

        // Токен аутентификации для доступа к OpenVidu API
        $openviduApiToken = env('OPENVIDU_SECRET');

        // Создаем HTTP клиент Guzzle
        $client = new Client();

        try {
            // Отправляем POST запрос к API OpenVidu для подключения к сессии
            $response = $client->post($openviduApiUrl, [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode('OPENVIDUAPP:' . $openviduApiToken),
                    'Content-Type' => 'application/json',
                ],
            ]);

            // Получаем тело ответа
            $body = $response->getBody()->getContents();

            // Преобразуем JSON ответа в массив
            $responseData = json_decode($body, true);

            // исправляем токен для корректного подключения
            $token = $responseData['token'];
            $token = str_replace(':1343', ':443', $token);
            $token = $token . '&secret=' . $openviduApiToken;

            $responseData['token'] = $token;

            // Возвращаем успешный ответ с данными о подключении
            return response()->json($responseData);
        } catch (\Exception $e) {
            // В случае ошибки возвращаем ошибочный ответ
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
