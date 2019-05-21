<?php
/**
 * Created by PhpStorm.
 * User: jorge canche
 * Date: 11/05/2019
 * Time: 10:26 PM
 */

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include_once ("conection.php");
include_once ("user_functions.php");
class nomina
{
    public $conection;
    public $fin_nomina=4;
    public $penalizacion=1.6;

    public function __construct()
    {
        date_default_timezone_set("America/merida");
        $this->conection = new conection();
    }

    /**
     * Función que calcula la nomina de todos los empleados que no esten revisión o tengo algún incidente
     *
     * @param $fecha_base: es un string con el siguiente formato: Y-m-d que será procesado para obtener
     * el calculo de nómina de 5 días que equivale a una semana laboral, se debe tomar en cuenta que sea lunes.
     */

    public function calcular_nomina($fecha_base,$semana_completa=true){
        /**Obtengo el día de la semana de la fecha base*/
        $dia = (int)date('w', strtotime($fecha_base));

        //Se resta uno, para obtener un día anterior, evita conflictos.
        $dia--;

        /**Si es lunes es el día 1, pero se debe restar para obtener los dias restantes por lo cual es 0 ya que el lunes es el principio*/
        $dias_restantes = $this->fin_nomina - $dia;

        //1. Buscar empleados sin incidentes
        $lista_empleados_sin_incidentes = $this->buscar_empleados_sin_penalizar();
        //var_dump($lista_empleados_sin_incidentes);
        if (count($lista_empleados_sin_incidentes) == 0) return array("mensaje"=>"No hay empleados para realizar calculo de nomina");

        $lista_nomina_empleados = array();
        foreach ($lista_empleados_sin_incidentes as $empleado){

            //1.Buscar en la tabla de entradas/salidas por día las horas trabajadas

            $fecha_ini = new DateTime($fecha_base);
            $fecha_fin = new DateTime($fecha_base);
            $fecha_fin->modify("+".$dias_restantes." day");

            $horas_trabajadas =0;
            $empleado['Horas_penalizadas']= 0;
            for($i = $fecha_ini; $i <= $fecha_fin; $i->modify('+1 day')) {

                $obj_user_check = $this->buscar_user_check($empleado['num_empleado'],$i);

                if(is_object($obj_user_check)){
                    //print "<br><span style='color: blue;'>Empleado:".$obj_user_check->num_empleado."</span>";

                    //Obtener las horas trabajas en el dia
                    $horas_por_dia= $this->restar_horas($obj_user_check->hora_e,$obj_user_check->hora_s);
                    //print "<br><span style='color:green;'>Horas por dia:".$horas_por_dia."</span>";
                    //Consultar si se paga por periodo en el caso de la resta de horas es cero
                    if($horas_por_dia == 0) $horas_por_dia = $this->obtener_pago_periodo_dia_inhabil($empleado['num_empleado'],$i);

                    //Si hay penalización
                    //print "<br><span style='color: red;'>Penalizacion:".$obj_user_check->num_penalizacion."</span><br>";
                    if ($obj_user_check->num_penalizacion != null)
                        $empleado['Horas_penalizadas'] += $this->penalizacion;
                        $horas_por_dia =  $horas_por_dia - $this->penalizacion;

                    //Sumar horas trabajadas
                    //print "<br><span style='color:green;'>Horas por dia:".$horas_por_dia."</span>";
                    $horas_trabajadas += $horas_por_dia;

                }


            }

            /*2. calcular salario: convertir las horas trabajadas en dinero*/
            $empleado['Horas_totales'] = $horas_trabajadas;
            //print "<br><span style='color: black;'>Horas totales:".$horas_trabajadas."</span><br>";
            $empleado['salario_total'] = $this->calcular_salario($empleado['salario_pe'],$horas_trabajadas);
            //print "<br><span style='color:yellow;'>Horas totales:".$empleado['salario_total']."</span><br>";
            $lista_nomina_empleados[] = $empleado;
        }

        //var_dump($lista_nomina_empleados);
        /********************************/
          $objeto_serializar = array("registros_nomina"=>$lista_nomina_empleados,"fecha_fin"=>$fecha_fin->format("Y-m-d"));
          return $this->guardar_nomina_serializada($objeto_serializar,$fecha_base);
        }

