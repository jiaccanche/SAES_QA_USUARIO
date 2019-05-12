<?php
/**
 * Created by PhpStorm.
 * User: jorge canche
 * Date: 10/05/2019
 * Time: 11:01 PM
 */
include_once ("Clases/user_functions.php");
switch ($_REQUEST['rutina']){

    case "login":
        $obj = json_decode($_REQUEST['data']);
        $user_f = new user_functions();
        print json_encode($user_f->realizar_operacion_entrada_salida($obj->user,$obj->pwd));
        break;

    default:
        break;


}

