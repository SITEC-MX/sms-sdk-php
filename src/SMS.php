<?php
/**
 * Sistemas Especializados e Innovaci�n Tecnol�gica, SA de CV
 * SMS - Sistema de env�o de SMS
 *
 * v.1.0.0.0 - 2016-11-23
 */
namespace Mpsoft\SMS;

class SMS
{
    private $servidor = "sms.svc-sitec.com";
    private $usuario;
    private $contrasena;

    public function __construct($usuario, $contrasena)
    {
        $this->usuario = $usuario;
        $this->contrasena = $contrasena;
    }

    public function Enviar($destinatario, $mensaje, $remitente = NULL)
    {
        $estado = array("estado" => 0, "mensaje" => "No inicializado");

        if (strlen($this->usuario) == 0 || strlen($this->contrasena) == 0) // Si no se proporciona el usuario o contrase�a
        {
            $estado["estado"] = -1;
            $estado["mensaje"] = "No se proporcion� el nombre de usuario o contrase�a.";
        }
        else // Si se proporciona el usuario y contrase�a
        {
            $destinatario = str_replace(array(" ", "(", ")", "-"), "", $destinatario); // Quitamos caracteres no necesarios

            if (!preg_match("/^\+{0,1}[0-9]{10,13}$/", $destinatario)) // Si el n�mero del destinatario no es v�lido
            {
                $estado["estado"] = -2;
                $estado["mensaje"] = "El destinatario del SMS tiene un formato inv�lido.";
            }
            else // Si el n�mero del destinatario es v�lido
            {
                if(!$dispositivoValido = $remitente == null) // Si se especifica un remitente
                {
                    $remitente = str_replace(array(" ", "(", ")", "-"), "", $remitente); // Quitamos caracteres no necesarios

                    if (preg_match("/^\+{0,1}[0-9]{10,13}$/", $remitente)) // Si el n�mero del remitente es v�lido
                    {
                        $dispositivoValido = true;
                    }
                }

                if (!$dispositivoValido) // Si el remitente no es v�lido
                {
                    $estado["estado"] = -3;
                    $estado["mensaje"] = "El remitente del SMS tiene un formato inv�lido.";
                }
                else // Si el remitente es v�lido
                {
                    $api_url = "https://{$this->servidor}/enviar";

                    $parametros = array("usuario"=>$this->usuario, "contrasena"=>$this->contrasena, "destinatario"=>$destinatario, "mensaje"=>$mensaje);
                    if($remitente) // Si se especifica el remitente del SMS
                    {
                        $parametros["remitente"]=$remitente;
                    }

                    $options = array(
                        "http" => array(
                            "header" => "Content-type: application/x-www-form-urlencoded\r\n",
                            "method" => "POST",
                            "content" => http_build_query($parametros)
                            )
                        );

                    $context  = stream_context_create($options);
                    if($respuesta = file_get_contents($api_url, false, $context)) // Si la respuesta se obtienen correctamente
                    {
                        $estado = json_decode($respuesta, true);

                        if (!is_array($estado)) // Si la respuesta del servidor no es válida
                        {
                            $estado = array("estado" => -4, "mensaje" => "Indeterminado", "debug"=>$respuesta);
                        }
                    }
                    else // Error al obtener la respuesta
                    {
                        $estado["estado"] = -5;
                        $estado["mensaje"] = "Sin conexi�n con el servidor de env�o de SMS.";
                    }
                }
            }
        }

        return $estado;
    }
}