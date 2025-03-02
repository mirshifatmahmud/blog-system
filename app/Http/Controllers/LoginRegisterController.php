<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginRegisterController extends BaseController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors(), 422); // 422 validation error
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
        ]);

        $success['token'] = $user->createToken('Auth_Token')->plainTextToken;
        $success['user'] = $user;

        return $this->sendResponse($success, 'User register successfully', 201); // 201 created user
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors(), 422); // 422 validation error
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            $success['token'] = $user->createToken('Auth_Token')->plainTextToken;
            $success['user'] = $user;

            return $this->sendResponse($success, 'User login successfully', 200); // 200 ok
        } else {
            return $this->sendError('Unauthorized user', ['error' => 'Unauthorized'], 401); // 401 unauthorized user
        }
    }

    public function logout()
    {
        if (auth()->user()) {
            auth()->user()->tokens()->delete();
            return $this->sendResponse([], 'Logged out successfully', 200); // 200 ok
        }
        return $this->sendError('Unauthenticated user', [], 401); // 401 Unauthenticated user
    }
}
