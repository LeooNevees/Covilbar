<?php
include('../../v_login.php');

$usuario = $_SESSION['usuario'];
$nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
$nomeFantasia = filter_input(INPUT_POST, 'nomeFantasia', FILTER_SANITIZE_STRING);
$tipo = filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING);
$sexo = filter_input(INPUT_POST, 'sexo', FILTER_SANITIZE_STRING);
$numDocumento = filter_input(INPUT_POST, 'numDocumento', FILTER_SANITIZE_STRING);
$telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
$cep = filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_STRING);
$rua = filter_input(INPUT_POST, 'rua', FILTER_SANITIZE_STRING);
$numEndereco = filter_input(INPUT_POST, 'numEndereco', FILTER_SANITIZE_STRING);
$bairro = filter_input(INPUT_POST, 'bairro', FILTER_SANITIZE_STRING);
$cidade = filter_input(INPUT_POST, 'cidade', FILTER_SANITIZE_STRING);
$uf = filter_input(INPUT_POST, 'uf', FILTER_SANITIZE_STRING);
date_default_timezone_set('America/Sao_Paulo');
$data = date('Y-m-d');

//VALIDANDO DADOS E CONVERTENDO PARA MAIUSCULAS
if (empty($nome)) {
	echo "Por favor retorne a pagina anterior e insira o nome";
	return 0;
}else{
	$v_nome = mb_strtoupper(trim($nome));
}

if (empty($nomeFantasia)) {
	$v_nomeFantasia = null;
}else{
	$v_nomeFantasia = mb_strtoupper(trim($nomeFantasia));
}

if (empty($tipo)) {
	echo "Por favor retorne a pagina anterior e insira o TIPO do PN";
	return 0;
}else{
	$v_tipo = mb_strtoupper(trim($tipo));
}

if (empty($email)) {
	$v_email = null;
}else{
	$v_email = mb_strtoupper(trim($email));
}

if (empty($sexo)) {
	$v_sexo = null;
}else{
	$v_sexo = mb_strtoupper(trim($sexo));
}

if (empty($numDocumento)) {
	$v_numDocumento = null;
}else{
	$v_numDocumento = trim($numDocumento);
}

if (strlen($telefone) < 3) {
	$v_telefone = null;
}else{
	$v_telefone = trim($telefone);
}

if (empty($cep)) {
	$v_cep = null;
}else{
	$v_cep = trim($cep);
}

if (empty($rua)) {
	$v_rua = null;
}else{
	$v_rua = mb_strtoupper(trim($rua));
}

if (empty($numEndereco)) {
	$v_numEndereco = null;
}else{
	$v_numEndereco = trim($numEndereco);
}

if (empty($bairro)) {
	$v_bairro = null;
}else{
	$v_bairro = mb_strtoupper(trim($bairro));
}

if (empty($cidade)) {
	$v_cidade = null;
}else{
	$v_cidade = mb_strtoupper(trim($cidade));
}

if (empty($uf)) {
	$v_uf = null;
}else{
	$v_uf = mb_strtoupper(trim($uf));
}

if ($v_tipo == 'FORNECEDOR') {
	$inicial = 'F';
}else {
	$inicial = 'C';
}
//TÉRMINO DA TRATATIVA

//CONEXÃO BANCO DE DADOS
include '../../conexao.php';

//BUSCAR BD PARA SABER SE JÁ FOI CADASTRADO PN MESMO TIPO E MESMO NUMERO DE DOCUMENTO
$NumeroDocumentoDuplicado = "SELECT * FROM OCRD WHERE numeroDocumento = '$v_numDocumento' AND tipo = '$v_tipo';";
$resultadoNumero = mysqli_query($conexao, $NumeroDocumentoDuplicado);
$DocDuplicado = mysqli_fetch_assoc($resultadoNumero);

if (!empty($DocDuplicado['idParceiro'])) {
	echo "Parceiro já cadastrado.";
	echo '<br>Id Parceiro = '.$DocDuplicado['idParceiro'].' - Tipo: '.$DocDuplicado['tipo'];
	return 0;	
}

//BUSCAR BD PARA TRAZER O ULTIMO ID EMBASADO NO TIPO DO PN
$tabelaBuscaParceiro = "SELECT idParceiro FROM OCRD WHERE tipo = '$v_tipo' ORDER BY idParceiro DESC LIMIT 1;";
$resultado = mysqli_query($conexao,$tabelaBuscaParceiro);
$linha = mysqli_fetch_assoc($resultado);

if (!empty($ultimoId = $linha['idParceiro'])) {
	//SEQUENCIAL DO PARCEIRO DE NEGOCIO
	$ultimas = substr($ultimoId, -5);

	$v_ultimas = $ultimas + 1;

	$contador = strlen($v_ultimas);

	if ($contador == 1) {
		$v_idParceiro = $inicial.'0000'.$v_ultimas;
	}else if ($contador == 2) {
		$v_idParceiro = $inicial.'000'.$v_ultimas;
	}else if ($contador == 3) {
		$v_idParceiro = $inicial.'00'.$v_ultimas;
	}else if ($contador == 4) {
		$v_idParceiro = $inicial.'0'.$v_ultimas;
	}else if ($contador == 5) {
		$v_idParceiro = $inicial.$v_ultimas;
	}
}else{
	$v_idParceiro = $inicial.'00001';
}

//GRAVAR DADOS NO BANCO
$tabela = "INSERT INTO OCRD(idParceiro, nomeParceiro, nomeFantasia, tipo, sexo, email, saldoAberto, numeroDocumento, endereco, numEndereco, bairro, CEP, cidade, uf, telefone, usuarioCadastro, cancelado, data_cadastro) VALUES('$v_idParceiro','$v_nome', '$v_nomeFantasia', '$v_tipo', '$v_sexo', '$v_email', 0, '$v_numDocumento', '$v_rua', '$v_numEndereco', '$v_bairro', '$v_cep', '$v_cidade', '$v_uf', '$v_telefone', '$usuario', 'N', '$data');";
$insert = mysqli_query($conexao, $tabela) or die('Erro ao tentar gravar dados no banco');

if (mysqli_affected_rows($conexao) > 0) {
	echo "<script>window.close();</script>";
}else{
	echo "Erro ao tentar cadastrar Parceiro";
}