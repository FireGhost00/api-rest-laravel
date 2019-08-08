<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;


class UserController extends Controller
{
    // public function pruebas(Request $request)
    // {
    //     return "Accion de pruebas de USER_CONTROLLER";
    // }


    public function register(Request $request)
    {

        // Recoger los datos del usuario por POST

        $json = $request->input('json', null);

        $params = json_decode($json); //objeto
        $params_array = json_decode($json, true); //array


        if (!empty($params_array) && !empty($params)) {
            //limpiar datos

            $params_array = array_map('trim', $params_array);


            //validaf datos

            $validate = \Validator::make($params_array, [
                'name'       => 'required|alpha',
                'surname'    => 'required|alpha',
                'email'      => 'required|email|unique:users',
                'password'   => 'required'
            ]);

            if ($validate->fails()) {
                // validacion fallo
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario no se ha creado',
                    'errors'  => $validate->errors()
                );
            } else {
                // validacion pasada correctamente


                //cifrar la contraseña
                $pwd =  hash('sha256', $params->password);


                //crear el usuario

                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';

                // Guardar el usuario

                $user->save();


                $data = array(
                    'status' => 'succes',
                    'code' => 200,
                    'message' => 'El usuario se creo correctamente',
                    'user' => $user

                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Los datos enviados no son correctos'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function login(Request $request)
    {
        $jwtAuth = new \JwtAuth();

        // Recibir  datos por Post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        // Validar datos 
        $validate = \Validator::make($params_array, [

            'email'      => 'required|email',
            'password'   => 'required'
        ]);

        if ($validate->fails()) {
            // validacion fallo
            $singup = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no se ha podido identificar',
                'errors'  => $validate->errors()
            );
        } else {
            // cifrar la password
            $pwd =  hash('sha256', $params->password);
            // devolver token o datos
            $singup =  $jwtAuth->signup($params->email, $pwd);
            if (!empty($params->gettoken)) {
                $singup =  $jwtAuth->signup($params->email, $pwd, true);
            }
        }



        return response()->json($singup, 200);
    }


    public function update(Request $request)
    {
        // Comprobar si el usuario esta identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);


        // recoger los datos por post

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);


        if ($checkToken && !empty($params_array)) {


            //sacar usuario identificado
            $user = $jwtAuth->checkToken($token, true);
            // validar los datos
            $validate =  \Validator::make($params_array, [
                'name'       => 'required|alpha',
                'surname'    => 'required|alpha',
                'email'      => 'required|email|unique:users,' . $user->sub

            ]);

            // Quitar los datos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['create_at']);
            unset($params_array['remember_token']);

            // Actualizar usuario en bd
            $user_update = User::Where('id', $user->sub)->update($params_array);

            // Devolver array con resultado

            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user,
                'change' => $params_array
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no esta identificado.'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function upload(Request $request)
    {
        // Recoger datos de la peticion
        $image = $request->file('file0');
        
        //validacion de la imagen
        $validate = \Validator::make($request->all(),[
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // Guardar imagen

        if (!$image || $validate->fails()) {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al no subir imagen'
            );
        } else {
            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
            
        }
        return response()->json($data, $data['code']);
    }

    public function getImage($filename){

        $isset =\Storage::disk('users')->exists($filename);
if($isset){
        $file = \Storage::disk('users')->get($filename);

        return new Response($file, 200);
}else{
    $data = array(
        'code' => 404,
        'status' => 'error',
        'message' => 'La imagen no existe.'
    );
}
return response()->json($data,$data['code']);
    }

    public function detail($id){
        $user = User::find($id);

        if(is_object($user)){
            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $user
            );
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El usuario no existe.'
            );
        }
        return response()->json($data, $data['code']);
    }
}
