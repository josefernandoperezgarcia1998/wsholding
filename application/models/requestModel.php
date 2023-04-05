<?php
defined('BASEPATH') or exit('No direct script access allowed');

class requestModel extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

public function consultaHolding(){
    try {
        $bdProteccion = $this->load->database('default', TRUE);
        $query="SELECT * FROM basesClientes";
        $data = $bdProteccion->query($query);
        $dataServicio= $data->result_array();
        if(is_array($dataServicio) and count($dataServicio)>0){
            $resp = array('status' => 1, 'response' => $dataServicio);
            return  $resp;
        }else{
            $resp = array('status' => 0, 'response' => 'No existen datos del servicio');
            return  $resp;
        }
    } catch (Exception $ex) {
        $resp = array('status' => 0, 'response' => "Hubo un problema con la consulta de datos");
        return $resp;
    }
    }

    public function get($url)
    {
        $ch = curl_init($url);

        //Set options to follow redirects and return output
        //as a string.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        //Execute the request.
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        $data = array(
            'status' => $info['http_code'],
            'result' => json_decode($result),
        );

        return $data;
    }

    public function getarray($url)
    {
        $ch = curl_init($url);

        //Set options to follow redirects and return output
        //as a string.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        //Execute the request.
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        $data = array(
            'status' => $info['http_code'],
            'result' => json_decode($result, true),
        );

        return $data;
    }

    public function post($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $server_output = curl_exec($ch);

        curl_close($ch);

        //error_log('nestor: '.print_r($server_output, true));

        return json_decode($server_output);
    }

    public function post_json($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        $server_output = curl_exec($ch);

        curl_close($ch);

        //error_log('nestor: '.print_r($server_output, true));

        return json_decode($server_output, 1);
    }

    public function post_tele($url, $data, $key)
    {
        date_default_timezone_set('America/Mexico_City');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = [
            'apiKey:' . $key
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $server_output = curl_exec($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);
        return json_decode($server_output);
    }


    public function post_custom($url, $data, $key)
    {
        // Debugger($key);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $headers = [
            $key,
            'Content-Type: application/json'

        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $server_output = curl_exec($ch);

        curl_close($ch);

        return json_decode($server_output, true);
        // return false;
    }
    public function postSinJson($url, $data, $key)
    {
        // Debugger($key);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $headers = [
            $key

        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $server_output = curl_exec($ch);

        curl_close($ch);

        return json_decode($server_output, true);
        // return false;
    }

    public function get_custom($url, $data, $key)
    {
        $ch = curl_init($url);

        //Set options to follow redirects and return output
        //as a string.
        $headers = [
            $key
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        //Execute the request.
        $result = curl_exec($ch);

        //        error_log(print_r($url, true));
        //        error_log(print_r($result, true));

        return json_decode($result);
    }
}
