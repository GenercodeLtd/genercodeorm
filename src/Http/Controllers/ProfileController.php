<?php
namespace GenerCodeOrm\Http\Controllers;

use \Illuminate\Container\Container;
use \GenerCodeOrm\Exceptions as Exceptions;
use \GenerCodeOrm\InputSet;
use \GenerCodeOrm\DataSet;
use \Illuminate\Support\Fluent;

class ProfileController extends AppController {

    
   
    function __construct(Container $app) {
        parent::__construct($app);
    }

    function checkUser() {
        return $this->profile->toArr();
    }


    
    function createAnon($name) {
        if (!$this->profile->allowAnonymousCreate($name)) {
            throw new Exceptions\PtjException("Anonymous profiles are not allowed for " . $name);
        }
        $inputSet = new InputSet();
        $inputSet->data(["type"=>$name, "terms"=>1]);
        $model = $this->model("users");
        $dataSet = new DataSet($model);
        $dataSet->data($inputSet);
        $dataSet->validate();
        return $model->setFromEntity(true)->insertGetId($dataSet->toCellNameArr());
    }


    function create($name, $params) {
        if (!$this->profile->allowCreate($name)) {
            throw new Exceptions\PtjException("Cannot create profile " . $name);
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
    
        return $model->setFromEntity(true)->insertGetId($dataSet->toCellNameArr());
    }


    function userDetails() {
        $repo = $this->model("users");
        $repo->select(["name", "email", "type"]);
        $repo->where("id", "=", $this->profile->id);
        return $repo->setFromEntity()->take(1)->get()->first();
    }


    function login($request, $response, $type) {

        if (!$this->profile->allowAssume($type)) {
            throw new Exceptions\PtjException("Cannot login to profile " . $type);
        }

        $request = $this->app->get("request");

        $params = new Fluent($request->getParsedBody());

        $repo = $this->model("users");
        $repo->select("id", "password");
    
        $params["type"] = $type;

        $inputSet = new InputSet();
        $inputSet->data(["email"=>$params["email"], "type"=>$type]);

        $dataSet = new DataSet($repo);
        $dataSet->data($inputSet);
        $dataSet->validate();

        $auth = $this->app->get("auth");
        if ($auth->attempt(["email"=>$params["email"], "type"=>$type, "password"=>$params["password"]])) {
            $request->session()->regenerate();
            $user = $auth->user();

            $response->getBody()->write(json_encode(["--id"=>$user->getAuthIdentifier()]));
            return $response;
        } else {
            throw new Exceptions\PtjException("This username / password was not recognised");
        }
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