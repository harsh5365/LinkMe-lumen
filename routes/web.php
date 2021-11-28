<?php

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
$router->get('migration', 'Api\UserController@migration');
$router->get('errorlogs', array('as' => 'errorlogs', 'uses' => 'Api\UserController@indexerror'));
$router->group(['prefix' => 'api'], function () use ($router) {
	$router->post('login', 'Api\UserController@login');
	$router->post('register', 'Api\UserController@register');
	$router->post('/password/reset-request', 'Api\UserController@sendResetLinkEmail');
	$router->post('/password/reset', ['as' => 'password.reset', 'uses' => 'Api\UserController@reset']);
	$router->get('/verify_account', 'Api\UserController@verifyAccount');
	$router->get('verify_mail_resend', 'Api\UserController@resendVerifyEmail');
    $router->get('show_public_links', 'Api\LinkController@showPublicLinks');

	$router->group(['middleware' => 'auth:api'], function () use ($router) {
		$router->post('change_password', 'Api\UserController@changePassword');
		$router->get('user', 'Api\UserController@details');
		$router->post('logout', 'Api\UserController@logout');

		// setup user
		$router->post('setup_user', 'Api\UserController@setupUser');
		$router->post('profile', 'Api\UserController@saveProfile');
		$router->get('getCategories', 'Api\UserController@getCategories');

		// link saving
        $router->get('delete_link', 'Api\LinkController@deleteLink');
		$router->post('save_link', 'Api\LinkController@saveLink');
        $router->get('list_links', 'Api\LinkController@linkUserLinks');
        $router->post('sort_links', 'Api\LinkController@SortUserLinks');
	});
});
