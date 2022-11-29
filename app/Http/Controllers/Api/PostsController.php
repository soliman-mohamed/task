<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;

class PostsController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::withTrashed()->with('tags');
        if ($request->filled('title')) {
            $posts = $posts->where('title', 'like', '%'. $request->title. '%');
        }
        if ($request->filled('user')) {
            $posts = $posts->where('user_id',$request->user);
        }

        $posts = $posts->orderByDesc('created_at')->paginate(10);

        if ($posts->total() > 0){

            return PostResource::collection($posts)->additional(['status' => true]);
        }
        return response()->json([
            'status' => false,
            'msg' => 'no results found'
        ]);
    }

    public function store(Request $request)
    {
        $request['tags'] = $request->tags ? json_decode($request->tags) : [];
        $validator = validator()->make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'pinned' => "boolean",
            'tags' => 'required|array|min:1'
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
            $data = $request->except('image', 'tags');
            $data['user_id'] = auth()->id();
            $data['pinned'] =  $request->pinned ?? 0;

            $image = $request->file('image');
            $filename = $image->getClientOriginalName();
            $f_name_array = explode('.', $filename);
            $f_file_ext = end($f_name_array);
            $file_name = time().'.'.$f_file_ext;
            $image->move('images', $file_name);
            $data['image'] = time().'.'.$f_file_ext;
            $post = Post::create($data);

            $post->tags()->sync($request->tags);
            return response()->json([
                'status' => true,
                'msg' => 'Post created'
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
        $post = Post::withTrashed()->with('tags')->find($id);
        if ($post){
            return PostResource::make($post)->additional(['status' => true]);
        }
        return response()->json([
            'status' => false,
            'msg' => 'no results found'
        ]);
    }

    public function update(Request $request, $id)
    {
        $request['tags'] = $request->tags ? json_decode($request->tags) : [];
        $validator = validator()->make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'pinned' => "boolean",
            'tags' => 'required|array|min:1'
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

        $post = Post::withTrashed()->find($id);
        if ($post){
            $post->title = $request->title;
            $post->content = $request['content'];
            $post->pinned = $request->pinned ?? 0;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = $image->getClientOriginalName();
                $f_name_array = explode('.', $filename);
                $f_file_ext = end($f_name_array);
                $file_name = time().'.'.$f_file_ext;
                $image->move('images', $file_name);
                $post->image = time().'.'.$f_file_ext;
            }

            $post->save();
            $post->tags()->sync($request->tags);
            return response()->json([
                'status' => true,
                'msg' => 'post updated'
            ]);
        }
        return response()->json([
            'status' => false,
            'msg' => 'no results found'
        ]);
    }

    public function destroy($id)
    {
        $post = Post::find($id);
        if ($post){
            $post->delete();
            return response()->json([
                'status' => true,
                'msg' => 'post deleted'
            ]);
        }
        return response()->json([
            'status' => false,
            'msg' => 'no results found'
        ]);
    }

    public function restore($id){
        $post = Post::withTrashed()->find($id);
        if ($post){
            $post->restore();
            return response()->json([
                'status' => true,
                'msg' => 'post restored'
            ]);
        }
        return response()->json([
            'status' => false,
            'msg' => 'no results found'
        ]);
    }
}
