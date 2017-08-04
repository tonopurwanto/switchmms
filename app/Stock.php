<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table      = 'whstock';
    protected $primaryKey = null;

    public $timestamps    = false;
    public $incrementing  = false;
}
