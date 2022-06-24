<?php
//use Illuminate\Support\Str;
/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'auth'], function () use ($router) 
{
   $router->post('register', 'AuthController@register');
   $router->post('login', 'AuthController@login');
   $router->post('logout', 'AuthController@logout');
   $router->get("/","AuthController@getProfile");
   $router->post("/","AuthController@updateProfile");
});

$router->group(['prefix' => 'home','namespace' => 'api'], function () use ($router) 
{
   $router->get('allcategory', 'CategoryController@allcategory');
   $router->get('allproducts', 'ProductsController@allproducts');
   $router->get('banner_home', 'BannerController@banner_home');
});

$router->group(['prefix' => 'detail','namespace' => 'api'], function () use ($router) 
{
   $router->get('product/{id}', 'ProductsController@product_details');
   $router->get('category/{category_id}', 'ProductsController@product_category');
   $router->get('cart','CartController@read');
   $router->post('cart','CartController@create');
   $router->get('cart/count-cart','CartController@count_cart');
   $router->delete('cart/{id}','CartControllerr@delete');
});

$router->group(['prefix' => 'historyorder','namespace' => 'api'],function () use ($router){
   $router->get('/','HistoryOrderController@index');
   $router->get('detail-pembelian/{id}', 'HistoryOrderController@detailPembelian');
});

$router->group(['prefix' => 'checkout','namespace' => 'api'],function () use ($router) {
   $router->post('order','CheckoutController@order_create');
   $router->get('order','CheckoutController@order_list');
});
//$router->get('/key', function(){
//    $key = Str::random(32);;
//    return $key;
//});