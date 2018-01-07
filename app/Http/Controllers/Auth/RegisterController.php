<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Auth;
use Socialite;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(Request $request)
    {
		$data = $request->all();
		$validation =  Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
		if($validation->fails()){
			$response = $validation->messages();
		}else {
			
			$user =  User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
			'LoginTypeId' => isset($data['email']) ? $data['email'] : null,
			'OAuthUniqueId' => isset($data['OAuthUniqueId']) ? $data['OAuthUniqueId'] : null,
			'DeviceId' => isset($data['DeviceId']) ? $data['DeviceId'] : null,
			'DeviceType' => isset($data['DeviceType']) ? $data['DeviceType'] : null,
			'UdId' => isset($data['UdId']) ? $data['UdId'] : null,
			'IsActive' => isset($data['IsActive']) ? $data['IsActive'] : null,
			]);
			if($user){
				$response = array('account_created' => 'Account created successfully!!, Please login');
			}else{
				$response = array('went_wrong' => 'Some thing went wrong, Please try again');
			}
		}
        return response()->json($response);
    }
	public function redirectToProvider($provider)
    {
		return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from provider.  Check if the user already exists in our
     * database by looking up their provider_id in the database.
     * If the user exists, log them in. Otherwise, create a new user then log them in. After that 
     * redirect them to the authenticated users homepage.
     *
     * @return Response
     */
    public function handleProviderCallback($provider)
    {
		//return Socialite::driver($provider)->user();
        $user = Socialite::driver($provider)->stateless()->user();
		$authUser = $this->findOrCreateUser($user, $provider);
        
		$token = $authUser->createToken('API Token')->accessToken;
		
		$response['access_token'] = $token;
		$response['refresh_token'] = $user->refreshToken;
		$response['expires_in'] = $user->expiresIn;
		$response['user'] = $authUser ;
		return response()->json($response, 200);
    }
	public function findOrCreateUser($user, $provider)
    {
        $authUser = User::where('provider_id', $user->id)->first();
		if ($authUser) {
		    return $authUser;
        }
        return User::create([
            'fname'     => $user->name,
            'email'    => $user->email,
            'provider' => $provider,
			'loginTypeId' => $provider,
            'provider_id' => $user->id
        ]);
    }
	public function getUserByToken($provider, $token){
		$user = Socialite::driver($provider)->userFromToken($token);
		return response()->json($user, 200);
	}
}
