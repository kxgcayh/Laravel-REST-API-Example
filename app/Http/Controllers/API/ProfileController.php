<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;


class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $auth_check = JWTAuth::parseToken()->authenticate();
        if ($auth_check) {
            $user = JWTAuth::authenticate($request->token);
            return response()->json(['user' => $user]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, token is an invalid'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'avatar' => 'image|mimes:jpeg,png,jpg,gif,svg'
        ]);

        $auth_check = JWTAuth::parseToken()->authenticate();
        if ($auth_check) {
            $id = JWTAuth::authenticate($request->token)->id;
            $user = User::find($id);
            $user->name = $request->name;
            $user->email = $request->email;
            if ($request->file('avatar')) {
                if ($user->avatar != 'user.jpg') {
                    Storage::disk('user_avatars')->delete($user->avatar);
                }
                $avatarName = $user->id . '_avatar' . time() . '.' . request()->avatar->getClientOriginalExtension();
                $avatarPath = $request->avatar->storeAs('avatars', $avatarName, 'user_avatars');
                $user->avatar = $avatarPath;
            }
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Data Updated Successfully',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'avatar_url' => url('storage/user-avatar/' . $avatarPath)
                ]
            ]);
        } else {
            $this->invalidToken();
        }
    }

    public function invalidToken()
    {
        return response()->json([
            'success' => false,
            'message' => 'Sorry, token is an invalid'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
