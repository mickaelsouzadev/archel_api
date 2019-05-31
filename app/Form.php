<?php
/* PHP Class for filtering and retriving information from forms
 * AUTHOR: Antony Acosta
 * LAST EDIT: 2018-10-22
 */
namespace App;
use App\Http;

class Form{
    
    private $data = array();
    private $files = array();
    private $fields;
    private $filters;
    private $method;
    private $isJsonRequest;
    
    public function __construct(array $data = null, $method = null, $isJsonRequest = false){
        if(!$isJsonRequest) {

            if($method === null) {
                $method = INPUT_POST;
            }

            $this->fields   = array_keys($data);
            $this->filters  = $data;
            $this->method   = $method;
        }
        
        $this->isJsonRequest = $isJsonRequest;
    }
    
    public function getFilteredData(){

        if($this->isJsonRequest) {
            $this->data = $this->filterJsonData();
        } else {
            if(!$this->data){
                foreach($this->fields as $field){
                    $this->data[$field] = filter_input($this->method, $field, $this->filters[$field]);
                    
                    if($this->data[$field] === ""){
                        $this->data[$field] = null;
                    }
                }
            }
        }

        return $this->data;
    }
    
    public function getEmptyFields(){
        return array_keys(
                array_filter($this->data,function($e){
                    return $e === null;
                })
         );
    }

    private function filterJsonData()//PHP Don't have an option to filter a PUT request with filter_input function
    {
       $data = Http::requestArray();
       $args = [];
    
       foreach ($data as $key => $value) {
           $args[$key] = FILTER_SANITIZE_STRING; 
       }
      
       return  filter_var_array($data, $args);
    }
    
    public function filterSingleFile($fieldname, array $validformats, $maxsize = 1048576){
        $file = $_FILES[$fieldname];
        $isValidFormat   = in_array($file['type'], $validformats);
        $isValidSize     = ($file['size'] <= $maxsize) ? true : false; 
        $error           = $file['error'];
        if($isValidFormat && $isValidSize && !$error){
               $file['name'] = filter_var($file['name'], FILTER_SANITIZE_STRING);
               $this->files[] = $file;
               return true;
        }
        return false;
    }
    
    //files needs to be filtered first
    public function saveUploadedFiles($dir){
        try{
            if(!$this->files){
                throw new Exception("There are no files, have you filtered them?");
            }
            foreach($this->files as $file){
                $done = move_uploaded_file($file['tmp_name'], $dir.$file['name']);
                return $done;
            }
        }catch(Exception $e){
            echo "Error: ".$e->getMessage();
        }
        
    }
    
}