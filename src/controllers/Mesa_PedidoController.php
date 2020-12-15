<?php

namespace App\Controllers;

use App\Models\Mesa_Pedido;
use App\Models\Mesa;
use App\Models\Pedido;
use \Firebase\JWT\JWT;

class Mesa_PedidoController
{
    public function addPedido($response, $request, $args)
    {
        $idMesa=$args['idMesa'];
       

        $idPedido=PedidoController::generarCodigo();

        $mesa=Mesa::where("idMesa",$idMesa);

        $pedido = new Pedido;

        $datos=$request->getParsedBody();

        $pedido->idPedido=$idPedido;//Verificar que no existan pedidos con ese ID
        $pedido->nombreCliente=$datos["nombreCliente"];
        $pedido->comida=$datos["comida"]??"";
        $pedido->cerveza=$datos["cerveza"]??"";
        $pedido->trago->$datos["trago"]??"";
        $pedido->postre->$datos["postre"]??"";

        
        
        return $response;
    }
}