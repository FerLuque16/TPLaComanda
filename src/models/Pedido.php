<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table='pedidos';
    public $timestamps = false;
    protected $primaryKey = 'idPedido';
    public $incrementing = false;

}
