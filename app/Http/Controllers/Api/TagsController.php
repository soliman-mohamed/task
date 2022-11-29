<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagsController extends Controller
{
    public function index(Request $request)
    {
        $tags = Tag::query();
        if ($request->filled('name')) {
            $tags = $tags->where('name', 'like', '%'. $request->name. '%');
        }
        $tags = $tags->orderByDesc('created_at')->paginate(10);

        if ($tags->total() > 0){

            return TagResource::collection($tags)->additional(['status' => true]);
        }
        return response()->json([
            'status' => false,
            'msg' => 'no results found'
        ]);
    }

    public function store(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'name' => 'required|string|max:255|unique:tags,name'
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $error_data = [];
            foreach ($errors->all() as $error) {
                array_push($error_data, $error);
            }
            $data = $error_data;
            $response = [
                'status' => false,
                'error' => $data,
            ];
            return response()->json($response);
        }

        try {
            Tag::create([
                'name' => $request->name,
            ]);

            return response()->json([
                'status' => true,
                'msg' => 'tag created'
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => $e->getMessage()
            ]);
        }
    }


    public function show($id)
    {
        $tag = Tag::find($id);
        if ($tag){
            return TagResource::make($tag)->additional(['status' => true]);
        }
        return response()->json([
            'status' => false,
            'msg' => 'no results found'
        ]);
    }


    public function update(Request $request, $id)
    {
        $validator = validator()->make($request->all(), [
            'name' => 'required|string|max:255|unique:tags,name,'.$id
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $error_data = [];
            foreach ($errors->all() as $error) {
                array_push($error_data, $error);
            }
            $data = $error_data;
            $response = [
                'status' => false,
                'error' => $data,
            ];
            return response()->json($response);
        }

        $tag = Tag::find($id);
        if ($tag){
            $tag->name = $request->name;
            $tag->save();
            return response()->json([
               'status' => true,
               'msg' => 'tag updated'
            ]);
        }
        return response()->json([
            'status' => false,
            'msg' => 'no results found'
        ]);
    }

    public function destroy($id)
    {
        $tag = Tag::find($id);
        if ($tag){
            $tag->delete();
            return response()->json([
                'status' => true,
                'msg' => 'tag deleted'
            ]);
        }
        return response()->json([
            'status' => false,
            'msg' => 'no results found'
        ]);
    }
}
