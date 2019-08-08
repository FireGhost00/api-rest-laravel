<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Http\Middleware\ApiAuthMiddleware;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pruebas/{nombre}', function($nombre){
    $texto = '<h2>texto desde una ruta</h2>';
    $texto .= 'Nombre '.$nombre;
return view('pruebas', array(
    'texto' => $texto
));
});

Route::get('/animales','PruebasController@index');
Route::get('/test-orm','PruebasController@testOrm');



    // Rutas de pruebas
    Route::get('/usuario/pruebas','UserController@pruebas');
    Route::get('/categoria/pruebas','CategoryController@pruebas');
    Route::get('/entradas/pruebas','PostController@pruebas');

       // Rutas del controlador de usuarios
       Route::post('/api/register', 'UserController@register');
       Route::post('/api/login', 'UserController@login');
       Route::put('/api/user/update', 'UserController@update');
       Route::post('/api/user/upload', 'UserController@upload')->middleware(ApiAuthMiddleware::class);
       Route::get('/api/user/avatar/{filename}','UserController@getImage');
       Route::get('/api/user/detail/{id}','UserController@detail');