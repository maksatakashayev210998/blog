<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Admin'); // Только администратор может управлять пользователями
    }

    // Список пользователей
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    // Создание нового пользователя
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return response()->json($user, 201);
    }


    // Получение информации о пользователе
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }


    // Обновление пользователя
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
        ]);

        $user = User::findOrFail($id);
        $user->update($request->only(['name', 'email', 'password']));

        return response()->json($user);
    }

    // Удаление пользователя
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    // Назначение роли пользователю
    public function assignRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::findOrFail($id);


        $user->assignRole($request->role);

        return response()->json(['message' => 'Role assigned successfully']);
    }

    // Назначение разрешения пользователю
    public function assignPermission(Request $request, $id)
    {
        $request->validate([
            'permission' => 'required|string|exists:permissions,name',
        ]);

        $user = User::findOrFail($id);
        $user->givePermissionTo($request->permission);

        return response()->json(['message' => 'Permission assigned successfully']);
    }
}
