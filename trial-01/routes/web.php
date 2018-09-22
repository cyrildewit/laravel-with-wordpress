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

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/wordpress/{params?}', function ($params = null) {
//     if (! $params) {
//         $params = 'index';
//     }

//     $path = '../wordpress/' . $params.'.php';

//     return $path;

//     return require $path;
// });


Route::get('/wordpress/config', function () {
    return require '../wordpress/wp-admin/setup-config.php';
});
