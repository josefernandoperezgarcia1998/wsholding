<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('Debugger'))
{
  function Debugger($var,$tipo = 0)
  {
    if ($tipo == 1)
    {
      echo "<pre>";
      var_dump($var);
      echo "</pre>";
      die();
    }
    else
    {
      echo "<pre>";
      print_r($var);
      echo "</pre>";
      die();
    }

  }
}
