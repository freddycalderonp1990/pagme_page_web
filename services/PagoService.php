<?php

class PagoService
{

    public static function limpiarTexto($texto)
    {
        return trim(preg_replace('/[^\p{L}\p{N}\p{P}\p{Z}]/u', '', $texto));
    }

    public static function guardarEnApi($data)
    {


        $payload = [
            "id_empresa"           => $data["id_empresa"],
            "order_id"             => $data["order_id"],
            "status"               => $data["status"],

            "producto_titulo"      => PagoService::limpiarTexto($data["producto_titulo"]),
            "producto_descripcion" => PagoService::limpiarTexto($data["producto_descripcion"]),
            "producto_precio"      => $data["producto_precio"],

            "moneda"               => $data["moneda"],

            "payer_id"             => $data["payer_id"],
            "payer_nombre"         => $data["payer_nombre"],
            "payer_email"          => $data["payer_email"],

            "fecha_pago"           => $data["fecha_pago"],
            "estado_interno"       => $data["estado_interno"],
            "id_vendedor"          => $data["id_vendedor"],
            "num_dias"              => $data["num_dias"],

            "tipo_plan_cupo"              => $data["tipoPlanCupon"],
            "id_plan_cupo"              => $data["idPlanCupon"],

            "ip"                   => $data["ip"]
        ];





        $apiUrl = $_ENV["API_URL_SAVE_PAGO"];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$httpCode, $response];
    }
}
