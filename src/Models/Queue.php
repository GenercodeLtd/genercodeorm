<?php

namespace GenerCodeOrm\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\UUID;


class Queue extends Model {

    use UUID;

    protected $table = "queue";
    protected $primaryKey = "id";

    const CREATED_AT = "date_created";

    protected $fillable = [
        "progress"
    ];
 

}