<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API для аутентификации, регистрации, входа и сброса пароля"
 * )
 */

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/register",
     *     summary="Регистрация пользователя",
     *     description="Регистрирует нового пользователя с указанными данными.",
     *     operationId="registerUser",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
 *                 @OA\Property(property="name", type="string", description="Имя пользователя", maxLength=255),
 *                 @OA\Property(property="email", type="string", description="Email пользователя", format="email"),
 *                 @OA\Property(property="password", type="string", description="Пароль пользователя", format="password"),
 *                 @OA\Property(property="password_confirmation", type="string", description="Подтверждение пароля", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Пользователь успешно зарегистрирован",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User created successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error")
     *         )
     *     )
     * )
     */

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'User created successfully!'], 201);
    }

    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Вход пользователя",
     *     description="Авторизует пользователя и возвращает токен доступа.",
     *     operationId="loginUser",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
 *                 @OA\Property(property="email", type="string", description="Email пользователя", format="email"),
 *                 @OA\Property(property="password", type="string", description="Пароль пользователя", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный вход",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", description="Токен доступа"),
     *             @OA\Property(property="token_type", type="string", description="Тип токена", example="Bearer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неверные учетные данные",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     */

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     summary="Выход пользователя",
     *     description="Выход из системы и удаление текущего токена доступа.",
     *     operationId="logoutUser",
     *     tags={"Authentication"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Выход выполнен успешно",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out successfully!")
     *         )
     *     )
     * )
     */

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully!']);
    }

    /**
     * @OA\Post(
     *     path="/password/email",
     *     summary="Отправка ссылки для сброса пароля",
     *     description="Отправляет ссылку для сброса пароля на указанный email.",
     *     operationId="sendResetLink",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
 *                 @OA\Property(property="email", type="string", description="Email пользователя", format="email")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ссылка для сброса пароля отправлена",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="We have emailed your password reset link!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Неверный email",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="We couldn't find a user with that email address.")
     *         )
     *     )
     * )
     */

    // Отправка ссылки для сброса пароля
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }


    /**
     * @OA\Post(
     *     path="/password/reset",
     *     summary="Сброс пароля",
     *     description="Сбрасывает пароль пользователя, используя токен и новый пароль.",
     *     operationId="resetPassword",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
 *                 @OA\Property(property="token", type="string", description="Токен для сброса пароля"),
 *                 @OA\Property(property="email", type="string", description="Email пользователя", format="email"),
 *                 @OA\Property(property="password", type="string", description="Новый пароль", format="password"),
 *                 @OA\Property(property="password_confirmation", type="string", description="Подтверждение пароля", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Пароль успешно сброшен",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Your password has been reset!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка сброса пароля",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The provided token is invalid.")
     *         )
     *     )
     * )
     */

    // Сброс пароля
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }
}
