<?php
include('../../v_login.php');

$usuario = $_SESSION['usuario'];
$nomeDeposito = filter_input(INPUT_POST, 'nomeDeposito', FILTER_SANITIZE_STRING);
$localizacao = filter_input(INPUT_POST, 'localizacao', FILTER_SANITIZE_STRING);
date_default_timezone_set('America/Sao_Paulo');
$data = date('Y-m-d');

//TRATATIVA
if (empty($nomeDeposito)) {
	echo "Por favor retorne a página anterior";
	return 0;
}else{
	$v_nomeDeposito = mb_strtoupper(trim($nomeDeposito));
}

$v_localizacao = mb_strtoupper(trim($localizacao));


//CONEXAO BANCO DE DADOS
include '../../conexao.php';

$tabela = "INSERT INTO OWHS(nomeDeposito, localizacao, dataCadastro, usuarioCadastro, cancelado) VALUES('$v_nomeDeposito', '$v_localizacao', '$data', '$usuario', 'N');";
$resultado = mysqli_query($conexao, $tabela) or die('Erro ao cadastrar os dados no banco de dados');

if (mysqli_affected_rows($conexao) > 0) {
	echo "<script>window.close();</script>";
}else{
	echo "Erro ao tentar cadastrar o depósito";

}
