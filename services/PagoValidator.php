<?php

class PagoValidator
{
    public static function validarToken($data)
    {

     
        $requiredToken = filter_var($_ENV['REQUIRED_TOKEN'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
        if (!$requiredToken) return true;



        $idEmpresa = $data['id_empresa']           ;
        $titulo = $data['producto_titulo']          ;
        $descripcion = $data['producto_descripcion']         ;
        $precio = $data['producto_precio']          ;
        $duracion = $data['producto_duracion']         ;

        $tokenEsperado = generateSha512($idEmpresa, $titulo, $descripcion, $precio, $duracion);


        if (empty($data['token'])) {
            return false; // o lanzar excepción, o registrar log
        }

        $token = $data['token'];



        return $token === $tokenEsperado;
    }

    public static function validarEstado($status)
    {
        return $status === "COMPLETED";
    }
}
