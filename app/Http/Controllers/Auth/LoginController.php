<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;
use Socialite;
use Illuminate\Http\Request;
use App\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }
    public function issueToken(Request $request){
    	try {
			$data = $request->all();
			$http = new \GuzzleHttp\Client;

			$response = $http->post('http://localhost/laravel_passport/public/oauth/token', [
			    'form_params' => [
			        'grant_type' => 'password',
			        'client_id' => '1',
			        'client_secret' => 'RzyRi2vSZMsVZZd9IADI2Lys17tubFmRy0sETo2G',
			        'username' => $data['username'],
			        'password' => $data['password'],
			        'scope' => '',
			    ],
			]);
			if (Auth::attempt(['email' => $data['username'], 'password' => $data['password']])) {
				$response = json_decode((string) $response->getBody(), true);
					User::where('id', Auth::user()->id)->update(['deviceId' => $data['deviceId'], 'deviceType' => $data['deviceType'], 'udId' => $data['udId']] );
				$response['user'] =  User::find(Auth::user()->id);
				
				return response()->json($response, 200);
        	}else {
				$response = array("error" => "invalid_credentials","message" => "The user credentials were incorrect.");
				return response()->json($response, 400);
			}
		} catch ( \Exception $e ) {
			$response = array("error" => "invalid_credentials","message" => "The user credentials were incorrect.");
			return response()->json($response, 400);
		}
		
	}
	
}
