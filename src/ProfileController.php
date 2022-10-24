<?php
namespace GenerCodeOrm;

use \Illuminate\Container\Container;

class ProfileController extends AppController {

    protected Profile $profile;
   
    function __construct(Container $app) {
        parent::__construct($app);
        $this->profile = $app->get(\GenerCodeOrm\Profile::class);
    }

    function checkUser() {
        return $this->profile->toArr();
    }


    
    function createAnon($name) {
        if (!$this->profile->allowAnonymousCreate()) {
            throw new Exceptions\PtjException("Anonymous profiles are not allowed");
        }
        $inputSet = new InputSet();
        $inputSet->data(["type"=>$name, "terms"=>1]);
        $model = $this->model("users");
        $dataSet = new DataSet($model);
        $dataSet->data($inputSet);
        $dataSet->validate();
        return $model->setFromEntity()->insertGetId($dataSet->toCellNameArr());
    }


    function create($name, $params) {
        if (!$this->profile->allowCreate()) {
            throw new Exceptions\PtjException("Cannot create profile " . $this->profile->name);
        }
        $model = $this->model("users");
        if (isset($params["password"])) {
            $params["password"] = password_hash($params["password"], \PASSWORD_DEFAULT);
        }

        $params["type"] = $name;

        $inputSet = new InputSet();
        $inputSet->data($params);
        
        $dataSet = new DataSet($model);
        $dataSet->data($inputSet);
        $dataSet->validate();
    
        return $model->setFromEntity()->insertGetId($dataSet->toCellNameArr());
    }


    function login($type, $params) {

        $repo = $this->model("users");
        $repo->select("id", "password");
    
        $params["type"] = $type;

        $inputSet = new InputSet();
        $inputSet->data(["email"=>$params["email"], "type"=>$type]);

        $dataSet = new DataSet($repo);
        $dataSet->data($inputSet);
        $dataSet->validate();
      
        $repo->filterBy($dataSet);
        $repo->take(1);

        $res = $repo->setFromEntity()->get()->first();
        if (!$res) {
            throw new Exceptions\PtjException("This username / password was not recognised");
        }
   
       
        if (!isset($params["password"])) {
            throw new Exceptions\PtjException("This username / password was not recognised");
        } 

        if (!password_verify($params["password"], $res->password)) {
            //now compare the password part of this
            throw new Exceptions\PtjException("This username / password was not recognised");
        }


        return $res->id;
    }


    public function updatePasswordRequest($params) {
        $params->fields = ["--id"];
        if (!isset($params->data["code"]) OR !isset($params->data["password"])) {
            throw new Exceptions\PtjException("Incorrect parameters");
        }
        $params->limit = 1;
        $repo = new \PressToJam\Repos\UserLogin($pdo, $params);
        $obj = $repo->get();
        if (!$obj) {
            throw new Exceptions\PtjException("This username was not recognised");
        }

        $nparams = new Params();
        $nparams->data = ["password"=>$params->data["password"], "id"=>$obj->{"--id"}];
        $model = new PressToJam\Models\UserLogin($pdo, $params);
        $model->update();
        return "success";
    }


    
    
    public function getResetPasswordRequest($username) {
        $field = new Cells\String();
        $params->data = ["username"=>$username];
        $params->limit = 1;
        $repo = ModelFactory::create("UserLogin");
        $repo->select(["--id"]);
        $repos->username = $username;
        $obj = $repo->get();
        if (!$obj) {
            throw new Exceptions\PtjException("This username was not recognised");
        }

        $params = new Params();
        $params->data = ["--whisper-id"=>$field->getRandom(75), "id"=>$obj->{"--id"}];
        $model = new \PressToJam\Models\UserLogin($pdo, $params);
        $model->update();
        return "success";
    }

    public function getDictionary() {
        $dict_root = configs("dictionary.root");
        return json_encode(file_get_contents($dict_root . "/" . $this->profile->name . ".json"));
    }

 
    public function getSitemap() {
        return $this->profile->getSitemap();
    }
}