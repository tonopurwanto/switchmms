<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Doitem extends Model
{
    protected $table      = 'doitem';
    protected $primaryKey = 'idx';

    const CREATED_AT      = 'datecreate';
    const UPDATED_AT      = 'dateupdate';
}
