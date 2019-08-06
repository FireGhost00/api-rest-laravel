<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth{

    public $key;
    public function __construct()
    {
        $this->key = 'esta_es_una_clave_ultra_secreta-99887766';
    }

    public function signup($email, $password,$getToken = null){
    // Buscar si existe el usuario con sus credenciales
$user = User::where([
    'email' => $email,
    'password' => $password
])-> first();


    // comprobar si son correctas
    $singup = false;
    if(is_object($user)){
        $singup = true;
    }

    // generar el token con los datos del usuario identificado
    if($singup){
        $token = array(
            'sub'       =>  $user->id,
            'email'     =>  $user->email,
            'name'      =>  $user->name,
            'surname'   =>  $user->surname,
            'iat'       =>  time(),
            'exp'       =>  time() + (7 * 24 * 60 * 60)
        );

        $jwt = JWT::encode($token,$this->key,'HS256');

        $decoded = JWT::decode($jwt,$this->key,['HS256']);
         // devolver los datos decodificados o el token en funcion de un parametro
        if(is_null($getToken)){
            $data = $jwt;
        }else{
           $data = $decoded;
        }
    }else{
        $data = array(
            'status' => 'Error',
            'message' => 'Login ha fallado.'
        );
    }
   

    return $data;
    }


}

?>