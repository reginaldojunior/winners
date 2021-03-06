<?php

require(APP . 'Controller/AviseMeController.php');

/**
* 
*/
class AviseMeControllerTest extends ControllerTestCase
{

    public $controllers = array('app.aviseme');

	protected $AviseMe;

	public function setUp()
	{
		$this->AviseMe = new AviseMeController;
	}

	/**
	* @test view listar cadastros
	**/
	public function testViewListarCadastros()
	{
		$result = $this->testAction('/aviseme/listar_cadastros');
		debug($result);
	}

	/**
	* @test post Test function to create list aviseme
	**/
	public function testPostMailAndProductToCreateOfAviseMe()
	{
		$data = array(
			'usuario_id' => 13,
			'produto_id' => 75,
			'email'      => 'email@teste.com',
			'ativo'      => 1
		);

		$result = $this->testAction(
			'/aviseme/cadastrar_avise_me',
			array('data' => $data, 'method' => 'post')
		);

		$this->assertEquals(true, $result);

		debug($result);
	}

	public function testSendMailOfAviseMe()
	{
		$produto_id = 75;

		$email = "jr.design_2010@hotmail.com";

		$result = $this->AviseMe->enviar_email_aviseme($produto_id, $email);

		$this->assertEquals(true, $result);

		debug($result);
	}


}