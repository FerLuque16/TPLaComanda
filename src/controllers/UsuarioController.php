<?php

namespace App\Controllers;

Use App\Models\User;
use \Firebase\JWT\JWT;


class UsuarioController
{
    public function getAll($request,$response, $args)
    {
       /* $rta= User::get();

        $response->getBody()->write(json_encode($rta));*/

        $rta = User::get();

        //var_dump($rta);

        $response->getBody()->write(json_encode($rta));

        return $response;
    
    }

    public function add($request,$response,$args)
    {
        $user = new User;

        $datos=$request->getParsedBody();

        $user->nombre = $datos["nombre"];
        $user->clave = $datos["clave"];        
        $user->tipo = $datos["tipo"];

        $registrado = User::where('nombre',$user->nombre)->first();
        if(UsuarioController::verificarTipo($user->tipo))
        {
            if(!strpos($user->nombre,' '))
            {
                if(strlen($user->clave)>=4)
                {
                    if(isset($registrado))
                    {
                        $response->getBody()->write(json_encode("Ya existe un usuario con ese nombre. Por favor ingrese datos distintos"));
                    }
                    else
                    {
                        $user->save();           
                        $response->getBody()->write(json_encode("Usuario guardado correctamente"));
                    }
                }
                else
                {
                    $response->getBody()->write(json_encode("La clave debe tener al menos 4 caracteres"));
                }
            }
            else
            {
                $response->getBody()->write(json_encode("El nombre no debe contener espacios. Ingrese los datos nuevamente"));
            } 
        }
        else
        {
            $response->getBody()->write(json_encode("El tipo que ha ingresado no es valido"));
        }
     
        return $response;
    }

    public function login($request, $response, $args)
    {
        $datos = $request->getParsedBody();

        $token=UsuarioController::BuscarUsuario($datos['clave'],$datos['nombre']);

        if($token !=false)
        {
            $response->getBody()->write(json_encode($token));
            
        }
        else
        {
            $response->getBody()->write(json_encode("Usuario no encontrado"));
        }
            
            return $response;
        
    }

    static function BuscarUsuario($clave,$nombre)
     {           
        $payload=array();

        $esCorrecto=false;

        $user = User::where('clave',$clave)->where('nombre', $nombre)->first();

                if($user != null)
                {
                   

                    $payload=array(
                    
                        "id"=>$user->idUsuario,
                        "nombre"=>$nombre,
                        "clave"=>$clave,                      
                        "tipo"=>$user->tipo                   

                    );
                    
                    $esCorrecto=JWT::encode($payload,'tpComanda');                    
                }
      


        return $esCorrecto;
        
     }

     static function verificarTipo($tipo)
     {
        $esValido = true;

        if($tipo != "mozo" && $tipo != "cervecero" && $tipo != "bartender" && $tipo != "cocinero" && $tipo != "socio" )
            $esValido = false;

        return $esValido;

     }

     public static function ObtenerTipoToken($token)
    {
        try 
        {
            $payload = JWT::decode($token, "tpComanda", array('HS256'));
            
            foreach ($payload as $key => $value) 
            {
                if($key == 'tipo')
                {
                    return $value;
                }
            }
        } catch (\Throwable $th)
        {
            echo 'Excepcion:' . $th->getMessage();
        }
    }

}