<?php

use Illuminate\Support\Facades\Route;
use Skygdi\ShopifyPrivateAPPFoundation\Controllers\ShopifyInstallController;
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

Route::get('/install', [ShopifyInstallController::class, 'install']);
Route::get('/install_authorize', [ShopifyInstallController::class, 'installAuthorize']);
