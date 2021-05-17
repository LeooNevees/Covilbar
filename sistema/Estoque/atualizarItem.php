<?php
include('../../v_login.php');
$codItem = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
$editar = filter_input(INPUT_POST, 'editar', FILTER_SANITIZE_STRING);
$usuario = $_SESSION['usuario'];
date_default_timezone_set('America/Sao_Paulo');
$data = date('Y-m-d H:i:s');

//TRATATIVA INFORMAÇÕES

if (empty($codItem)) {
	echo "Por favor retorne a página anterior e refaça o procedimento.";
	return 0;
}else{
	$v_codItem = trim($codItem);
}

if($editar == 'ativar'){
	$v_editar = 'N';
}else if($editar == 'inativar'){
	$v_editar = 'S';
}else{
	echo "Por favor refaça o procedimento novamente.";
	return 0;
}

//CONEXAO BANCO DE DADOS
include '../../conexao.php';

//ANÁLISE DADOS ANTIGOS
$tabelaLog = "SELECT * FROM OITM WHERE idProduto = '$v_codItem';";
$resultado = mysqli_query($conexao, $tabelaLog);

while($linha = mysqli_fetch_assoc($resultado)){
	$idProdutoAntigo = $linha['idProduto'];
	$nomeProdutoAntigo = $linha['nomeProduto'];
	$canceladoAntigo = $linha['cancelado'];
}

if ($canceladoAntigo == $v_editar) {
	echo "Status antigo do item igual ao novo status. Impossível continuar, por favor refaça o procedimento";
	return 0;
}

//GRAVAR DADOS NO BANCO
$tabela = "UPDATE OITM SET cancelado = '$v_editar' WHERE idProduto = '$v_codItem';";

$insert = mysqli_query($conexao, $tabela) or die('Erro ao tentar gravar dados no banco');

if(mysqli_affected_rows($conexao) > 0) {
	$logAntigo = "INSERT INTO log_item(idProduto, nomeProduto, cancelado, usuario, dataAlteracao) VALUES('$v_codItem', '$nomeProdutoAntigo', '$canceladoAntigo', '$usuario', '$data');";
	$logNovo = "INSERT INTO log_item(idProduto, nomeProduto, cancelado, usuario, dataAlteracao) VALUES('$v_codItem', '$nomeProdutoAntigo', '$v_editar', '$usuario', '$data');";
	mysqli_query($conexao, $logAntigo) or die('Erro ao tentar cadastrar log antigo');
	mysqli_query($conexao, $logNovo) or die('Erro ao tentar cadastrar log novo');
	echo "<script>window.close();</script>";
}else{
	echo "Erro ao tentar adicionar Log de modificação do Item";
}


?>