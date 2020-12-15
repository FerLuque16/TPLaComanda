<?php

namespace App\Controllers;

use App\Models\Cerveza;
use App\Models\Comanda;
use App\Models\Comida;
use App\Models\Pedido;
use App\Models\Mesa;
use App\Models\Mesa_Pedido;
use App\Models\Postre;
use App\Models\Trago;
use \Firebase\JWT\JWT;
use Slim\Middleware\MethodOverrideMiddleware;

class PedidoController
{
    public function addMesa($request,$response, $args)
    {
        $mesa = new Mesa;
        $idMesa= PedidoController::CrearIdMesa();

        $mesa->idMesa=$idMesa;
        $mesa->estado="Habilitado";

        $mesa->save();
        //PedidoController::VerificarProductos($request,$response);

        $response->getBody()->write(json_encode("El codigo de mesa es $mesa->idMesa"));
        
        return  $response;
    }

    public function addPedido($request,$response, $args)
    {
        
        

        $idPedido=PedidoController::CrearIdPedido();
        $pedido = new Pedido;
       // $mesa = Mesa::where("idMesa", $idMesa)->first();
        $idMesa=$args['idMesa'];
        $datos=$request->getParsedBody();

        $pedido->idPedido=$idPedido;//Verificar que no existan pedidos con ese ID
        $pedido->nombreCliente=$datos["nombreCliente"];
        $pedido->comida=$datos["comida"]??"";
        $pedido->cerveza=$datos["cerveza"]??"";
        $pedido->trago=$datos["trago"]??"";
        $pedido->postre=$datos["postre"]??"";

        
            
            if($pedido->comida=="")
                $pedido->comida_estado="";
        else
            $pedido->comida_estado="Pendiente";

        if($pedido->cerveza=="")
                $pedido->cerveza_estado="";
        else
        $pedido->cerveza_estado="Pendiente";

        if($pedido->trago=="")
            $pedido->trago_estado="";
        else    
            $pedido->trago_estado="Pendiente";

        if($pedido->postre=="")
            $pedido->postre_estado="";
        else    
            $pedido->postre_estado="Pendiente";  

        




        $resultado=PedidoController::VerificarProductos($pedido);

        if($resultado == "")
        {
            //$response->getBody()->write(json_encode("Correcto"));

            $mesa=Mesa::where("idMesa",$idMesa)->first();



            if(isset($mesa))
            {
                $mesa_pedido=new Mesa_Pedido;
                
                $mesa_pedido->idMesa=$idMesa;
                $mesa_pedido->idPedido=$idPedido;
                $mesa->estado="Clientes esperando el pedido";
                //$pedido->estado="En preparacion";
                $pedido->estado="Pendiente";
                $pedido->tiempo_espera=20;
                $pedido->save();
                $mesa_pedido->save();
                $mesa->save();
                
    
                $response->getBody()->write(json_encode("Se ha agregado el pedido correctamente. El codigo del pedido es: $idPedido"));
            }
            else
            {
                $response->getBody()->write(json_encode("No se ha encontrado una mesa con ese id"));
            }
    


        }
        else
        {
            $mensaje=explode('*',$resultado);

            array_pop($mensaje);

            $separado=implode(" || ",$mensaje);

            $response->getBody()->write(json_encode($separado));
        }

        
        //$response->getBody()->write(json_encode($idMesa));

        return  $response;

    }

    public function getPedido($request,$response, $args)
    {
        
        $datos= $request->getQueryParams();

        $idPedido=$datos["idPedido"];
        $idMesa=$datos["idMesa"];

        $pedido=Mesa_Pedido:: join('pedidos', 'pedidos.idPedido', '=', 'mesa_pedido.idPedido')
                ->join('mesas', 'mesas.idMesa', '=', 'mesa_pedido.idMesa')
                ->select('pedidos.nombreCliente as Comensal','pedidos.comida as Comida','pedidos.trago as Trago','pedidos.postre as Postre'
                ,'pedidos.cerveza as Cerveza','pedidos.estado as Estado','pedidos.tiempo_espera as Minutos restantes')
                ->where('pedidos.idPedido',$idPedido)->where('mesas.idMesa',$idMesa)
                ->get();

        if(count($pedido)>0)
        {
            $response->getBody()->write(json_encode($pedido));
        }
        else
        {
            $response->getBody()->write(json_encode("No hay  pedidos que coincidan con los datos ingresados"));
        }

       

        //$response->getBody()->write(json_encode("Hola"));

        return $response;

    }

