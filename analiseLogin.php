<?php
//VERIFICAR SE O USUÁRIO ESTÁ LOGADO
session_start();

//ATRIBUIÇÃO DE DADOS
$usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING);
$senha = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING);

//ANALISE DE RETORNO EM BRANCO
$v_usuario = trim($usuario);
$v_senha = trim($senha);


if (empty($v_usuario) || empty($v_senha)) {
	echo "Retorne a página anterior.";
	return 0;
}

//CONVERTENDO EM MAIUSCULA
$v_usuario = strtoupper($v_usuario);
$v_senha = strtoupper($v_senha);



//CONEXAO BANCO DE DADOS
include 'conexao.php';
$tabela = "SELECT login, senha, nome FROM usuario WHERE login = '$v_usuario' AND senha = md5('$v_senha');";
$resultado = mysqli_query($conexao, $tabela) or die('Erro ao buscar dados no banco');
$linha = mysqli_num_rows($resultado);

if ($linha == 1) {
	$_SESSION['usuario'] = $v_usuario;
	$_SESSION['nome'] = 
	header('location:sistema/index.php');
	exit();
}else{
	$_SESSION['nao_autenticado'] = true;
	header('location:index.php');
	exit();
}