<?php

$idParcela = filter_input(INPUT_GET, 'idParcela', FILTER_SANITIZE_NUMBER_INT);
if (empty($idParcela)) {
	echo "Código da parcela inválido. Por favor retorno na página anterior e refaça o procedimento";
	return 0;
}else{
	$v_idParcela = trim($idParcela);
}

//CONEXAO BANCO DE DADOS
include '../../conexao.php';

$tabelaPCH2 = "UPDATE PCH2 SET status = 'FECHADO' WHERE idParcela = '$v_idParcela';";
$resultado = mysqli_query($conexao, $tabelaPCH2);

if (mysqli_affected_rows($conexao) <= 0) {
	echo "Erro ao tentar efetuar o pagamento para a parcela ".$v_idParcela.". Por favor contate o administrador";
	return 0;
}else{
	echo "<script>window.close();</script>";
}