    public function getPendientes($request,$response, $args)
    {
        
       // $datos= $request->getQueryParams();

        $tipoEmpleado = UsuarioController::ObtenerTipoToken($request->getHeaderLine('token'));
        //$pedidos= Pedido::where('comida_estado','en preparacion')->get();

        //var_dump($tipoEmpleado);

        
        switch ($tipoEmpleado) {
            case 'mozo':
                //$pedidos= Pedido::where('estado','en preparacion')->get();
                $pedidos= Pedido::where('estado','pendiente')->select()->get();
                break;
            
            case 'cocinero':
                //$pedidos= Pedido::where('comida_estado','en preparacion')->get();
                $pedidos= Pedido::where('comida_estado','pendiente')
                ->select('pedidos.idPedido as Id de Pedido','pedidos.nombreCliente as Comensal','pedidos.comida as Comida','pedidos.postre as Postre')->get();
                break;
            
            case 'cervecero':
                //$pedidos= Pedido::where('cerveza_estado','en preparacion')->get();
                $pedidos= Pedido::where('cerveza_estado','pendiente')
                ->select('pedidos.idPedido as Id de Pedido','pedidos.nombreCliente as Comensal','pedidos.cerveza as Cerveza')->get();               
                break;

            case 'bartender':
                //$pedidos= Pedido::where('trago_estado','en preparacion')->get();
                $pedidos= Pedido::where('trago_estado','pendiente')
                ->select('pedidos.idPedido as Id de Pedido','pedidos.nombreCliente as Comensal','pedidos.trago as Trago')->get();
                break;

            case 'socio':
                //$pedidos= Pedido::where('estado','en preparacion')->get();
                $pedidos= Pedido::where('estado','pendiente')->get();
                break;
        }

        /*$idPedido=$datos["idPedido"];
        $idMesa=$datos["idMesa"];

        $pedido=Mesa_Pedido:: join('pedidos', 'pedidos.idPedido', '=', 'mesa_pedido.idPedido')
                ->join('mesas', 'mesas.idMesa', '=', 'mesa_pedido.idMesa')
                ->select('pedidos.nombreCliente as Comensal','pedidos.comida as Comida','pedidos.trago as Trago','pedidos.postre as Postre'
                ,'pedidos.cerveza as Cerveza','pedidos.estado as Estado')
                ->where('pedidos.idPedido',$idPedido)->where('mesas.idMesa',$idMesa)
                ->get();

        if(count($pedido)>0)
        {
            $response->getBody()->write(json_encode($pedido));
        }
        else
        {
            $response->getBody()->write(json_encode("No hay  pedidos que coincidan con los datos ingresados"));
        }

       */

        $response->getBody()->write(json_encode($pedidos));

        return $response;

    }

   public function servirPedido($request,$response, $args)
    {
        $idPedido=$args['idPedido'];
        $tipoEmpleado = UsuarioController::ObtenerTipoToken($request->getHeaderLine('token'));

        $pedido = Pedido::where("idPedido",$idPedido)->first();

        if(isset($pedido))
        {
            if($pedido->estado != "Listo para servir")
            {
                switch ($tipoEmpleado) {
                    case 'mozo':
                         
                        $resultado = PedidoController::VerificarEstados($pedido);
                        if($resultado)
                        {
                            $pedido->estado="Listo para servir";
                           //echo "Entro";
                            //$response->getBody()->write(json_encode($pedido));

                            //Crear funcion que verifique si todos los pedidos ya estan listos y cambiar el estado de la mesa
                            $pedido->save();
                            $estado=PedidoController::CambiarEstadoMesa($idPedido);
                            if($estado)
                            {
                                $response->getBody()->write(json_encode("Todos los comensales estan comiendo"));
                            }
                
                        }
                        else
                        {
                            $response->getBody()->write(json_encode("El pedido aun no esta listo para servirse"));
                        }
                        //$pedidos= Pedido::where('estado','en preparacion')->get();
                        break;
                    
                    case 'cocinero':
                        if($pedido->comida_estado != "Listo" && $pedido->postre_estado != "Listo")
                        {
                            if($pedido->comida !="" )
                                $pedido->comida_estado="Listo";

                            if($pedido->postre !="")
                                $pedido->postre_estado="Listo";

                            $pedido->tiempo_espera=$pedido->tiempo_espera-5;
                            //$pedido->idPedido=$idPedido;
                            $response->getBody()->write(json_encode($pedido));

                            $pedido->save();
                        }
                        else
                        {
                            $response->getBody()->write(json_encode("No hay nada para que usted sirva en este pedido"));
                        }
                        
                        break;
                    
                    case 'cervecero':
                        if($pedido->cerveza_estado != "Listo")
                        {
                            $pedido->cerveza_estado="Listo";
                            $response->getBody()->write(json_encode($pedido));
                            $pedido->save();
                        }
                        else
                        {
                            $response->getBody()->write(json_encode("No hay nada para que usted sirva en este pedido"));
                        }
                        
                        break;
        
                    case 'bartender':
                        if($pedido->trago_estado != "Listo")
                        {
                            $pedido->trago_estado="Listo";
                            $response->getBody()->write(json_encode($pedido));
                            $pedido->save();

                        }
                        else
                        {
                            $response->getBody()->write(json_encode("No hay nada para que usted sirva en este pedido"));
                        }
                        break;
        
                    case 'socio':
                        //$pedidos= Pedido::where('estado','en preparacion')->get();
                        break;
                }
            }
            else
            {
               // $response->getBody()->write(json_encode("Este pedido ya esta servido o esta listo para servir"));

                $mesa=Mesa_Pedido::where('idPedido',$idPedido)->first();
                $mesaGeneral=Mesa::where('idMesa',$mesa->idMesa)->first();

                //$pedidos=PedidoController::CambiarEstadoMesa($idPedido);

                
                // PedidoController::CambiarEstadoMesa($idPedido);
                $mesaGeneral->estado="Clientes pagando";
                $mesaGeneral->save();

                $response->getBody()->write(json_encode($mesaGeneral));


            }
                      
        }
        else
        {
            $response->getBody()->write(json_encode("No existe un pedido con el id indicado"));
        }

        return $response;

    }


