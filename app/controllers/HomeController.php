<?php  

namespace App\Controllers;

class HomeController extends Controller{
    
    public function __construct()
    {
        parent::__construct();
        // $this->model->setTable("Review");
        // $this->model->addTable("Comic");
    }
    
    public function index()
    {
		$data['title'] = "Welcome!";
        $this->view->loadPage("home",$data);
    }

  
    
}