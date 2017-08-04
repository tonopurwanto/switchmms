<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $table      = 'user_name';
    protected $primaryKey = 'Nomor_Hp';

    public $timestamps    = false;
    public $incrementing  = false;
}
