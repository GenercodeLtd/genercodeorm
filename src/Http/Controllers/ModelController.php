<?php

namespace GenerCodeOrm\Http\Controllers;

use Illuminate\Support\Fluent;
use GenerCodeOrm\Models\Model;
use Nyholm\Psr7\Response;
use GenerCodeOrm\Inputs;


class ModelController extends AppController
{


    protected $collection;

    public function response($results) {
        $response = new Response();
        $response->getBody()->write(json_encode($results, JSON_INVALID_UTF8_SUBSTITUTE));
        return $response;
    }

    public function create($request)
    {
        $this->checkPermission("post");

        $this->collection->create($request);
        
        return $this->response($this->repo->create($request->safe()));
    }


    public function importFromCSV($request)
    {
        $this->checkPermission("post");

        $this->collection->import($request);
      
        return $model->import($request->safe());
    }



    public function update($request)
    {
        $this->checkPermission("put");

        $this->collection->update($request);
      
        return $this->response($this->repo->update($request->safe()));
    }



    public function destroy($request)
    {
        $this->checkPermission("delete");

        $this->collection->destroy($request);
     
        return $this->response($this->repo->destroy($request->safe()));
    }



    public function resort($request)
    {
        $this->checkPermission("put");

        $this->collection->resort($request);
     
        return $this->response($this->repo->resort($request->safe()));
    }




    public function get($request) {

        $this->checkPermission("get");

        $this->collection->get($request);

        return $this->response($this->repo->get($request->safe(), $inputs->structure()));
    }


    public function active($request)
    {
        $this->checkPermission("get");
        $this->collection->active($request);
        return $this->response($this->repo->active($request->safe(), $inputs->structure()));
    }



    public function first($request)
    {
        $this->checkPermission("get");
        $this->collection->get($request);
        return $this->response($this->repo->first($request->safe(), $inputs->structure()));
    }


    public function last($request)
    {
        $this->checkPermission("get");
        $this->collection->get($request);
        return $this->response($this->repo->last($request->safe(), $inputs->structure()));
    }


    public function count($request)
    {
        $this->checkPermission("get");
        $this->collection->get($request);
        return $this->response($this->repo->count($request->safe(), $inputs->structure()));
    }

}



//$response->getBody()->write(json_encode($results, JSON_INVALID_UTF8_SUBSTITUTE));
