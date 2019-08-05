<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function pruebas(Request $request){
        return "Accion de pruebas de USER_CONTROLLER";
    }
    public function register(Request $request){

        return "Accion de Registro de usuarios";
    }
    public function login(Request $request){
        return "Accion de login de usuarios";
    }
}
