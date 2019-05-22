<?php
/**
* @autor rosi garcilazo
*/
require_once('Clases/user_functions.php');
final class user_functionsTest extends PHPUnit_Framework_TestCase
{
	
	public function testUsuarioIncorrectoRegresaFalse(){
		$uf = new user_functions();
		$this->assertFalse($uf->verificar_empleado('1000', '98787'));
	}

	public function testRangoDeHorasRetornaTrueConHorasCorrectas(){
		$hora = new DateTime();
		$hora_actual = new DateTime();
		$hora->modify("-1 hour");
		$h_inicial = new DateTime((string)$hora->format("H:i:s"));
		$hora->modify("+2 hour");
		$h_final = new DateTime((string)$hora->format("H:i:s"));

		$uf = new user_functions();
		$this->assertTrue($uf->hora_dentro_rango_horas($h_inicial, $h_final, $hora_actual));
		
	}
	public function testRangoDeHorasRetornaFalseConHorasIncorrectas(){
		$hora = new DateTime();
		$hora_actual = new DateTime();
		$hora->modify("-4 hour");
		$h_inicial = new DateTime((string)$hora->format("H:i:s"));
		$hora->modify("+1 hour");
		$h_final = new DateTime((string)$hora->format("H:i:s"));

		$uf = new user_functions();
		$this->assertFalse($uf->hora_dentro_rango_horas($h_inicial, $h_final, $hora_actual));
	}
}