    public function prepararPedido($request,$response, $args)
    {
        $idPedido=$args['idPedido'];
        $tipoEmpleado = UsuarioController::ObtenerTipoToken($request->getHeaderLine('token'));

        $pedido = Pedido::where("idPedido",$idPedido)->first();

        if(isset($pedido))
        {
            //if($pedido->estado != "En preparacion")
           // {
                switch ($tipoEmpleado) {
                    case 'mozo':
                         
                        
                    
                    case 'cocinero':
                        if($pedido->comida_estado != "En preparacion" && $pedido->postre_estado != "En preparacion")
                        {
                            if($pedido->comida !="" )
                            {
                                $pedido->comida_estado="En preparacion";
                                $pedido->estado = "En preparacion";
                            }
                                

                            if($pedido->postre !="")
                            {
                                $pedido->postre_estado="En preparacion";
                                $pedido->estado = "En preparacion";
                            }
                                

                            $pedido->tiempo_espera=$pedido->tiempo_espera-5;
                            //$pedido->idPedido=$idPedido;
                            $response->getBody()->write(json_encode($pedido));

                            $pedido->save();
                        }
                        else
                        {
                            $response->getBody()->write(json_encode("No hay nada para que usted prepare en este pedido"));
                        }
                        
                        break;
                    
                    case 'cervecero':
                        if($pedido->cerveza_estado != "En preparacion")
                        {
                            $pedido->cerveza_estado="En preparacion";
                            $pedido->estado = "En preparacion";
                            $response->getBody()->write(json_encode($pedido));
                            $pedido->save();
                        }
                        else
                        {
                            $response->getBody()->write(json_encode("No hay nada para que usted prepare en este pedido"));
                        }
                        
                        break;
        
                    case 'bartender':
                        if($pedido->trago_estado != "En preparacion")
                        {
                            $pedido->trago_estado="En preparacion";
                            $pedido->estado = "En preparacion";
                            $response->getBody()->write(json_encode($pedido));
                            $pedido->save();

                        }
                        else
                        {
                            $response->getBody()->write(json_encode("No hay nada para que usted prepare en este pedido"));
                        }
                        break;
        
                    /*case 'socio':
                        $pedidos= Pedido::where('estado','en preparacion')->get();
                        break;*/
                }
          
                      
        }
        else
        {
            $response->getBody()->write(json_encode("No existe un pedido con el id indicado"));
        }

        return $response;

    }

    static function CerrarMesa($request,$response, $args)
    {
        $idMesa=$args["idMesa"];
        //$response->getBody()->write(json_encode($idMesa));
   
        $mesa=Mesa::where("idMesa",$idMesa)->first();

        //$response->getBody()->write(json_encode($mesa));

        //$mesa=Mesa::find($idMesa);

        if($mesa->estado == "Clientes pagando")
        {
            $mesa->estado = "Cerrada";
            $mesa->save();

            $encuesta=PedidoController::CrearEncuesta();

            $response->getBody()->write(json_encode("Mesa cerrada"));

            $response->getBody()->write(json_encode($encuesta));

        }
        else if($mesa->estado == "Cerrada")
        {
            $response->getBody()->write(json_encode("Esta mesa ya esta cerrada"));
        }
        else if($mesa->estado == "Clientes comiendo")
        {
            $response->getBody()->write(json_encode("Los clientes aun estan comiendo"));
        }
        //$encuesta=PedidoController::CrearEncuesta();
        //$response->getBody()->write(json_encode($encuesta));


        return $response;
    }

    static function generarCodigo()
    {
        $str = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codigo = substr(str_shuffle($str), 0, 5);
        return $codigo;
    }