        public function obtener_pago_periodo_dia_inhabil($empleado,$fecha){

            $query = "select tipo from periodo where num_empleado =:num_empleado 
                      and fecha_ini <=:fecha ::date and fecha_fin >=:fecha ::date";

            $obj_conection = $this->conection->conectar();
            $prepare = $obj_conection->prepare($query);
            $prepare->bindValue(":num_empleado",$empleado);
            $prepare->bindValue(":fecha", (string)$fecha->format("Y-m-d"));
            $prepare->execute();

            $respuesta = $prepare->fetchObject();
            //var_dump($respuesta);
            if(is_object($respuesta)){
                if ($respuesta->tipo != 'suspension') {
                    return 8;
                }else{
                    return 0;
                }

            }else {

                if($this->dia_inhabil($fecha)){
                    /*Es un día inhabil*/
                    return 8;
                }else{
                    /*No asisitio a trabajar*/
                    return 0;
                }

            }

        }

        public function calcular_salario($salario_pe, $horas_totales){
            if($horas_totales > 40) $horas_totales = 40;
            return $salario_pe * $horas_totales;
        }

        public function buscar_user_check($empleado,$date){
            $query = "select check_t.num_empleado,check_t.hora_e, check_t.hora_s, check_t.fecha_jornada, check_t.entrada_salida, 
	                        penalizacion.num_penalizacion, penalizacion.fecha_jornada as f_jornada_p
                            from check_t
                            left join penalizacion on check_t.fecha_jornada = penalizacion.fecha_jornada 
                            and check_t.num_empleado = penalizacion.num_empleado
                          where check_t.num_empleado=:num_empleado and check_t.fecha_jornada=:fecha_jornada";

            $obj_conection = $this->conection->conectar();
            $prep = $obj_conection->prepare($query);
            $prep->bindValue(":num_empleado",$empleado);
            $prep->bindValue(":fecha_jornada",(string) $date->format("Y-m-d") );


            $prep->execute();

            return $prep->fetchObject();

        }

        public function buscar_empleados_sin_penalizar(){
            $query = "SELECT empleado.num_empleado,
                        empleado.nombre,
                        empleado.estatus,
	                    puesto.descripcion,
	                    tabulador.salario_pe
	                    FROM empleado 
                        left join tabulador on empleado.puesto = tabulador.num_puesto
                        left join puesto on empleado.puesto = puesto.num_puesto
  	                    where not exists (
		                select 
		                from incidencias
		                where empleado.num_empleado = incidencias.num_empleado )
                        order by empleado.num_empleado asc;";

            $obj_conection = $this->conection->conectar();
            $prepare  = $obj_conection->prepare($query);
            //$prepare = $obj_conection->prepare("select * from diasinhabiles");
            $prepare->execute();

            return $prepare->fetchAll();


        }

        public function restar_horas($horaini,$horafin){

        try {
                $f1 = new DateTime($horaini);
                $f2 = new DateTime($horafin);
            } catch (Exception $e) {
                return $e->getMessage();
            }

            $d = $f1->diff($f2);
            $horas = $d->h + ($d->i/60);
            return $horas;
        }

        public function dia_inhabi1($fecha){
            $obj_c = $this->conection->conectar();
            $prepare = $obj_c->prepare("select hora from diasinhabiles where hora=:fecha_s ::date");
            $prepare->bindValue(":fecha_s",(string)$fecha->format("Y-m-d"));
            $prepare->execute();

            if(is_object($prepare->fetchObject())){
                return true;
            }else{
                return false;
            }

        }

    private function guardar_nomina_serializada($lista_empleados_sin_incidentes,$fecha)
    {

        $serializado = json_encode($lista_empleados_sin_incidentes);

        $nombre_archivo = "../nominas_excel/nomina_".$fecha.".txt";

        if(file_exists($nombre_archivo))
        {
            if(!unlink($nombre_archivo)){
                //No fue posible eliminar el archivo
            }
        }

        if($archivo = fopen($nombre_archivo, "a"))
        {
            if(fwrite($archivo, $serializado))
            {
                $mensaje = "Se han guardado los datos";
            }
            else
            {
                $mensaje= "Ha habido un problema al crear el archivo";
            }

            fclose($archivo);
        }

        return array("path"=> $nombre_archivo,"mensaje"=>$mensaje);

    }

    public function read_archivo_txt ($path_file){
        $fn = fopen($path_file,"r");
        $result = fgets($fn);
        fclose($fn);
        return (string)$result;

    }


