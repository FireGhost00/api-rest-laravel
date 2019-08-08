<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;
class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index(){
        $posts = Post::all()->load('category');

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'posts' => $posts
        ],200);
    }
    public function show($id){
        $post = Post::find($id)->load('category');
        if(is_object($post)){
            $data = array(
                'code' => 200,
                'status' => 'success',
                'category' => $post
            );
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'la entrada no existe'
            );
        }
        return  response()->json($data, $data['code']);
    }
    public function store(Request $request)
    {
        // Recoger los datos por post
        $json = $request->input('json',null);
        $params = json_decode($json);
       
        $params_array = json_decode($json, true);
        if(!empty($params_array)){
            // conseguir usuario identificado
            $jwtAuth = new JwtAuth();
            $token = $request->header('Authorization',null);
            $user = $jwtAuth->checkToken($token,true);
        // Validar los datos
        $validate = \Validator::make($params_array,[
            'title' => 'required',
            'content' => 'required',
            'category_id' => 'required',
        ]);
        if($validate->fails()){
            $data=[
                'code' => 400,
                'status'=>'error',
                'message' => 'No se ha guardado la Entrada.',
                'image' => 'required'
            ];
       
           
        }else{
            // Guardar el articulo

            $post = new Post();
            $post->user_id = $user->sub;
            $post->category_id = $params->category_id;
            $post->title = $params->title;
            $post->content = $params->content;
            $post->image = $params->image;
            $post->save(); 
            $data=[
                'code' => 200,
                'status'=>'success',
                'category' => $post
            ];
        }
    }else{
        $data=[
            'code' => 400,
            'status'=>'error',
            'message' => 'No has enviado ninguna entrada'
        ];
    }
        // Devolver resultado
        return response()->json($data, $data['code']);
    }
}
