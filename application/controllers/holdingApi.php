<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
date_default_timezone_set('America/Mexico_City');
class holdingApi extends REST_Controller
{
  public function __construct(){
    parent::__construct();
    $this->load->model('requestModel');
    $this->load->model('holdingApiModel');
    $this->load->helper(array('form', 'url', 'debugger_helper', 'mapas_helper'));
    $this->load->library('form_validation');
  }
  private $rulesConsultaCliente=[
      [
        'field' => 'numEmpleado',
        'rules' => 'required',
        'errors' => [
          'required' => '%s es requerido'
        ]
      ]
  ];
  private $rulesInsertaCliente=[
    [
      'field' => 'numEmpleado',
      'rules' => 'required',
      'errors' => [
        'required' => '%s es requerido'
      ]
    ],
    [
      'field' => 'nombre',
      'rules' => 'required',
      'errors' => [
        'required' => '%s es requerido'
      ]
    ],
    [
      'field' => 'apellidoPaterno',
      'rules' => 'required',
      'errors' => [
        'required' => '%s es requerido'
      ]
    ],
    [
      'field' => 'apellidoMaterno',
      'rules' => 'required',
      'errors' => [
        'required' => '%s es requerido'
      ]
    ],
    [
      'field' => 'direccion',
      'rules' => 'required',
      'errors' => [
        'required' => '%s es requerido'
      ]
    ],
    [
      'field' => 'numEmpleado',
      'rules' => 'required',
      'errors' => [
        'required' => '%s es requerido'
      ]
    ],
    [
      'field' => 'modelo',
      'rules' => 'required',
      'errors' => [
          'required' => '%s es requerido'
      ]
    ],
    [
      'field' => 'numCertificado',
      'rules' => 'required',
      'errors' => [
        'required' => '%s es requerido'
      ]
    ]
  ];
  private $rulesGenerarToken=[
      [
        'field' => 'usuario',
        'rules' => 'required',
        'errors' => [
          'required' => '%s es requerido'
        ]
      ],
      [
        'field' => 'pass',
        'rules' => 'required',
        'errors' => [
          'required' => '%s es requerido'
        ]
      ]
  ];
  private $rulesStatusCliente=[
      [
        'field' => 'status',
        'rules' => 'required',
        'errors' => [
          'required' => '%s es requerido'
        ]
      ]
  ];
    public function generarToken_post(){
      $headers = $this->input->request_headers();
      $data = json_decode($this->input->raw_input_stream, true);
      if (!empty($data)) {
          $this->form_validation->set_data($data);
          $this->form_validation->set_rules($this->rulesGenerarToken);
          if (!$this->form_validation->run()) {
            $resp = array(
              'respuesta' => array('status' => 0, 'response' => $this->get_string_between($this->form_validation->error_string(), '<p>', '</p>')),
              'status' => 428
            );
            goto f;
          }else{
            if($data['usuario']==$this->config->item('usuarioServicio') and password_verify($data['pass'], $this->config->item('passHash')) ){
              $tokenData = array();
              $tokenData['usuario'] = $data['usuario']; 
              $tokenData['pass'] = $data['pass']; 
              $tokenData['timestamp'] = time();
              $tokenString=$output['token'] = AUTHORIZATION::generateToken($tokenData);
              $resp = array(
                'respuesta' => array('status' => 1, 'response' => array('token'=>$tokenString)),
                'status' => 200
              );
              goto f;
            }else{
              $resp = array(
                'respuesta' => array('status' => 0, 'response' => "Credenciales incorrectas"),
                'status' => 401
              );
              goto f;
            }
          }
      }else {
        $resp = array(
          'respuesta' => array('status' => 0, 'response' => "Arreglo vacio"),
          'status' => 401
        );
        goto f;
      }
      f:
      $this->response($resp['respuesta'], $resp['status']);
    }
    public function validarToken(){
      $response = array();
      $headers = $this->input->request_headers();
      if(isset($headers['Authorization'])){
        $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);
        if ($decodedToken) {
          if (password_verify($decodedToken->pass, $this->config->item('passHash')) and ($decodedToken->usuario==$this->config->item('usuarioServicio')) ) {
            return $decodedToken;
          }else{
            return false;
          }
        } else {
          return false;
        }
      }else{
        return false;
      }
    
      
    }
    public function buscarCliente_post(){
      $headers = $this->input->request_headers();
      $data = json_decode($this->input->raw_input_stream, true);
      $token = $this->validarToken($headers);
      if ($token) {
        if (!empty($data)) {
          $this->form_validation->set_data($data);
          $this->form_validation->set_rules($this->rulesConsultaCliente);
          if (!$this->form_validation->run()) {
            $resp = array(
              'respuesta' => array('status' => 0, 'response' => $this->get_string_between($this->form_validation->error_string(), '<p>', '</p>')),
              'status' => 428
            );
            goto f;
          } else {
            $numEmpleado=$data['numEmpleado'];
            $response = $this->holdingApiModel->consultaHolding($numEmpleado);
            $resp = array(
              'respuesta' => array('status' => $response['status'], 'msg' => $response['msg'], 'response'=>$response['response']),
              'status' => 200
            );
            goto f;
          }
        } else {
          $resp = array(
            'respuesta' => array('status' => 0, 'response' => "Arreglo Vacio"),
            'status' => 401
          );
          goto f;
        }
      } else {
        $resp = array(
          'respuesta' => array('status' => 0, 'response' => "Acesos incorrectos"),
          'status' => 401
        );
        goto f;
      }
      f:
      $this->response($resp['respuesta'], $resp['status']);
    }
    public function insertarCliente_post(){
      $headers = $this->input->request_headers();
      $data = json_decode($this->input->raw_input_stream, true);
      $token = $this->validarToken($headers);
      if ($token) {
        if (!empty($data)) {
          $this->form_validation->set_data($data);
          $this->form_validation->set_rules($this->rulesInsertaCliente);
          if (!$this->form_validation->run()) {
            $resp = array(
              'respuesta' => array('status' => 0, 'response' => $this->get_string_between($this->form_validation->error_string(), '<p>', '</p>')),
              'status' => 428
            );
            goto f;
          } else {
            $today = date('d-m-Y');
            $dataInsert=array(
              "nombre"=>$data["nombre"],
              "apellidoPaterno"=>$data["apellidoPaterno"],
              "apellidoMaterno"=>$data["apellidoMaterno"],
              "direccion"=>$data["direccion"],
              "numCuenta"=>$data["numCuenta"],
              "numEmpleado"=>$data["numEmpleado"],
              "modelo"=>$data["modelo"],
              "numCertificado"=>$data["numCertificado"],
              "fechaAlta"=>$today,
              "status"=>1
            );
            $response=$this->holdingApiModel->insertarCliente($dataInsert);
            $resp = array(
              'respuesta' => array('status' => $response['status'], 'msg' => $response['msg'], 'response'=>$response['response']),
              'status' => 200
            );
            goto f;
          }
        } else {
          $resp = array(
            'respuesta' => array('status' => 0, 'response' => "Arreglo Vacio"),
            'status' => 401
          );
          goto f;
        }
      } else {
        $resp = array(
          'respuesta' => array('status' => 0, 'response' => "Acesos incorrectos"),
          'status' => 401
        );
        goto f;
      }
      f:
      $this->response($resp['respuesta'], $resp['status']);
    }
    public function updateCliente_post(){
      $headers = $this->input->request_headers();
      $data = json_decode($this->input->raw_input_stream, true);
      $token = $this->validarToken($headers);
      if ($token) {
        if (!empty($data)) {
          $this->form_validation->set_data($data);
          $this->form_validation->set_rules($this->rulesInsertaCliente);
          if (!$this->form_validation->run()) {
            $resp = array(
              'respuesta' => array('status' => 0, 'response' => $this->get_string_between($this->form_validation->error_string(), '<p>', '</p>')),
              'status' => 428
            );
            goto f;
          } else {
            $today = date('d-m-Y');
            $dataInsert=array(
              "nombre"=>$data["nombre"],
              "apellidoPaterno"=>$data["apellidoPaterno"],
              "apellidoMaterno"=>$data["apellidoMaterno"],
              "direccion"=>$data["direccion"],
              "numCuenta"=>$data["numCuenta"],
              "numEmpleado"=>$data["numEmpleado"],
              "modelo"=>$data["modelo"],
              "numCertificado"=>$data["numCertificado"],
              "fechaActualizacion"=>$today
            );
            $response=$this->holdingApiModel->actualizaCliente($dataInsert);
            $resp = array(
              'respuesta' => array('status' => $response['status'], 'msg' => $response['msg'], 'response'=>$response['response']),
              'status' => 200
            );
            goto f;
          }
        } else {
          $resp = array(
            'respuesta' => array('status' => 0, 'response' => "Arreglo Vacio"),
            'status' => 401
          );
          goto f;
        }
      } else {
        $resp = array(
          'respuesta' => array('status' => 0, 'response' => "Acesos incorrectos"),
          'status' => 401
        );
        goto f;
      }
      f:
      $this->response($resp['respuesta'], $resp['status']);
    }
    public function cancelarCliente_post(){
      $headers = $this->input->request_headers();
      $data = json_decode($this->input->raw_input_stream, true);
      $token = $this->validarToken($headers);
      if ($token) {
        if (!empty($data)) {
          $this->form_validation->set_data($data);
          $this->form_validation->set_rules($this->rulesConsultaCliente);
          if (!$this->form_validation->run()) {
            $resp = array(
              'respuesta' => array('status' => 0, 'response' => $this->get_string_between($this->form_validation->error_string(), '<p>', '</p>')),
              'status' => 428
            );
            goto f;
          } else {
            $numEmpleado=$data["numEmpleado"];
            $response=$this->holdingApiModel->bajaCliente($numEmpleado);
            $resp = array(
              'respuesta' => array('status' => $response['status'], 'msg' => $response['msg'], 'response'=>$response['response']),
              'status' => 200
            );
            goto f;
          }
        } else {
          $resp = array(
            'respuesta' => array('status' => 0, 'response' => "Arreglo Vacio."),
            'status' => 401
          );
          goto f;
        }
      } else {
        $resp = array(
          'respuesta' => array('status' => 0, 'response' => "Acesos incorrectos"),
          'status' => 401
        );
        goto f;
      }
      f:
      $this->response($resp['respuesta'], $resp['status']);
    }

    public function statusCliente_post(){
      $headers = $this->input->request_headers();
      $data = json_decode($this->input->raw_input_stream, true);
      $token = $this->validarToken($headers);
      if ($token) {
        if (!empty($data)) {
          $this->form_validation->set_data($data);
          $this->form_validation->set_rules($this->rulesStatusCliente);
          if (!$this->form_validation->run()) {
            $resp = array(
              'respuesta' => array('status' => 0, 'response' => $this->get_string_between($this->form_validation->error_string(), '<p>', '</p>')),
              'status' => 428
            );
            goto f;
          } else {
            $dataUpdate=array(
              "numEmpleado"=>$data["numEmpleado"],
              "status"=>$data["status"]
            );
            $response=$this->holdingApiModel->statusCliente($dataUpdate);
            $resp = array(
              'respuesta' => array('status' => $response['status'], 'msg' => $response['msg'], 'response'=>$response['response']),
              'status' => 200
            );
            goto f;
          }
        } else {
          $resp = array(
            'respuesta' => array('status' => 0, 'response' => "Arreglo Vacio."),
            'status' => 401
          );
          goto f;
        }
      } else {
        $resp = array(
          'respuesta' => array('status' => 0, 'response' => "Acesos incorrectos"),
          'status' => 401
        );
        goto f;
      }
      f:
      $this->response($resp['respuesta'], $resp['status']);
    }

  private function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0)
      return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
  }
  private function sanear_string($string){

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
    $string = filter_var($string, FILTER_SANITIZE_STRING);

    return $string;
  }




}
