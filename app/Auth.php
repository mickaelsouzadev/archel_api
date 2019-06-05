<?php  
/* PHP Class for managing the authentication of the users
 * AUTHOR: Mickael Souza
 * LAST EDIT: 2019-05-28
 */
namespace App;
use App\Session;
use App\Cookie;
use App\Password;
use App\Http;
// use App\Models\UserModel;

class Auth 
{
	private $user;

	public function __construct()
	{
		Session::start();
	}

	public function login($form_password, $user, $type = 'user', $isLoginApi = false)
	{
		$this->user = $user;
		
		try {
			if(Password::verifyPassword($form_password, $this->user['password'])) {
				if(!$isLoginApi){
					$this->createSession($type);
				}

				return true;
			} else {
				throw new \Exception('Email ou senha incorretos!');
			}
		} catch(\Exception $exception) {
			return $exception->getMessage();
		}

	}

	public function newUserLogin($user, $type = 'user')
	{
		$this->user = $user;

		if(Session::sessionExists('user')) {
			Session::setSessionAttribute('user', "");
		}

		$this->createSession($type);
	}

	public static function verifyAdminIsLogged()
	{
		if(Session::sessionExists('admin')) {
			

			if(Session::getSessionAttribute("admin", "credentials")) {
				
				return true;
			}
			
		} else {
			return false;
		}	 
	}

	public static function verifyUserIsLogged()
	{	
		if(Session::sessionExists('user')) {
			return true;
		} else {
			return Cookie::cookieExists('user');
		}
	}

	public function createSession($type = 'user')
	{
		
		if($type == "admin") {
			Session::setSessionAttribute("admin", "credentials", $this->user);
		} else {
			Session::setSession($type, $this->user);
		}

		
	}

	public function createCookie($type = 'user')
	{
		Cookie::setCookies([$type => $this->user['username'], 'password' => $this->user['password']]);
	}

	public function getJwtToken($password, $alg = 'HS256') 
	{
		$header = [
			'alg' => $alg,
			'type' => 'JWT'
		];

		$header = base64_encode(json_encode($header));

		$payload = [
			'iss' => $_SERVER['HTTP_HOST'],
			'password' => $password
		];

		$payload = base64_encode(json_encode($payload));

		$signature = base64_encode(hash_hmac('sha256',"$header.$payload",'fr12018arch3l',true));
		
		$jwt = "$header.$payload.$signature";

		return $jwt;
	}

	public static function verifyJwtToken()
	{
		$bearer_token = Http::requestAHeader("authorization");
		
		if (preg_match('/Bearer\s(\S+)/', $bearer_token, $matches)) {
            $token = $matches[1];
            
            $part = explode(".",$token);
            $header = $part[0];
            $payload = $part[1];
            $signature = $part[2];

            $valid = base64_encode(hash_hmac('sha256',"$header.$payload",'fr12018arch3l',true));

            if($valid == $signature) {
            	return true;
            } else {
            	return false;
            }

        } else {
        	return false;
        }


		
	}
	
	public function logout()
	{
		if(Session::sessionExists('user') || Cookie::cookieExists('user')) {
			Session::destroySession('user');
			Cookie::destroyCookie('user');
		}

		if(Session::sessionExists('admin') || Cookie::cookieExists('admin')) {
			Session::destroySession('admin');
			Cookie::destroyCookie('admin');
		}
	}
		
}