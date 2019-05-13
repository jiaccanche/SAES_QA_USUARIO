<?php
/**
 * Created by PhpStorm.
 * User: jorge canche
 * Date: 11/05/2019
 * Time: 10:26 PM
 */

class nomina
{
    public $fin_nomina=4;

    public function __construct()
    {
    }

    /**
     * Función que calcula la nomina de todos los empleados que no esten revisión o tengo algún incidente
     *
     * @param $fecha_base: es un string con el siguiente formato: Y-m-d que será procesado para obtener
     * el calculo de nómina de 5 días que equivale a una semana laboral, se debe tomar en cuenta que sea lunes.
     */
    public function calcular_nomina($fecha_base,$semana_completa=true){
        /**verificar si es lunes*/
        $dia = (int)date('w', strtotime($fecha_base));
        //     print $dia;
//      print "Dia:".$dia."<br>";
        //Se resta un día porque se comienza a contar desde el mismo dia, si es lunes deben ser 5 días = 5 - 0, si es martes 5 - 1 = 4
        $dia--;
        $dias_restantes = $this->fin_nomina - $dia;

       $fecha_ini = new DateTime($fecha_base);
       $fecha_fin = new DateTime($fecha_base);
       $fecha_fin->modify("+".$dias_restantes." day");

       /*Obtener empleados que no tienen revisiones sin resolver*/
        /*verificar que no tenga incidentes*/
        /*verificar si no tiene un periodo -vacaciones o incapacidad*/


        for($i = $fecha_ini; $i <= $fecha_fin; $i->modify('+1 day')) {
            var_dump($i);
        }


        }

}


    $obj = new nomina();
    $obj->calcular_nomina("2019-05-13");
//$fecha = date('w', strtotime('2019-05-10'));
    //print $fecha;