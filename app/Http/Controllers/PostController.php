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
        $this->middleware('api.auth', ['except' => [
            'index',
             'show',
             'getImage',
             'getPostsByCategory',
             'getPostsByUser',
             ]]);
    }

    public function index()
    {
        $posts = Post::all()->load('category');

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }
    public function show($id)
    {
        $post = Post::find($id)->load('category');
        if (is_object($post)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'category' => $post
            );
        } else {
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
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);


        if (!empty($params_array)) {
            // conseguir usuario identificado
            $user = $this->getIdentity($request);
            // Validar los datos
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
            ]);
            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado la Entrada.',
                    'image' => 'required'
                ];
            } else {
                // Guardar el articulo

                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;
                $post->save();
                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'category' => $post
                ];
            }
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ninguna entrada'
            ];
        }
        // Devolver resultado
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request)
    {
        // Recoger datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        //datos para devolver

        $data = [
            'code' => 400,
            'status' => 'error',
            'message' => 'Datos enviados incorrectos'
        ];
        if (!empty($params_array)) {
            // Validar los datos
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
            ]);
            if ($validate->fails()) {
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }
            // quitar lo que no se va a actualizar
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['user']);
            unset($params_array['create_at']);
            //conseguir usuario identificado
            $user = $this->getIdentity($request);

            //obtener si registro
            $post = Post::where('id', $id)
                ->where('user_id', $user->sub)
                ->first();

            if (!empty($post) && is_object($post)) {
                // Actualizar el registro  entradas
                $post->update($params_array);
                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post,
                    'changes' => $params_array
                ];
            }
            // $where = [
            //     'id'=> $id,
            //     'user_id'=> $user->sub
            // ];
            // $post = Post::updateOrCreate($where,$params_array);

        }
        // devolver repuesta
        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request)
    {
        //conseguir usuario identificado
        $user = $this->getIdentity($request);


        //obtener si existe el registro
        $post = Post::where('id', $id)->where('user_id', $user->sub)->first();
        if (!empty($post)) {
            // Borrarlo
            $post->delete();
            //devolver algo
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'El post no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }

    private function getIdentity($request)
    {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);
        return $user;
    }

    public function upload(Request $request)
    {
        // Recoger datos de la peticion
        $image = $request->file('file0');

        //validacion de la imagen
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // Guardar la imagen
        if (!$image || $validate->fails()) {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen'
            );
        } else {
            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('images')->put($image_name, \File::get($image));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
        }
        // devolver datos
        return response()->json($data, $data['code']);
    }

    public function getImage($filename)
    {
        // Comprobar si existe la imagen
        $isset = \Storage::disk('images')->exists($filename);
        if ($isset) {
            // Conseguir la imagen
            $file = \Storage::disk('images')->get($filename);
            //devolver la imagen
            return new Response($file, 200);
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe.'
            );
        }
        return response()->json($data, $data['code']);
    }


    public function getPostsByCategory($id){
        $posts = Post::where('category_id',$id)->get();
        $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $posts
        ];

        return response()->json($data, $data['code']);
    }
    public function getPostsByUser($id){
        $posts = Post::where('user_id',$id)->get();
        $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $posts
        ];

        return response()->json($data, $data['code']);
    }
}
