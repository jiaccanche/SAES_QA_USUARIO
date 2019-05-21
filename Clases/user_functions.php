<?php
/**
 * Created by PhpStorm.
 * User: jorge canche
 * Date: 10/05/2019
 * Time: 11:06 PM
 */
include_once("conection.php");

class user_functions
{
    private $con=null;

 public function __construct()
 {
     $this->con = new conection();
     date_default_timezone_set("America/merida");
 }

    /**
     * Función que verifica si el empleado existe en la base de datos
     * @param $user
     * @param $pwd
     */
 public function verificar_empleado($user,$pwd){

     $obtener_conexion = $this->con->conectar();
     /*Agrego el query de verificacion*/
     $prepare = $obtener_conexion->prepare("select * from empleado where num_empleado=".$user." and password='".$pwd."'");
     /*Ejecuto query*/
     $prepare->execute();
     /*obtengo la respuesta como objeto*/
        return $prepare->fetchObject();

 }

 public function verificar_diaInhabil(){
    $conection = $this->con->conectar();
    $date = date('Y-m-d');

    $prepare = $conection->prepare("select * from diasinhabiles where hora = '$date'");
    $prepare->execute();

    return $prepare->fetchObject();
 }

 public function verificar_entrada_salida($user){
     $conection = $this->con->conectar();
     $date = date('Y-m-d');

    /*Verificar según la hora de entrada*/
     $dia_semana = (int) date("w");
     //1.1sumar uno para coincidir con la BD
     //$dia_semana++;

     //2.verificar si esta dentro del horario de entrada del usuario la hora
     $hora_actual = new DateTime();
     //$hora_actual = new DateTime($this->hora_actual_entrada_test);
     //$hora_actual = new DateTime($this->hora_actual_salida_test);
     //$hora_actual = new DateTime("19:20:10");

     //2.1 Obtener horario del empleado

     $dia_semana=5;

     /*Fin de semana*/
     //print $dia_semana;
     if($dia_semana == 7 || $dia_semana == 6){
         //Esta fuera de lo permitido enviar a revisión, enviar mensaje de problema
         return array("mensaje"=>"No es posible realizar el registro, fuera de días de trabajo.","estado"=>1);
     }

     $prepare = $conection->prepare("select * from horario where num_empleado=".$user." and num_dia=".$dia_semana);
     $prepare->execute();

     //Se obtiene la entrada y salida para ese día
     $obj_horario = $prepare->fetchObject();
     //var_dump($obj_horario);
     $hora_permitida = new DateTime($obj_horario->entrada);
     /*Se verifica que este dentro del rango de hora permitida*/
     $res_entrada_rango = $this->verificar_rango_horas($hora_permitida,$hora_actual);
     $salida=false;
     if(!$res_entrada_rango){
         /*Verificar si es una salida*/
         $hora_permitida= new DateTime($obj_horario->salida);
         $res_salida_rango = $this->verificar_rango_horas($hora_permitida,$hora_actual);
         /*No es salida*/
         if(!$res_salida_rango){
             return array("mensaje"=>"No es posible realizar una entrada o salida ya que está fuera del horario permitido.","estado"=>2);
         }else{
             $salida=true;
         }

     }

     /*Verificación en registro de la tabla de entradas*/
     /*Refactorizar esto es una función diferente*/
     $query = "select * from check_t where num_empleado=".$user." and fecha_jornada='".$date."'::date";
     //$query = "select * from check_t where num_empleado=".$user." and fecha_jornada='2019-05-12'::date";
     $prepare = $conection->prepare($query);
     //Ejecuto query
     $prepare->execute();
     //obtengo la respuesta como objeto
     $obj =$prepare->fetchObject();
     $registro = is_object($obj);

     if($registro){
         /*Verifica si la hora introducida sigue en el rango de entrada y ya existe un registro de entrada*/
         if($res_entrada_rango) return array("mensaje"=>"Ya existe un registro para entrada,para realizar una salida es necesario salir a la hora pertinente.","estado"=>2);

         return array("registro"=>$obj,"estado"=>4);
     }else{
         return array("mensaje"=>"No hay registro.","estado"=> $salida==true ? 4:3);
     }

 }

 function verificar_rango_horas($hora_permitida,$hora_actual){
     /*Rango de horas permitidas*/
     $hora_permitida->modify("+1 hour");
     $h_final = new DateTime((string)$hora_permitida->format("H:i:s"));
     $hora_permitida->modify("-2 hour");
     $h_ini =new DateTime((string)$hora_permitida->format("H:i:s"));

     return $this->hora_dentro_rango_horas($h_ini,$h_final,$hora_actual);
 }

