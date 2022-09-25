<?php
namespace GenerCodeOrm;

class ProfileController {

    protected $dbmanager;
    protected $profile;
   
    function __construct(\Illuminate\Database\DatabaseManager $dbmanager, Profile $profile) {
        $this->dbmanager = $dbmanager;
        $this->profile = $profile;
    }

    function checkUser() {
        return $this->profile->toArr();
    }

    
    function anonymousCreate($params) {
        if (!$this->profile->allowsAnonymousCreate()) {
            throw new Exceptions\PtjException("Anonymous profiles are not allowed");
        }
        $model = new Model($this->dbmanager, new SchemaRepository($this->profile->factory));
        $model->name = "users";
        $model->data = ["type"=>$params["type"]];
        $res = $model->create();
        $this->profile->id = $res["--id"];
        return $this->profile;
    }


    function create($params) {
        if (!$this->profile->allowCreate()) {
            throw new Exceptions\PtjException("Cannot create profile " . $this->profile->name);
        }
        $model = new Model($this->dbmanager, new SchemaRepository($this->profile->factory));
        $model->name = "users";
        if (isset($params["password"])) {
            $params["password"] = password_hash($params["password"], \PASSWORD_DEFAULT);
        }
        $model->data = $params;
        $res = $model->create();
        $this->profile->id = $res["--id"];
        return $this->profile;
    }


    function login($type, $params) {
        $repo = new Repository($this->dbmanager, new SchemaRepository($this->profile->factory));
        $repo->name = "users";
        $repo->fields = ["--id", "password"];
        $repo->where = ["email"=>$params["email"], "type"=>$type];
        $repo->limit = 1;

        $res = $repo->get();
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

        return $res->{"--id"};
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