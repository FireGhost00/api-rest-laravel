<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;


class UserController extends Controller
{
    public function pruebas(Request $request){
        return "Accion de pruebas de USER_CONTROLLER";
    }
    public function register(Request $request){

        // Recoger los datos del usuario por POST

        $json = $request->input('json',null);

        $params = json_decode($json); //objeto
        $params_array = json_decode($json, true); //array
     

        if(!empty($params_array) && !empty($params)){
        //limpiar datos

        $params_array = array_map('trim', $params_array);


        //validaf datos

        $validate = \Validator::make($params_array,[
            'name'       => 'required|alpha',
            'surname'    => 'required|alpha',
            'email'      => 'required|email|unique:users',
            'password'   => 'required'
        ]);

        if($validate->fails()){
            // validacion fallo
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no se ha creado',
                'errors'  => $validate->errors()
            );
            
        }else{
            // validacion pasada correctamente


            //cifrar la contraseña
          $pwd =  hash('sha256',$params->password);
         

            //crear el usuario

            $user = new User();
            $user ->name = $params_array['name'];
            $user ->surname = $params_array['surname'];
            $user ->email = $params_array['email'];
            $user ->password = $pwd;
            $user ->role = 'ROLE_USER';

            // Guardar el usuario

            $user->save();
           

            $data = array(
                'status' => 'succes',
                'code' => 200,
                'message' => 'El usuario se creo correctamente',
                'user' => $user
                
            ); 
        }
    }else{
        $data = array(
            'status' => 'error',
            'code' => 404,
            'message' => 'Los datos enviados no son correctos'
        );  
    }

       return response()->json($data,$data['code']);
    }

    public function login(Request $request){
        $jwtAuth = new \JwtAuth();

        $email = 'ngom@gmail.com';
        $password = 'nelson';
        $pwd =  hash('sha256', $password);


        return response()->json( $jwtAuth->signup($email,$pwd,true),200);
    }
}
