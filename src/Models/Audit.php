<?php

namespace GenerCodeOrm\Models;

use Illuminate\Database\Eloquent\Model;


class Audit extends Model {

    protected $table = "audit";
    protected $primaryKey = "id";

    const CREATED_AT = "date_created";

    protected $fillable = [
        "model",
        "action",
        "model_id",
        "user_login_id",
        "log"
    ];
 

}