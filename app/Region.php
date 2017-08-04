<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $table      = 'csstatid4';
    protected $primaryKey = 'CSSTATID';

    const CREATED_AT      = 'datecreate';
    const UPDATED_AT      = 'dateupdate';

    public $incrementing  = false;
}
