<?php  

namespace App\Controllers;
use App\Form;
use App\Password;
use App\Http;

class ContatoController extends Controller 
{
	public function __construct() 
	{
		parent::__construct();
	    $this->model->setTable("contato");
	}

	public function index($id = null) 
	{
		$data = $this->model->select()->where("deleted", 0)->where("accepted", 1)->run("fetchAll");
		 
		if($id) {
			$data = $this->model->select()->where("id", $id)->run("fetch");
		}
	
 		if($data !== []) {
 			http::jsonResponseData(true, "", $data);
 		} else {
 			http::jsonResponseData(false, "Não foi possível retornar os contatos!", null);
 		}
 		
	}

	public function insert() 
	{

		$fields = ['nome'=>FILTER_SANITIZE_STRING, 'sobrenome'=>FILTER_SANITIZE_STRING, 'email'=>FILTER_SANITIZE_STRING, 'senha'=>FILTER_SANITIZE_STRING, 'telefone_comercial'=>FILTER_SANITIZE_STRING, 'telefone_residencial'=>FILTER_SANITIZE_STRING, 'telefone_celular'=>FILTER_SANITIZE_STRING];

		$this->form_manager = new Form($fields, INPUT_POST);
        $form_data = $this->form_manager->getFilteredData();

       	if(!$this->verifyIfEmailIsRegistred($form_data['email'])) {
	        $form_data['senha'] = Password::hashPassword($form_data['senha']);
	          
	        if($this->model->insert($form_data)->run("lastInsertId", $form_data)) {
	        	Http::jsonResponse(true, "");
	        } else {
	        	Http::jsonResponse(false, "Não foi possível cadastrar seu usuário, verifique todos os seus campos ou tente mais tarde!");
	        }
      	} else {
      		Http::jsonResponse(false, "Email já castrado!");
      	}
	}

	public function update($id) 
	{
		$this->form_manager = new Form(null, null, true);
		$form_data = $this->form_manager->getFilteredData();

		if($this->model->update($form_data, $id)->run("rowCount", $form_data)) {
			Http::jsonResponse(true, "");
		} else {
			Http::jsonResponse(false, "Não foi possível atualizar suas informações");
		}

	}

	public function delete($id) 
	{
		if($this->model->update(['deleted'=>1], $id)->run("rowCount", ['deleted'=>1])) {
			Http::jsonResponse(true, "");
		} else {
			Http::jsonResponse(false, "Não foi possível excluir este usuaário!");
		}
	}

	public function deleteDefinitively($id) 
	{
		print $this->model->delete($id)->run("rowCount");
	}

	public function login()
	{
		$fields = ['email'=>FILTER_SANITIZE_STRING, 'senha'=>FILTER_SANITIZE_STRING];

		$this->form_manager = new Form($fields);
		$form_data = $this->form_manager->getFilteredData();


		$data = $this->model->select()->where('email', $form_data['email'])->run("fetch", $form_data);

		$user = ['id'=>$data['id'], 'username'=>$data['nome'], 'email'=>$data['email'], 'password'=>$data['senha'], 'admin'=>$data['admin']];

		$login = $this->auth->login($form_data['senha'], $user, null, false);

		
		if($login === true) {
			if($data['deleted'] == true) {
				Http::jsonResponseData(false, "Seu perfil foi removido!", null);
			} elseif($data['accepted'] == true) {
				$user['token'] = $this->auth->getJwtToken($user['password']);
				Http::jsonResponseData(true, " ", $user);
			} else {
				Http::jsonResponseData(false, "Você ainda não foi aprovado por um administrador", null);
			}
		} else {
			Http::jsonResponseData(false,$login,null);
		}
		
	}

	public function acceptContact($id) {
		if($this->model->update(['accepted'=>true], $id)->run("rowCount", ['accepted'=>true])) {
			Http::jsonResponse(true, "");
		} else {
			Http::jsonResponse(false, "Não foi possível aceitar este usuário!");
		}

	}

	private function verifyIfEmailIsRegistred($email)
    {
        return $this->model->select()->where('email', $email)->run("rowCount");
    }
}