    public function generar_reporte_excel_fecha($fecha_lunes,$path_file=''){
        $dia_lunes = date('w',strtotime($fecha_lunes));
        /*Verificar si era lunes*/
        if($dia_lunes != 1) return array("mensaje"=>"No es un lunes, no es posible generar el reporte","estado"=>false);

        /*Buscar dentro del directorio*/
        if($path_file=='') $path_file="../nominas_excel/nomina_".$fecha_lunes.".txt";

        /*Leemos el archivo txt*/
        if(is_file($path_file)){
            $result=$this->read_archivo_txt($path_file);
        }else{
            $respuesta = $this->calcular_nomina($fecha_lunes);
            if($respuesta['path']==null) return $respuesta;
            $result=$this->read_archivo_txt($respuesta['path']);
        }


        /*Decode la lista serializada*/
        $objeto_nomina=  json_decode($result);
        $lista = $objeto_nomina->registros_nomina;
        /*Creo archivo excel*/
        $Excel_archivo =  new Spreadsheet();
        $sheet = $Excel_archivo->getActiveSheet();

        /*Agregar titulos*/
        $sheet->setCellValue('A1','Nomina')->getStyle('A1')->getFont()->setBold(true);
        $sheet->setCellValue('A2','Nombre')->getStyle('A2')->getFont()->setBold(true);
        $sheet->setCellValue('B2','Puesto')->getStyle('B2')->getFont()->setBold(true);
        $sheet->setCellValue('C2','Salario por hora')->getStyle('C2')->getFont()->setBold(true);
        $sheet->setCellValue('D2','Horas penalizadas')->getStyle('D2')->getFont()->setBold(true);
        $sheet->setCellValue('E2','Horas totales')->getStyle('E2')->getFont()->setBold(true);
        $sheet->setCellValue('F2','Salario total')->getStyle('F2')->getFont()->setBold(true);
        $sheet->setCellValue('G2','Fecha inicio')->getStyle('F2')->getFont()->setBold(true);
        $sheet->setCellValue('H2','Fecha final')->getStyle('F2')->getFont()->setBold(true);
        $numero_fila_libre = 3;
        /*Recorro lista de empleados*/

        if (is_array($lista)){
            foreach ($lista as $key=>$value){
                $celda_escribir = $key + $numero_fila_libre;
                $sheet->setCellValue('A'.$celda_escribir,$value->nombre);
                $sheet->setCellValue('B'.$celda_escribir,$value->descripcion);
                $sheet->setCellValue('C'.$celda_escribir,$value->salario_pe);
                $sheet->setCellValue('D'.$celda_escribir,$value->Horas_penalizadas);
                $sheet->setCellValue('E'.$celda_escribir,$value->Horas_totales);
                $sheet->setCellValue('F'.$celda_escribir,$value->salario_total);
                $sheet->setCellValue('G'.$celda_escribir,$fecha_lunes);
                $sheet->setCellValue('H'.$celda_escribir,$objeto_nomina->fecha_fin);
            }

        }

        /*Guardo el archivo*/
        $writer_Excel = new Xlsx($Excel_archivo);
        try{
           // $path_excel= '../nominas_excel/nomina_'.$fecha_lunes.'.xlsx';
            $name_file = 'nomina_'.$fecha_lunes.'_'.$objeto_nomina->fecha_fin;
            //$res = $writer_Excel->save($path_excel);
            //return array("mensaje"=>"Se ha creado exitosamente el arhivo excel","path_excel"=>$path_excel,"estado"=>true);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="'.$name_file.'.xlsx"');
            $writer_Excel->save("php://output");

        }catch (\PhpOffice\PhpSpreadsheet\Exception $e){
            return array("mensaje"=>$e->getMessage(),"estado"=>false);
        }


    }

}


    //$obj = new nomina();
    //$response = $obj->calcular_nomina("2019-05-06");
    //$response = $obj->generar_reporte_excel_fecha("2019-05-06");

    /**/
    // $response = $obj->buscar_empleados_sin_penalizar();
    //$response = $obj->buscar_user_check('3', new DateTime("2019-05-06"));
    //$response = $obj->restar_horas("00:00:00","00:00:00");
    //$response = $obj->obtener_pago_periodo_dia_inhabil('5', new DateTime("2019-05-06"));
    //$response = $obj->dia_inhabi1(new DateTime("2019-05-06"));