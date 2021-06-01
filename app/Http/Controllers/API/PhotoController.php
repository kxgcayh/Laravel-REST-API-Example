<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function index()
    {
        $photos = Photo::all();

        return response()->json([
            'success' => true,
            'message' => 'Photo Loaded Successfully',
            'data' => $photos
        ]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'gallery_id' => 'required|integer',
                'image' => 'image|mimes:jpeg,png,jpg,gif,svg'
            ]);
            $auth_id = JWTAuth::authenticate($request->token)->id;
            $user = User::find($auth_id);
            $gallery = Gallery::find($request->gallery_id);
            if ($gallery->user_id != $auth_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You only can update your gallery',
                ], Response::HTTP_UNAUTHORIZED);
            }

            $photos = new Photo();
            $photoName = $this->trim($user->name) . '_photo' . time() . '.' . $request->image->getClientOriginalExtension();
            $photoPath = $request->image->storeAs($this->trim($gallery->name), $photoName, 'user_photos');
            $photos->gallery_id = $request->gallery_id;
            $photos->image = $photoPath;
            $photos->photo_uri = url('storage/user-photos/' . $photoPath);
            $photos->save();

            return response()->json([
                'success' => true,
                'message' => 'Photo Uploaded Successfully',
                'data' => $photos,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something Went Wrong',
                'error' => $e,
            ]);
        }
    }

    public function show($id)
    {
        try {
            $photo = Photo::findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => 'Photo Loaded Succcessfully',
                'data' => $photo
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'message' => 'Photo ID Not Found',
                'data' => $e
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
                'image' => 'image|mimes:jpeg,png,jpg,gif,svg'
            ]);

            $user_id = JWTAuth::authenticate($request->token)->id;
            $user = User::find($user_id);
            $photo = Photo::findOrFail($request->id);
            $gallery = Gallery::find($photo->gallery_id);
            Storage::disk('user_photos')->delete($photo->image);
            $photoName = $this->trim($user->name) . '_photo' . time() . '.' . $request->image->getClientOriginalExtension();
            $photoPath = $request->image->storeAs($this->trim($gallery->name), $photoName, 'user_photos');
            $photo->image = $photoPath;
            $photo->photo_uri = url('storage/user-photos/' . $photoPath);
            $photo->save();
            return response()->json([
                'success' => true,
                'message' => 'Photo Updated Succcessfully',
                'data' => [
                    'photo' => $photo,
                    'gallery' => $gallery,
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'message' => 'Photo ID Not Found',
                'data' => $e
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function showPhotos(Request $request)
    {
        $gallery_id = $request->gallery_id;
        $photos = Photo::get()->where('gallery_id', $gallery_id);
        return response()->json([
            'success' => true,
            'message' => 'Photos in Your Gallery Loaded Successfully',
            'data' => $photos,
        ], Response::HTTP_OK);
    }

    public function trim($string)
    {
        return str_replace(' ', '_', $string);
    }
}
