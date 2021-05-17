<?php
include('../../v_login.php');

$usuario = $_SESSION['usuario'];
$nomeItem = filter_input(INPUT_POST, 'nomeItem', FILTER_SANITIZE_STRING);
$grupoItens = filter_input(INPUT_POST, 'grupoItens', FILTER_SANITIZE_NUMBER_INT);
$grupoUnidadeMedida = filter_input(INPUT_POST, 'unidadeMedida', FILTER_SANITIZE_NUMBER_INT);
$itemCompra = filter_input(INPUT_POST, 'itemCompra', FILTER_SANITIZE_STRING);
$itemVenda = filter_input(INPUT_POST, 'itemVenda', FILTER_SANITIZE_STRING);
$deposito = 1;
$usuario = $_SESSION['usuario'];
date_default_timezone_set('America/Sao_Paulo');
$data = date('Y-m-d');


//TRATATIVA DE DADOS
if (empty($nomeItem)) {
	echo "Por favor retorne a página anterior e insira o nome do item";
	return 0;
}else{
	$v_nomeItem = mb_strtoupper(trim($nomeItem));
}

if (empty($grupoItens)) {
	echo "Por favor retorne a página anterior e insira o grupo do item";
	return 0;
}else{
	$v_grupoItens = trim($grupoItens);
	$inicial = $v_grupoItens;
}

if (empty($grupoUnidadeMedida)) {
	echo "Por favor retorne a página anterior e insira a unidade de medida";
	return 0;
}else{
	$v_grupoUnidadeMedida = trim($grupoUnidadeMedida);
}

if (empty($itemCompra)) {
	$v_itemCompra = 'N';
}else if($itemCompra == 'on'){
	$v_itemCompra = 'Y';
}else{
	echo "Retorno inesperado no campo Item Compra. Por favor contate o administrador<br>";
	return 0;
}

if (empty($itemVenda)) {
	$v_itemVenda = 'N';
}else if($itemVenda == 'on'){
	$v_itemVenda = 'Y';
}else{
	echo "Retorno inesperado no campo Item Venda. Por favor contate o administrador<br>";
	return 0;
}

echo 'Item Compra:'.$v_itemCompra.' - Item Venda: '.$v_itemVenda;


//CONEXAO COM BANCO DE DADOS
include '../../conexao.php';


//BUSCANDO A TABELA UNIDADE DE MEDIDA EMBASADA NO GRUPO UNIDADE DE MEDIDA DO CADASTRO DO ITEM
$tabelaUnidadeMedida = "SELECT * FROM grupounidademedida WHERE idGrupoUnidadeMedida = '$v_grupoUnidadeMedida' ;";
$resultadoUnidadeMedida = mysqli_query($conexao, $tabelaUnidadeMedida);
if (mysqli_affected_rows($conexao) <= 0) {
	echo "Não encontrado unidade de Medida. Por favor refaça o procedimento<br>";
	return 0;
}else{
	echo "Encontrado unidade de Medida<br>";
}
$linhaUnidadeMedida = mysqli_fetch_assoc($resultadoUnidadeMedida);
$unidadeMedida = $linhaUnidadeMedida['idUnidadeMedida'];


//BUSCAR BD PARA TRAZER O ULTIMO ID EMBASADO NO TIPO DO ITEM
$tabelaBuscaProduto = "SELECT idProduto FROM OITM WHERE idGrupoItens = '$v_grupoItens' ORDER BY idProduto DESC LIMIT 1;";
$resultado = mysqli_query($conexao,$tabelaBuscaProduto);
$linha = mysqli_fetch_assoc($resultado);


//SEQUENCIAL DO ITEM
if (mysqli_affected_rows($conexao) > 0) {
	$ultimoId = $linha['idProduto'];

	$ultimas = substr($ultimoId, -7);

	$v_ultimas = $ultimas + 1;

	$contador = strlen($v_ultimas);

	if ($contador == 1) {
		$v_numeroItem = $inicial.'000000'.$v_ultimas;
	}else if ($contador == 2) {
		$v_numeroItem = $inicial.'00000'.$v_ultimas;
	}else if ($contador == 3) {
		$v_numeroItem = $inicial.'0000'.$v_ultimas;
	}else if ($contador == 4) {
		$v_numeroItem = $inicial.'000'.$v_ultimas;
	}else if ($contador == 5) {
		$v_numeroItem = $inicial.'00'.$v_ultimas;
	}else if ($contador == 6) {
		$v_numeroItem = $inicial.'0'.$v_ultimas;
	}else if ($contador == 7) {
		$v_numeroItem = $inicial.$v_ultimas;
	}
}else{
	$v_numeroItem = $inicial.'0000001';
}

echo "IdGrupoUnidadeMedida: ".$v_grupoUnidadeMedida." UnidadeMedida: ".$unidadeMedida."<br>";



//GRAVAR DADOS NA TABELA OITM
$tabelaOITM = "INSERT INTO OITM(idProduto, nomeProduto, idGrupoItens, idUnidadeMedida, idGrupoUnidadeMedida, itemCompra, itemVenda, usuarioCadastro, cancelado, dataCadastro) VALUES('$v_numeroItem', '$v_nomeItem', '$v_grupoItens', '$unidadeMedida', '$v_grupoUnidadeMedida', '$v_itemCompra', '$v_itemVenda', '$usuario', 'N', '$data');";
mysqli_query($conexao, $tabelaOITM) or die('Erro ao inserir os dados no banco de dados');

if (mysqli_affected_rows($conexao) <= 0) {
	echo "Erro ao tentar cadastrar Item na tabela OITM";
	return 0;
}

//GRAVAR OS DADOS NA TABELA OITW
$tabelaOITW = "INSERT INTO OITW(idProduto, idDeposito, quantidadeEstoque) VALUES('$v_numeroItem', '$deposito', 0);";
mysqli_query($conexao, $tabelaOITW);

if (mysqli_affected_rows($conexao) > 0) {
	echo "<script>window.close();</script>";
}else{
	echo "Erro ao tentar cadastrar Item na tabela OITW";
	return 0;
}
?>