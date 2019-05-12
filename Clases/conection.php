<?php
/**
 * Created by PhpStorm.
 * User: jorge canche
 * Date: 10/05/2019
 * Time: 04:37 PM
 */

class conection
{

    private $server = "localhost";
    private $password = "d0ct0r";
    private $user = "postgres";
    private $bdname = "SAES";
    private $conection_bd = null;

    public function __construct()
    {
    }

    public function conectar($nameBD=null){

        if($nameBD !=null){
            return;
        }else{

            $dsn = "pgsql:host=$this->server;port=5432;dbname=$this->bdname";
            try{
                /*Se crea una conexión*/
                $this->conection_bd = new PDO($dsn,$this->getUser(),$this->getPassword());
                if($this->conection_bd){
                    /*La conexión fue correcta*/
                    return $this->conection_bd;
                }
            }catch (PDOException $e) {
                // report error message
                echo $e->getMessage();
                //die($e->getMessage());
            }
        }


    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getBdname()
    {
        return $this->bdname;
    }

    /**
     * @param mixed $bdname
     */
    public function setBdname($bdname)
    {
        $this->bdname = $bdname;
    }

    /**
     * @return resource|null
     */
    public function getConectionBd()
    {
        return $this->conection_bd;
    }

    /**
     * @param resource|null $conection_bd
     */
    public function setConectionBd($conection_bd)
    {
        $this->conection_bd = $conection_bd;
    }
    /**
     * @return mixed
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param mixed $server
     */
    public function setServer($server)
    {
        $this->server = $server;
    }
}



    //Ejemplo para realizar la conección y hacer un query simple para hacer un select
    /*
    //crear el objeto para poder realizar la conexion
    $objeto_to_conectar = new conection();
    //Haces la conexión
    $pd = $objeto_to_conectar->conectar();
    //Realizar una preparación para un query
    $registrosx = $pd->prepare("select * from empleado");
    //Ejecutas el query preparado
    $registrosx->execute();
    //obtienes los datos extraidos, aqui puedes crear objeto tipo std de php
    $lista = $registrosx->fetchAll(PDO::FETCH_ASSOC);
    var_dump($lista);*/






