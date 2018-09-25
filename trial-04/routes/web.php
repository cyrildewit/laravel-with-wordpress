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

use Corcel\Model\Page;



Route::get('/', function () {
        return view('welcome');
});

// Route::group(['domain' => 'wp.lwp-trial-three.test'], function () {
//     Route::get('/', function () {
//         return view('welcome');
//     });
// });

// Route::get('/cookie/create', function () {

//     $expiresAfter = 60 * 24 * 7;
//     Cookie::queue('shared_cookie', 'my_shared_value', $expiresAfter, null, '.lwp-trial-three.test');

// });
