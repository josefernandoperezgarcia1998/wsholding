<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('UbicacionMaps'))
{
  function UbicacionMaps($latitud ,$longitud )
  {
    $direcciones = array(
      'Calle' => 'route',
      'colonia' => 'sublocality_level_1',
      'estado' => 'administrative_area_level_1',
      'municipio' => 'administrative_area_level_3',
      'codigoPostal' => 'postal_code',
      'municipio2' => 'locality',
    );
    $key = decodeKey();
    $CI =& get_instance();
    $Servciosmodel =& get_instance();
    $CI->load->model('Request_model');
    $Servciosmodel->load->model('serviciosModel');
    $geoURL = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitud},{$longitud}&sensor=true&key={$key}";
    $respuesta_services = $CI->Request_model->get($geoURL);
    if ($respuesta_services['status'] == 200)
    {
      if ($respuesta_services['result']->status == 'OK')
      {
        $respuesta_datos = buscarDatoDireccion($respuesta_services['result'],$direcciones);
        $Estado = $respuesta_datos[2];
        $Municipio = $respuesta_datos[4];
        $estadoCast = $Servciosmodel->serviciosModel->switchEstado(strtoupper(sanear_string($respuesta_datos[2])));
        $idEstado = $Servciosmodel->serviciosModel->empatarEstado($estadoCast);
        if ( empty($idEstado) )
        {
          $resp = array('status' => 0, 'response' => "El Estado no es valido");
        }
        else
        {
          $respuesta_datos[6] = $idEstado[0]['cve_edo'];
          $idMunicipio = $Servciosmodel->serviciosModel->empatarMunicipio($idEstado[0]['cve_edo'], strtoupper(sanear_string($Municipio)));
          $respuesta_datos[7] = $idMunicipio;
          $resp = array('status' => 1, 'response' => $respuesta_datos);
        }
      }
      else
      {
        $respuesta_datos = array(
          'routeChange',
          'colChange',
          'CDMX',
          'MIGUEL HIDALGO',
          '11000',
          '0'
        );
        $estadoCast = $Servciosmodel->serviciosModel->switchEstado($respuesta_datos[2]);
        $idEstado = $Servciosmodel->serviciosModel->empatarEstado($estadoCast);
        $idMunicipio = $Servciosmodel->serviciosModel->empatarMunicipio($idEstado[0]['cve_edo'], strtoupper(sanear_string($respuesta_datos[3])));
        $respuesta_datos[6] = $idEstado[0]['cve_edo'];
        $respuesta_datos[7] = $idMunicipio;
        $resp = array('status' => 1, 'response' => $respuesta_datos);
      }
    }
    else
    {
      $resp = array('status' => 0, 'response' => "El servicios de Mapas de Google no se encuentra disponibles");
    }
    return $resp;
  }

  function GetDrivingDistance($latOrigen, $latDestino, $longOrigen, $longDestino)
  {
    $key = decodeKey();
    $CI =& get_instance();
    $CI->load->model('Request_model');
    $time = round(microtime(true) * 1000);
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$latOrigen.",".$longOrigen."&destinations=".$latDestino.",".$longDestino."&sensor=false&departure_time=$time&mode=driving&traffic_model=optimistic&language=pl-PL&key=$key";
    $respuesta_services = $CI->Request_model->getarray($url);
    if ($respuesta_services['status'] == 200)
    {
      if ($respuesta_services['result']['status'] == 'OK')
      {
        $response_a = $respuesta_services['result'];
        $dist = $response_a['rows'][0]['elements'][0]['distance']['text'];
        $val = ($response_a['rows'][0]['elements'][0]['distance']['value'] >= 0 && $response_a['rows'][0]['elements'][0]['distance']['value'] <= 1000) ? 1 : round(($response_a['rows'][0]['elements'][0]['distance']['value']/1000),1);
        $time = $response_a['rows'][0]['elements'][0]['duration']['text'];
        $respuesta_datos = array('distance' => $dist, 'km'=>$val, 'time' => $time);
        $resp = array('status' => 1, 'response' => $respuesta_datos);
      }
      else
      {
        $respuesta_datos = array('distance' => 1, 'km' => 1, 'time' => 1);
        $resp = array('status' => 1, 'response' => $respuesta_datos);
      }
    }
    else
    {
      $resp = array('status' => 0, 'response' => "El servicios de Mapas de Google no se encuentra disponibles");
    }

    return $resp;
  }

  function buscarDatoDireccion($array, $Arreglo_direccion)
  {
    $direccion = array();
    $direcciones = array();
    foreach ($Arreglo_direccion as $indice => $campoApiMaps)
    {
      $d=0;
      foreach ($array->results as $key => $value)
      {
        $i=0;
        $address=$value->address_components;
        foreach ($address as $key => $val)
        {
          $types=$val->types;
          foreach ($types as $key => $tip)
          {
            // Debugger($val);
            if ($tip==$campoApiMaps && !isset($direcciones[$indice]))
            {
              $direcciones[$indice] = 1;
              $dato=$array->results[$d]->address_components[$i]->long_name;
              $direccion[$indice] = $dato;
              // array_push($direccion,$dato);
            }
          }
          $i++;
        }
        $d++;
      }
    }

    if (!isset($direccion['municipio']))
    {
      if (!isset($direccion['municipio2']))
      {
        $direccion['municipio']='*'.strtolower($direccion['estado']);
      }
      else {
        $direccion['municipio'] = $direccion['municipio2'];
      }
    }
    return array(
      $direccion['Calle'],
      $direccion['colonia'],
      $direccion['estado'],
      $direccion['codigoPostal'],
      $direccion['municipio'],
      $direccionApiOrigen=$array->results[0]->formatted_address
    );
  }

  function decodeKey()
  {
    $key = '!QUl6YVN5Qlg5blk1djYzd2VqMmpDSGo5VjVKc2ZMRVJLaTd6YWs4!';
    return base64_decode(trim($key,'!'));
  }

  function sanear_string($string)
  {

    $string = trim($string);

    $string = str_replace(
      array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
      array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
      $string
    );

    $string = str_replace(
      array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
      array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
      $string
    );

    $string = str_replace(
      array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
      array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
      $string
    );

    $string = str_replace(
      array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
      array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
      $string
    );

    $string = str_replace(
      array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
      array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
      $string
    );

    $string = str_replace(
      array('ñ', 'Ñ', 'ç', 'Ç'),
      array('n', 'N', 'c', 'C',),
      $string
    );

    // Desinfectar e imprimir cadena de comentarios
    $string= filter_var($string , FILTER_SANITIZE_STRING);

    return $string;
  }
}
