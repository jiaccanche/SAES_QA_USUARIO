<?php
/**
* @autor Jorge Canche
*/
require_once('Clases/conection.php');
final class conectionTest extends PHPUnit_Framework_TestCase
{
	public function testConectionReturnsBDConnection(){
		$con = new conection();
		$this->assertNotNull($con->conectar()); 
	}
}