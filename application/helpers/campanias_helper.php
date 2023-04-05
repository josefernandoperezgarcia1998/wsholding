<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('validaCampania'))
{
  function validaCampania($TokenCampania)
  {
    $CI =& get_instance();
    $Servciosmodel =& get_instance();
    $CI->load->model('Request_model');
    $Servciosmodel->load->model('serviciosModel');
    $estadoCast = $Servciosmodel->serviciosModel->serviciosDatosValidaToken($TokenCampania);
    return $estadoCast;
  }
}