    static function VerificarEstados($pedido)
    {
        $listo=false;
        if((($pedido->comida != "" && $pedido->comida_estado == "Listo") || ($pedido->comida == "" && $pedido->comida_estado != "Listo"))                
        && (($pedido->cerveza != "" && $pedido->cerveza_estado == "Listo") || ($pedido->cerveza == "" && $pedido->cerveza_estado != "Listo")) 
        && (($pedido->trago != "" && $pedido->trago_estado == "Listo") || ($pedido->trago =="" && $pedido->trago_estado != "Listo" )) 
        && (($pedido->postre != "" && $pedido->postre_estado == "Listo") || ($pedido->postre == "" && $pedido->postre_estado != "Listo")))
        {
            $listo = true;
            //var_dump($pedido);
            //echo "hola";
        }
        
        return $listo;
    }

    static function CambiarEstadoMesa($idPedido)
    {

        $resultado=false;

        $mesa=Mesa_Pedido::where('idPedido',$idPedido)->first();
       

    
        $pedidos=Mesa_Pedido:: join('pedidos', 'pedidos.idPedido', '=', 'mesa_pedido.idPedido')
                ->join('mesas', 'mesas.idMesa', '=', 'mesa_pedido.idMesa')
                ->select('pedidos.nombreCliente as Comensal','pedidos.comida as Comida','pedidos.trago as Trago','pedidos.postre as Postre'
                ,'pedidos.cerveza as Cerveza','pedidos.estado as Estado')//,'pedidos.tiempo_espera as Minutos restantes')                
                ->where('mesas.idMesa',$mesa->idMesa)
                ->get();

        $pedidosListos= Mesa_Pedido:: join('pedidos', 'pedidos.idPedido', '=', 'mesa_pedido.idPedido')
                ->join('mesas', 'mesas.idMesa', '=', 'mesa_pedido.idMesa')
                ->select('pedidos.nombreCliente as Comensal','pedidos.comida as Comida','pedidos.trago as Trago','pedidos.postre as Postre'
                ,'pedidos.cerveza as Cerveza','pedidos.estado as Estado')//,'pedidos.tiempo_espera as Minutos restantes')       
                ->where('pedidos.estado',"listo para servir")->where('mesas.idMesa',$mesa->idMesa)
                ->get();

      

        $mesaGeneral=Mesa::where('idMesa',$mesa->idMesa)->first();

        
       
        if(count($pedidos)==count($pedidosListos))
        {
            $resultado=true;
            $mesaGeneral->estado="Clientes comiendo";
           // echo json_encode("Todos los comensales estan comiedo");
            $mesaGeneral->save();
            

        }

        return $resultado;
    }


    


    static function VerificarProductos($pedido)
    {

        $mensaje ="";

        if( $pedido->comida != "")
        {
            $comida=Comida::where("nombre", $pedido->comida)->first();

            if(!isset($comida))
            {
                $mensaje = $mensaje."$pedido->comida no se encuentra en el menu"."*"; 
            }
            
        }
        
              
        if($pedido->cerveza != "")
        {
            $cerveza=Cerveza::where("nombre", $pedido->cerveza)->first();

            if(!isset($cerveza))
            {
                $mensaje =$mensaje."$pedido->cerveza no se encuentra en el menu"."*"; 
            }
           
        }
       
        if($pedido->postre != "")
        {
            $postre=Postre::where("nombre", $pedido->postre)->first();
            if(!isset($postre))
            {
                $mensaje =$mensaje."$pedido->postre no se encuentra en el menu"."*"; 
            }
           
        }

        
        if($pedido->trago != "")
        {
            $trago=Trago::where("nombre",$pedido->trago)->first();
            if(!isset($trago))
            {
                $mensaje =$mensaje."$pedido->trago no se encuentra en el menu"."*"; 
            }
            
        }
       
        

        //$response->getBody()->write(json_encode("Hola mundo"));

        return  $mensaje;
    }

    static function CrearIdMesa()
    {

        do
        {
            $idMesa = PedidoController::generarCodigo();

            $mesa = Mesa::where("idMesa",$idMesa)->first();

        } while(isset($mesa));
 
        return $idMesa;
        
    }

    static function CrearIdPedido()
    {
        do
        {
            $idPedido = PedidoController::generarCodigo();

            $pedido = Pedido::where("idPedido",$idPedido)->first();

        } while(isset($pedido));
 
        return $idPedido;
        
    }


    static function CrearEncuesta()
    {
        $puntajeMesa=rand(1,10);
        $puntajeMozo=rand(1,10);
        $puntajeRestaurante=rand(1,10);
        $puntajeCocinero=rand(1,10);

        $encuesta=array("mesa"=>$puntajeMesa,
                        "mozo"=>$puntajeMozo,
                        "cocinero"=>$puntajeCocinero,
                        "restaurante"=>$puntajeRestaurante);

       return $encuesta; 
    }

   

    

}
