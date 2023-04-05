<?php
defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set('America/Mexico_City');
class holdingApiModel extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function consultaHolding($numEmpleado){
        try {
            $bdProventel = $this->load->database('default', TRUE);
            $query="SELECT nombres, apPaterno, apMaterno, fechaNacimiento, ciudad, estado, genero, convert(varchar(15),fechaAlta,103) as fechaAlta, case when status = 1 then 'ACTIVO' when status = 2 then 'SUSPENDIDO' when status = 0 then 'CANCELADO' else 'NO EXISTE' end as status 
            FROM clientesHolding WHERE numEmpleado= '{$numEmpleado}'";
            $data = $bdProventel->query($query);
            $dataServicio= $data->result_array();
            if(is_array($dataServicio) and count($dataServicio)>0){

                $resp = array('status' => 1, 'msg'=>'Consulta correcta', 'response' => $dataServicio[0]);
                return  $resp;
            }else{
                $updateData = array("numEmpleado" => $numEmpleado);
                $resp = array('status' => 0,  'msg'=>'No existe el cliente',  'response' => $updateData);
                return  $resp;
            }
        } catch (Exception $ex) {
            $resp = array('status' => 0, 'msg'=>'Error en la consulta', 'response' => "");
            return $resp;
        }
    }
    
    public function insertarCliente($dataInsert){
        try {
            $IMEI=$dataInsert['IMEI'];
            $bdProventel = $this->load->database('default', TRUE);
            $query="SELECT * FROM clientesHolding WHERE IMEI= '{$IMEI}' and status = 1";
            $data = $bdProventel->query($query);
            $dataServicio= $data->result_array();
            $response= $dataInsert;
            if(is_array($dataServicio) and count($dataServicio)>0){
                $resp = array('status' => 0, 'msg'=>'Cliente ya existe con ese numero de IMEI', 'response' =>$IMEI);
                return  $resp;
            }else{
                $response= $bdProventel->insert('clientesHolding', $dataInsert);
                /* $bdProventel->set('fechaAlta', '09/03/2022');
                $bdProventel->where('numCuenta', $numCuenta);
                $bdProventel->update('clientesHolding');*/
                $resp = array('status' => 1,  'msg'=>'Registros exitoso',  'response' => $dataInsert);
                return  $resp;
            }
        } catch (Exception $ex) {
            $resp = array('status' => 0, 'msg'=>'Error en la consulta', 'response' => "");
            return $resp;
        }
    }

    public function actualizaCliente($dataUpdate){
        try {
            $IMEI=$dataUpdate['IMEI'];
            $bdProventel = $this->load->database('default', TRUE);
            $query="SELECT *
            FROM clientesHolding WHERE IMEI= '{$IMEI}' and status = 1";
            $data = $bdProventel->query($query);
            $dataServicio= $data->result_array();
            if(is_array($dataServicio) and count($dataServicio)>0){
                $updateData = array("IMEI" => $IMEI, "fechaActualizacion" => $dataUpdate['fechaActualizacion']);
                $bdProventel->where('IMEI', $IMEI);
                $bdProventel->update('clientesHolding', $dataUpdate);
                $resp = array('status' => 1,  'msg'=>'Actualización exitosa',  'response' => $updateData);
                return  $resp;
            }else{
                $updateData = array("IMEI" => $IMEI);
                $resp = array('status' => 1,  'msg'=>'Cliente no existe',  'response' => $updateData);
                return  $resp;
            }
        } catch (Exception $ex) {
            $resp = array('status' => 0, 'msg'=>'Error en la consulta', 'response' => "");
            return $resp;
        }
    }

    public function bajaCliente($IMEI){
        try {
            $bdProventel = $this->load->database('default', TRUE);
            $query="SELECT *
            FROM clientesHolding WHERE IMEI= '{$IMEI}' and status=1";
            $data = $bdProventel->query($query);
            $dataServicio= $data->result_array();
            if(is_array($dataServicio) and count($dataServicio)>0){
                $today = date('d-m-Y');
                $bdProventel->set('status', 0);
                $bdProventel->set('fechaCancelado', $today);
                $bdProventel->set('fechaActualizacion', $today);
                $bdProventel->where('IMEI', $IMEI);
                $bdProventel->update('clientesHolding');
                $updateData = array("IMEI" => $IMEI, "fechaCancelado" => $today);
                $resp = array('status' => 1,  'msg'=>'Cancelación exitosa',  'response' => $updateData);
                return  $resp;
            }else{
            	$query="SELECT * FROM clientesHolding WHERE IMEI= '{$IMEI}' and status=0";
            	$data = $bdProventel->query($query);
            	$dataServicio= $data->result_array();
            	if (is_array($dataServicio) and count($dataServicio)>0) {
            		$updateData = array("IMEI" => $IMEI, "fechaCancelado" => $dataServicio[0]['fechaCancelado']);
                	$resp = array('status' => 0, 'msg'=>'Cliente cancelado con anterioridad', 'response' => $updateData);
                	return  $resp;		
            	}else{
            		$resp = array('status' => 0, 'msg'=>'Cliente no existe', 'response' => $IMEI);
                	return  $resp;
            	}
                
            }
        } catch (Exception $ex) {
            $resp = array('status' => 0, 'msg'=>'Error en la consulta', 'response' => "");
            return $resp;
        }
    }

    public function statusCliente($dataUpdate){
        try {
            $IMEI=$dataUpdate['IMEI'];//arreglo de IMEIS a actualizar
            $status = $dataUpdate['status'];//status a cambiar
            $activos = array();
            $cancelados = array();            
            $suspendidos = array();            
            $inexistentes = array();  
            $yaActivos = array();
            $yaSuspendidos = array();          
            //$IMEIEXITOSOS = 0;
            //$IMEIINEXISTENTES = 0;
            //$IMEICANCELADOS = 0;
            //$IMEISUSPENDIDOS = 0;

            $bdProventel = $this->load->database('default', TRUE);
            foreach ($IMEI as $key => $value) {
                $query="SELECT IMEI, status FROM clientesHolding WHERE IMEI= '{$value}'";
                $data = $bdProventel->query($query);
                $dataServicio= $data->result_array();
                if(is_array($dataServicio) and count($dataServicio)>0){
                    if ($dataServicio[0]['status'] == 1) {
                        if ($status == 1) {
                            array_push($yaActivos, $value);
                        }
                        if ($status == 2) {
                        $today = date('d-m-Y');
                        $bdProventel->set('status', 2);
                        $bdProventel->set('fechaActualizacion', $today);
                        $bdProventel->where('IMEI', $value);
                        $bdProventel->update('clientesHolding');
                        array_push($suspendidos, $value);
                        }
                    }
                    if ($dataServicio[0]['status'] == 2) {
                        if ($status == 2) {
                            array_push($yaSuspendidos, $value);
                        }
                        if ($status == 1) {
                        $today = date('d-m-Y');
                        $bdProventel->set('status', 1);
                        $bdProventel->set('fechaActualizacion', $today);
                        $bdProventel->where('IMEI', $value);
                        $bdProventel->update('clientesHolding');
                        array_push($activos, $value);
                        }   
                    }
                    if ($dataServicio[0]['status'] == 0) {
                        array_push($cancelados, $value);
                    }
            }else{
                array_push($inexistentes, $value);
            }

            /*
            
            }else{
                $query="SELECT * FROM clientesHolding WHERE IMEI= '{$IMEI}' and status=0";
                $data = $bdProventel->query($query);
                $dataServicio= $data->result_array();
                if (is_array($dataServicio) and count($dataServicio)>0) {
                    $updateData = array("IMEI" => $IMEI, "fechaCancelado" => $dataServicio[0]['fechaCancelado']);
                    $resp = array('status' => 0, 'msg'=>'Cliente cancelado con anterioridad', 'response' => $updateData);
                    return  $resp;      
                }else{
                    $resp = array('status' => 0, 'msg'=>'Cliente no existe', 'response' => $IMEI);
                    return  $resp;
                }
                
            }*/
            }
            if ($status == 1) {
                $msg = ["IMEI ya activos:"=>$yaActivos,"IMEI activados:"=>$activos,"IMEI cancelados:"=>$cancelados,"IMEI inexistentes:"=>$inexistentes];
            }
            if ($status == 2) {
                $msg = ["IMEI ya suspendidos:"=>$yaSuspendidos,"IMEI suspendidos:"=>$suspendidos,"IMEI cancelados:"=>$cancelados,"IMEI inexistentes:"=>$inexistentes];
            }
                $resp = array('status' => 1, 'msg'=>'PRUEBA DE DATOS', 'response' => $msg);
                    return  $resp;
        } catch (Exception $ex) {
            $resp = array('status' => 0, 'msg'=>'Error en la consulta', 'response' => "");
            return $resp;
        }
    }
}