 function hora_dentro_rango_horas($dateEntrada_menos, $dateEntrada_mas, $dateEntrada) {
        return $dateEntrada_menos <= $dateEntrada && $dateEntrada <= $dateEntrada_mas;
 }

 public function realizar_operacion_entrada_salida($user,$pwd){
    $verificacion = $this->verificar_empleado($user,$pwd);

    if ($this->verificar_diaInhabil() == false) {
        return array("mensaje"=>"No se puede registrar entradas ni salidas en día inhábil","estado"=>false);
    }
    if($verificacion == false){
        return array("mensaje"=>"La contraseña o el usuario son incorrectos","estado"=>false);
    }else{
        //Verificar si es entrada
        $response = $this->verificar_entrada_salida($user);
        //verificar estados para la entrada
        switch ($response['estado']){
            case 1: /*Esta en fin de semana*/return $response; break;

            case 2: /*Esta fuera del rango de horas*/return $response; break;

            case 3: /*No hay registro de entrada*/return $this->insertar_entrada_salida($user,0);break;
            case 4: /*Es una salida*/
                /*Existe el objeto para salida*/
                if(isset($response['registro'])){
                    /*Verdadero si ya es una salida*/
                    if(!$response['registro']->entrada_salida){
                        return $this->actualizar_to_salida($user);
                    }else{
                        return array("mensaje"=>"Ya hay un registro de salida, no es posible agregar uno nuevo para este día.","estado"=>false);
                    }
                }else{
                    return $this->insertar_entrada_salida($user,1);
                }

                break;
        }
    }

 }

    public  function insertar_entrada_salida($user,$e_s)
    {
        $conection=$this->con->conectar();
        /*Realizar guardado*/
        $prepare =$conection->prepare("insert into check_t(hora_e,hora_s, fecha_jornada, entrada_salida,num_empleado)
                                      values (:hora_e,:hora_s,:fecha_actual,:es,:num_empleado) ");

        $hora_actual = new DateTime();
        $string_hora = (string) $hora_actual->format("H:i:s");
        $string_date = (string) $hora_actual->format("Y-m-d");
        $prepare->bindValue(':fecha_actual',$string_date);
        $prepare->bindValue(':es',$e_s);
        $prepare->bindValue(':num_empleado',$user);

        /*Insertar la entrada o salida en su caso*/
        if($e_s==0){
            $prepare->bindValue(':hora_e',$string_hora);
            $prepare->bindValue(':hora_s',null);
        }else{
            $prepare->bindValue(':hora_e',null);
            $prepare->bindValue(':hora_s',$string_hora);
        }

        $resultado = $prepare->execute();
        $operacion = ($e_s == 0) ? "Entrada":"Salida";
        if($resultado){
            return array("mensaje"=>"Se ha realizado la ".$operacion.".","estado"=>true);
        }else{
            return array("mensaje"=>"No fue posible realizar la ".$operacion.".","errorinfo"=>$prepare->errorInfo(),"estado"=>false);
        }
    }

    public function actualizar_to_salida($user)
    {
        $fecha = new DateTime();

        //$fecha = new DateTime($this->hora_actual_salida_test." ".$this->fecha_actual_test);
        /*Verifico si existe un registro para actualizar*/
        $conection = $this->con->conectar();
        $prepare = $conection->prepare("update check_t set hora_s = :hora_s, entrada_salida= :es 
        where num_empleado=:num_empleado and fecha_jornada=:fecha_jornada");

        $string_salida = (string) $fecha->format("H:i:s") ;
        $string_fecha = (string) $fecha->format("Y-m-d");
        $prepare->bindValue(":hora_s",$string_salida);
        $prepare->bindValue(":es",1);
        $prepare->bindValue(":num_empleado",$user);
        $prepare->bindValue(":fecha_jornada",$string_fecha);

        $res = $prepare->execute();
        if($res){
            return array("mensaje"=>"Se ha realizado la salida.","estado"=>true);
        }else{
            return array("mensaje"=>"No fue posible realizar la salida","errorinfo"=>$prepare->errorInfo(),"estado"=>false);
        }
    }


}



// $objeto = new user_functions();
//$cc =  $objeto->insertar_entrada("1");
//$cc = $objeto->realizar_operacion_entrada_salida("2","12345");
//$cc = $objeto->actualizar_to_salida("2");
//var_dump($cc);
//print $cc;