<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table      = 'setting';
    protected $primaryKey = null;

    public $timestamps    = false;
    public $incrementing  = false;
}
