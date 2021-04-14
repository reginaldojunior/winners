<?php

class HieraquiaController extends AppController {

	public function listar_cadastros() {
		$this->loadModel('Hieraquia');
		$dados = $this->Hieraquia->find('all',
			array('conditions' => 
				array(
					'ativo' => 1,
					'id_usuario' => $this->instancia
				)
			)
		);

		$this->set('hieraquias', $dados);
		$this->layout = 'wadmin';
	}

	public function adicionar_hieraquia() {
		$this->layout = 'wadmin';
		$this->set('modulos', $this->modulos);
	}

	public function s_adicionar_hieraquia() {
		$this->loadModel('Hieraquia');
		$this->loadModel('HieraquiaModulo');

		$dados = $this->request->data('dados');

		$hieraquia['nome'] = $dados['nome'];
		$hieraquia['id_usuario'] = $this->instancia;
		$hieraquia['ativo'] = 1;

		$hieraquia_salva = $this->Hieraquia->save($hieraquia);

		if (!$hieraquia_salva) {
			$this->Session->setFlash('Ocorreu um erro ao salva o hieraquia!');
            return $this->redirect('/hieraquia/listar_cadastros');
		}

		$relaciona_modulo_hieraquia = [];
		foreach ($dados['modulos'] as $tipo_de_permissao => $modulos) {
			foreach ($modulos as $modulo) {
				$relaciona_modulo_hieraquia[] = [
					'hieraquia_id' => $hieraquia_salva['Hieraquia']['id'],
					'modulo_id' => $modulo,
					'tipo_de_permissao' => $tipo_de_permissao
				];
			}
		}

		$this->HieraquiaModulo->saveMany($relaciona_modulo_hieraquia);

		$this->Session->setFlash('Tudo certo, sua hieraquia foi criada com sucesso!');
		return $this->redirect('/hieraquia/listar_cadastros');
	}
	
	public function listar_subusuarios() {
		$this->loadModel('Usuario');
		$this->loadModel('SubUsuarios');

		$subusuarios = $this->SubUsuarios->find('all', 
			array('conditions' => 
				array(
					'id_usuario' => $this->instancia
				)
			)
		);
		
		$usuarios = [];
		foreach ($subusuarios as $subusuario) {
			$usuario = $this->Usuario->find('first', array(
					'conditions' => array(
						'subusuario_id' => $subusuario['SubUsuarios']['id']
					)
				)
			);

			if (!empty($usuario)) { 
				$usuarios[] = $usuario;
			}
		}
		
		$this->layout = 'wadmin';
		$this->set('usuarios', $usuarios);
	}

	public function visualizar($id)
	{
		$this->loadModel('Hieraquia');
		$this->loadModel('HieraquiaModulo');

		$hieraquia = $this->HieraquiaModulo->find('all',
						array('conditions' => 
							array(
								'hieraquia_id' => $id
							)
						)
					);

		$this->layout = 'wadmin';
		$this->set('hieraquia', $hieraquia);
	}

	public function adicionar_subusuario()
	{
		$this->loadModel('Hieraquia');
		$hieraquias = $this->Hieraquia->find('all',
			array('conditions' => 
				array(
					'id_usuario' => $this->instancia
				)
			)
		);

		$this->set('hieraquias', $hieraquias);
		$this->layout = 'wadmin';
	}

	public function s_adicionar_usuario()
	{
		$this->loadModel('SubUsuarios');

		$dados = $this->request->data('dados');

		if ($dados['password'] != $dados['password_confirm']) {
			$this->Session->setFlash('As senhas não coincidem, digite as mesmas senhas em ambos campos.');
			return $this->redirect('/hieraquia/adicionar_subusuario');
		}

		if ($this->verificar_email($dados['email'])) {
			$this->Session->setFlash('E-mail já está sendo utilizado no sistema, utilize outro acesso.');
			return $this->redirect('/hieraquia/adicionar_subusuario');
		}

		unset($dados['password_confirm']);

		$dados_subusuario = [
			'id_usuario' => $this->instancia,
			'id_hieraquia' => $dados['hieraquia_id'],
			'ativo' => 1
		];
		$subusuario = $this->SubUsuarios->save($dados_subusuario);

		$dados_usuario = [
			'nome' => $dados['nome'],
			'email' => $dados['email'],
			'senha' => sha1($dados['password']),
			'subusuario_id' => $subusuario['SubUsuarios']['id'],
			'loja' => 0,
			'loja_active' => 0,
			'layout_loja' => 'default',
			'cep_origem' => '',
			'descricao' => '',
			'email_pagseguro' => '',
			'folder_view' => '',
			'token_pagseguro' => ''
		];
		$usuario = $this->Usuario->save($dados_usuario);

		if (!$usuario) {
			$this->Session->setFlash('Ocorreu algum erro, tente novamente ou contate o suporte.');
			return $this->redirect('/hieraquia/adicionar_subusuario');
		}
		
		$this->Session->setFlash('Usuario ' . $dados['email'] . ' foi cadastrado com sucesso.');
		return $this->redirect('/hieraquia/listar_subusuarios');
	}

	public function verificar_email($email)
	{
		$this->loadModel('Usuario');

		$resposta = $this->Usuario->find('count',
			array(
				'conditions' => array('Usuario.email' => $email)
			)
		);

		return $resposta > 0 ? true : false;
	}

}