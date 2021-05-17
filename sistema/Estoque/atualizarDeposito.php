<?php
include('../../v_login.php');

$usuario = $_SESSION['usuario'];

$idDeposito = filter_input(INPUT_POST, 'idDeposito', FILTER_SANITIZE_NUMBER_INT);
$nomeDeposito = filter_input(INPUT_POST, 'nomeDeposito', FILTER_SANITIZE_STRING);
$localizacao = filter_input(INPUT_POST, 'localizacao', FILTER_SANITIZE_STRING);
$status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
date_default_timezone_set('America/Sao_Paulo');
$data = date('Y-m-d H:i:s');

//VALIDANDO DADOS E CONVERTENDO PARA MAIUSCULAS
if (empty($idDeposito)) {
	echo "Por favor retorne a pagina anterior e refaça o procedimento";
	return 0;
}else{
	$v_idDeposito = trim($idDeposito);
}

if (empty($nomeDeposito)) {
	echo "Por favor retorno a pagina anterior e insira o nome do deposito";
	return 0;
}else{
	$v_nomeDeposito = mb_strtoupper(trim($nomeDeposito));
}

if (empty($localizacao)) {
	$v_localizacao = null;
}else{
	$v_localizacao = mb_strtoupper(trim($localizacao));
}

if (empty($status)) {
	echo "Obrigatório inserir status do depósito";
	return 0;
}else{
	$v_status = mb_strtoupper(trim($status));
}
//TÉRMINO DA TRATATIVA

//CONEXÃO BANCO DE DADOS
include '../../conexao.php';

//ANÁLISE DADOS ANTIGOS
$tabelaLog = "SELECT * FROM OWHS WHERE idDeposito = '$v_idDeposito';";
$resultado = mysqli_query($conexao, $tabelaLog);

while($linha = mysqli_fetch_assoc($resultado)){
	$nomeDepositoAntigo = $linha['nomeDeposito'];
	$localizacaoAntigo = $linha['localizacao'];
	$canceladoAntigo = $linha['cancelado'];
}

//GRAVAR DADOS NO BANCO
$tabela = "UPDATE OWHS SET nomeDeposito = '$v_nomeDeposito', localizacao = '$v_localizacao', cancelado = '$v_status' WHERE idDeposito = '$v_idDeposito';";
$insert = mysqli_query($conexao, $tabela) or die('Erro ao tentar gravar dados no banco');

if (mysqli_affected_rows($conexao) > 0) {
	$logAntigo = "INSERT INTO log_Deposito(nomeDeposito, localizacao, cancelado, usuario, dataAlteracao) VALUES('$nomeDepositoAntigo', '$localizacaoAntigo', '$canceladoAntigo', '$usuario', '$data');";

	$logNovo = "INSERT INTO log_Deposito(nomeDeposito, localizacao, cancelado, usuario, dataAlteracao) VALUES('$v_nomeDeposito', '$v_localizacao', '$v_status', '$usuario', '$data');";
	mysqli_query($conexao, $logAntigo);
	mysqli_query($conexao, $logNovo);
	echo "<script>window.close();</script>";
}else{
	echo "Erro ao tentar atualizar Depósito";
}