<?php

include('../v_login.php');

//COLENTADO OS DADOS
$usuario = $_SESSION['usuario'];
$idComanda = filter_input(INPUT_POST, 'retornoIdComanda', FILTER_SANITIZE_NUMBER_INT);
$valorComanda = filter_input(INPUT_POST, 'valorComanda', FILTER_SANITIZE_STRING);
$valorDesconto = filter_input(INPUT_POST, 'desconto', FILTER_SANITIZE_STRING);
$valorFornecido = filter_input(INPUT_POST, 'valorFornecido', FILTER_SANITIZE_STRING);
$trocoComanda = filter_input(INPUT_POST, 'trocoComanda', FILTER_SANITIZE_STRING);
$cortesia = filter_input(INPUT_POST, 'cortesia', FILTER_SANITIZE_STRING);
date_default_timezone_set('America/Sao_Paulo');
$data = date('Y-m-d');
$horaSaida = date('H:i');
$dataAlteracao = date('Y-m-d H:i:s');

//FAZENDO AS TRATATIVAS DOS DADOS COLETADOS
if (empty($idComanda)) {
	echo "Faltando ID da Comanda. Por favor refaça o procedimento anterior.";
	return 0;
}else{
	$v_idComanda = trim($idComanda);
}

if (empty($valorComanda)) {
	echo "Faltando o Valor da Comanda. Por favor refaça o procedimento anterior.";
	return 0;
}else{
	$v_valorComanda = trim(str_replace('.', '', $valorComanda));
	$validado_valorComanda = trim(str_replace(',', '.', $v_valorComanda));
}

if (empty($valorFornecido)) {
	echo "Faltando o valor Fornecido. Por favor refaça o procedimento anterior.";
	return 0;
}else{
	$v_valorFornecido = trim(str_replace('.', '', $valorFornecido));
	$validado_valorFornecido = trim(str_replace(',', '.', $v_valorFornecido));
}

if (empty($valorDesconto)) {
	$v_valorDesconto = 0;
}else{
	$v_desconto = trim(str_replace('.', '', $valorDesconto));
	$v_valorDesconto = trim(str_replace(',', '.', $v_desconto));
}

$valorTotalFinal = $validado_valorComanda - $v_valorDesconto;
//echo "Valor comanda: ".$validado_valorComanda." - Valor Desconto: ".$v_valorDesconto."<br>";
//TROCO
$v_trocoComanda = $validado_valorFornecido - $valorTotalFinal;
//echo "Valor total final ".$valorTotalFinal." - Valor Fornecido: ".$validado_valorFornecido."<br>";

//echo "Troco: ".$v_trocoComanda."<br>";


if (empty($cortesia)) {
	$v_cortesia = 'N';
	if ($v_trocoComanda < 0) {
		echo "Valores inseridos para o pagamento da comanda errado. Por favor refaça o procedimento";
		return 0;
	}
}else if($cortesia == 'on'){
	$v_cortesia = 'Y';
	$validado_valorFornecido = 0;
	$v_valorDesconto = 0;
	$v_trocoComanda = 0;
}else{
	echo "Erro ao detectar a opção cortesia";
	return 0;
}

echo "Usuário: ".$usuario." - idComanda: ".$v_idComanda." - valorComanda: ".$validado_valorComanda." - valorFornecido: ".$validado_valorFornecido." - trocoComanda: ".$v_trocoComanda." - ValorDesconto: ".$v_valorDesconto." - Valor Descontado = ".$valorTotalFinal." - Opção Cortesia: ".$v_cortesia."<br>";


//CONEXAO BANCO DE DADOS
include '../conexao.php';

echo "Iniciando processo de busca da comanda na OCMD<br>";
$tabelaOCMD = "SELECT * FROM OCMD WHERE idComanda = '$v_idComanda';";
$resultadoOCMD = mysqli_query($conexao, $tabelaOCMD	);
if (mysqli_affected_rows($conexao) <= 0) {
	echo "Erro ao encontrar a comanda ".$v_idComanda." na tabela OCMD. Por favor refaça o procedimento<br>";
	return 0;
}else{
	echo "Comanda encontrada com sucesso na tabela OCMD<br>";
}


echo "<br>Iniciando UPDATE na tabela OCMD<br>";
//INSERINDO OS DADOS NO BANCO DE DADOS
$updateOCMD = "UPDATE OCMD SET horaSaida = '$horaSaida', valorDesconto = '$v_valorDesconto', valorPago = '$validado_valorFornecido', troco = '$v_trocoComanda', cortesia = '$v_cortesia', statusPagamento = 'FECHADO' WHERE idComanda = '$v_idComanda'; ";
$resultadoUpdateOCMD = mysqli_query($conexao, $updateOCMD);

if (mysqli_affected_rows($conexao) <= 0) {
	echo "Erro ao finalizar a comanda ".$v_idComanda;
	return 0;
}else{
	echo "Comanda finalizada com sucesso na tabela OCMD<br>";
	echo "<br>Iniciando processo de INSERT na tabela LOG_COMANDA<br>";
	//INSERINDO OS DADOS NA COMANDA 
	$insertLogComanda = "INSERT INTO LOG_COMANDA(idComanda, statusPagamento, usuario, dataAlteracao) VALUES('$v_idComanda', 'FECHADO', '$usuario', '$dataAlteracao');";
	$resultadoLogComanda = mysqli_query($conexao, $insertLogComanda);

	if (mysqli_affected_rows($conexao) <= 0) {
		echo "Erro ao atualizar a tabela LOG COMANDA<br>";
		return 0;
	}else{
		echo "Sucesso ao atualizar a tabela LOG COMANDA<br>";
	}
}
echo "Procedimento finalizado com sucesso";


echo "<script>window.close();</script>";
