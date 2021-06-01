<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;


class GalleryController extends Controller
{
    public function index()
    {
        $galleries = Gallery::all();

        return response()->json([
            'success' => true,
            'message' => 'Galleries Loaded Successfully',
            'data' => $galleries
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        $gallery = new Gallery();

        $id = JWTAuth::authenticate($request->token)->id;

        $gallery->user_id = $id;
        $gallery->name = $request->name;
        $gallery->save();

        return response()->json([
            'success' => true,
            'message' => 'Gallery Created Succcessfully',
            'data' => $gallery
        ], Response::HTTP_OK);
    }

    public function show($user_id)
    {
        $gallery = Gallery::get()->where('user_id', $user_id);

        return response()->json([
            'success' => true,
            'message' => 'Gallery Loaded Succcessfully',
            'data' => $gallery
        ], Response::HTTP_OK);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'name' => 'required|string'
        ]);

        $id = $request->id;
        $user_id = JWTAuth::authenticate($request->token)->id;
        $gallery = Gallery::find($id);
        if ($gallery->user_id != $user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You only can update your gallery',
            ], Response::HTTP_UNAUTHORIZED);
        }
        $gallery->name = $request->name;
        $gallery->save();

        return response()->json([
            'success' => true,
            'message' => 'Gallery Updated Succcessfully',
            'data' => $gallery
        ], Response::HTTP_OK);
    }

    public function showGalleries(Request $request)
    {
        $user_id = JWTAuth::authenticate($request->token)->id;
        $galleries = Gallery::get()->where('user_id', $user_id);
        return response()->json([
            'success' => true,
            'message' => 'Your Gallery Loaded Successfully',
            'data' => $galleries,
        ], Response::HTTP_OK);
    }
}
