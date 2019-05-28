<?php  

namespace App\Controllers;
use App\Form;
use App\Password;

class ContatoController extends Controller 
{
	public function __construct() 
	{
		parent::__construct();
	    $this->model->setTable("contato");
	}

	public function index($id = null) 
	{
		$data = $this->model->select()->where("deleted", 0)->run("fetchAll");
		 
		if($id) {
			$data = $this->model->select()->where("id", $id)->run("fetch");
		}

 		echo json_encode($data);
	}

	public function insert() 
	{

		$fields = ['nome'=>FILTER_SANITIZE_STRING, 'sobrenome'=>FILTER_SANITIZE_STRING, 'email'=>FILTER_SANITIZE_STRING, 'senha'=>FILTER_SANITIZE_STRING, 'telefone_comercial'=>FILTER_SANITIZE_STRING, 'telefone_residencial'=>FILTER_SANITIZE_STRING, 'telefone_celular'=>FILTER_SANITIZE_STRING];

		$this->form_manager = new Form($fields);
        $form_data = $this->form_manager->getFilteredData();

       	if(!$this->verifyIfEmailIsRegistred($form_data['email'])) {
	        $form_data['senha'] = Password::hashPassword($form_data['senha']);
	          
	        if($this->model->insert($form_data)->run("lastInsertId", $form_data)) {
	        	$this->jsonResponse(true, "");
	        } else {
	        	$this->jsonResponse(false, "Não foi possível cadastrar seu usuário, verifique todos os seus campos ou tente mais tarde!");
	        }
      	} else {
      		$this->jsonResponse(false, "Email já cadastrado!");
      	}
	}

	public function update($id) 
	{
		$form_data = Form::getPutRequest();
		
		

		if($this->model->update($form_data, $id)->run("rowCount", $form_data)) {
			$this->jsonResponse(true, "");
		} else {
			$this->jsonResponse(false, "Não foi possível atualizar suas informações");
		}

	}

	public function delete($id) 
	{
		print $this->model->update(['deleted'=>1], $id)->run("rowCount", ['deleted'=>1]);
	}

	public function login()
	{
		$fields = ['email'=>FILTER_SANITIZE_STRING, 'senha'=>FILTER_SANITIZE_STRING];

		$this->form_manager = new Form($fields);
		$form_data = $this->form_manager->getFilteredData();


		$data = $this->model->select()->where('email', $form_data['email'])->run("fetch", $form_data);

		$user = ['id'=>$data['id'], 'username'=>$data['nome'], 'email'=>$data['email'], 'password'=>$data['senha']];

		if($this->auth->login($form_data['senha'], $user, null, false)) {
			echo json_encode($user);
		}
	}

	private function verifyIfEmailIsRegistred($email)
    {
        return $this->model->select()->where('email', $email)->run("rowCount");
    }
}