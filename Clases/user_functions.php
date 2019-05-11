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

 public function verificar_entrada($user){
     $obtener_conexion = $this->con->conectar();
     $date = date('Y-m-d');

     $query = "select * from check_t where num_empleado=".$user." and fecha_jornada='".$date."'::date";

     $prepare = $obtener_conexion->prepare($query);
     //Ejecuto query
     $prepare->execute();
     //obtengo la respuesta como objeto
     return $prepare->fetchObject();


 }

 public function realizar_operacion_entrada_salida($user,$pwd){
    $verificacion = $this->verificar_empleado($user,$pwd);

    if($verificacion == false){
        return array("mensaje"=>"La contraseña o el usuario son incorrectos","estado"=>false);
    }else{
        //Verificar si es entrada
        $response = $this->verificar_entrada($user);
        //Insertar entrada
        if($response == false){

            $response = $this->insertar_entrada($user);
            return $response;

        }else{
            $response = $this->actualizar_to_salida($user);
        }




    }

 }

    public  function insertar_entrada($user)
    {
        //1.obtener el día de la semana
        $dia_semana = (int) date("w");
        //1.1sumar uno para coincidir con la BD
        $dia_semana++;
        print $dia_semana."<br>";
        //2.verificar si esta dentro del horario de entrada del usuario la hora

        $hora_actual = new DateTime();

        //2.1 Obtener horario del empleado
        $dia_semana=5;
        $conection = $this->con->conectar();
        $prepare = $conection->prepare("select * from horario where num_empleado=".$user." and num_dia=".$dia_semana);
        $prepare->execute();
        //
        $obj_horario = $prepare->fetchObject();
        $hora_permitida = new DateTime($obj_horario->entrada);
        /*minutos despues permitidos*/
        $hora_permitida->modify("+5 minute");
        /*realizar la operación de resta si esta permitido el chequeo en el intervalo*/
        $diff = $hora_permitida->diff($hora_actual);
        /*verifico que la resta de la hora permitida - la hora es negativa, entonces está fuera de la hora permitida*/
        if($diff->invert == 1){

        }else{

    }



    }


}


 $objeto = new user_functions();
 $cc =  $objeto->insertar_entrada("1");
 //var_dump($cc);