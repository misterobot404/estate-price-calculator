<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function signin()
    {
        // Проверяем существует ли пользователь с указанным email адресом
        $user = User::whereEmail(request('email'))->first();

        if (!$user) {
            return response()->json([
                'message' => 'Ошибка ввода почты или пароля',
            ], 422);
        }

        // Если пользователь с таким email адресом найден - проверим совпадает
        // ли его пароль с указанным
        if (!Hash::check(request('password'), $user->password)) {
            return response()->json([
                'message' => 'Ошибка ввода почты или пароля',
            ], 422);
        }

        // Внутренний API запрос для получения токенов
        $client = DB::table('oauth_clients')
            ->where('password_client', true)
            ->first();

        // Убедимся, что Password Client существует в БД (т.е. Laravel Passport установлен правильно)
        if (!$client) {
            return response()->json([
                'message' => 'Ошибка сервера'
            ], 500);
        }

        $data = [
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => request('email'),
            'password' => request('password'),
        ];

        $request = Request::create('/oauth/token', 'POST', $data);

        $response = app()->handle($request);

        // Проверяем был ли внутренний запрос успешным
        if ($response->getStatusCode() !== 200) {
            return response()->json([
                'message' => 'Ошибка ввода почты или пароля',
            ], 422);
        }

        // Вытаскиваем данные из ответа
        $data = json_decode($response->getContent());

        // Формируем окончательный ответ в нужном формате
        return response()->json([
            'message' => 'Авторизация успешна',
            'data' => [
                'token' => $data->access_token,
                'user' => $user
            ]
        ]);
    }

    public function signup()
    {
        $validator = Validator::make(request()->all(), ['email' => 'unique:users']);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                "message" => "Данная почта уже используется"
            ], 422);
        }

        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'password' => bcrypt(request('password'))
        ]);

        if (!$user) {
            return response()->json([
                "message" => "Ошибка сервера"
            ], 500);
        }

        return response()->json(["status" => "success", "data" => null], 201);
    }

    public function logout()
    {
        $accessToken = auth()->user()->token();

        $refreshToken = DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update(['revoked' => true]);

        $accessToken->revoke();

        return response()->json(['status' => 200]);
    }
}
