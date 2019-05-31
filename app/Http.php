<?php  
/* PHP Class for manage responses and requests
 * AUTHOR: Mickael Souza
 * LAST EDIT: 2018-11-26
*/
namespace App;

class Http 
{

	public static function request() 
	{
		$request_data = file_get_contents("php://input");

		return $request_data;
	}

	public static function requestArray()
	{
		return json_decode(self::request(), true);
	}

	public static function jsonResponse($success, $message = null)
	{
		 $response = array(
            "success"=>$success,
            "message"=>$message
        );

		echo json_encode($response);
	}

	public static function jsonResponseData($success, $message = null, $data = null)
	{
		// if($data !== null) {
		// 	$data = json_encode($data);
		// }

		$response = array(
	        "success"=>$success,
	        "message"=>$message,
	        "data"=>$data
	    );

		// var_dump($response);

		echo json_encode($response);
	}

}