<?php
include('../v_login.php');

//COLENTADO OS DADOS
$usuario = $_SESSION['usuario'];
$idComanda = filter_input(INPUT_GET, 'idComanda', FILTER_SANITIZE_NUMBER_INT);
date_default_timezone_set('America/Sao_Paulo');
$data = date('Y-m-d');
$horaSaida = date('H:i');
$dataAlteracao = date('Y-m-d H:i:s');

//FAZENDO AS TRATATIVAS DOS DADOS COLETADOS
if (empty($idComanda)) {
	echo "Faltando ID da Comanda. Por favor refaça o procedimento anterior.<br>";
	return 0;
}else{
	$v_idComanda = trim($idComanda);
}

echo "Comanda a ser cancelada: ".$v_idComanda."<br>";

//CONEXAO COM BANCO DE DADOS 
include '../conexao.php';

echo "<br>Iniciando UPDATE da tabela OCMD<br>";
//ATUALIZANDO O CAMPO STATUS DA TABELA OCMD
$updateOCMD = "UPDATE OCMD SET statusPagamento = 'CANCELADO', statusEstoque = 'CANCELADO', cancelado = 'S' WHERE idComanda = '$v_idComanda';";
$resultadoUpdate = mysqli_query($conexao, $updateOCMD);

if (mysqli_affected_rows($conexao) <= 0) {
	echo "Erro ao atualizar a tabela OCMD<br>";
	return 0;
}else{
	echo "Sucesso ao atualizar a comanda: ".$v_idComanda." na tabela OCMD<br>";
	echo "Iniciando INSERT na tabela LOG_COMANDA<br>";
	//INSERINDO OS DADOS NA TABELA LOG_COMANDA 
	$insertLogComanda = "INSERT INTO LOG_COMANDA(idComanda, statusPagamento, statusEstoque, usuario, dataAlteracao) VALUES('$v_idComanda', 'CANCELADO', 'CANCELADO', '$usuario', '$dataAlteracao');";
	$resultadoLogComanda = mysqli_query($conexao, $insertLogComanda);

	if (mysqli_affected_rows($conexao) <= 0) {
		echo "Erro ao atualizar a tabela LOG COMANDA<br>";
		return 0;
	}else{
		echo "Sucesso ao atualizar a tabela LOG COMANDA<br>";
	}
}

echo "<br>Iniciando UPDATE na tabela CMD1<br>";
//ATUALIANDO A TABELA CMD1 PARA CANCELADO
$updateCMD1 = "UPDATE CMD1 SET cancelado = 'S' WHERE idComanda = '$v_idComanda';";
$resultadoCMD1 = mysqli_query($conexao, $updateCMD1);

if (mysqli_affected_rows($conexao) <= 0) {
	echo "Erro ao atualizar a tabela CMD1<br>";
	return 0;
}else{
	echo "Sucesso ao atualizar a comanda ".$v_idComanda." na tabela CMD1<br>";
}

echo "Comanda encerrada com sucesso<br>";

echo "<script>window.close();</script>";


