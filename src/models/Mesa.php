<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    protected $table='mesas';
    public $timestamps = false;
    protected $primaryKey = 'idMesa';
    public $incrementing = false;


}