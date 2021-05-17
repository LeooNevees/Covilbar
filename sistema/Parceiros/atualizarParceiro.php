<?php
include('../../v_login.php');

$usuario = $_SESSION['usuario'];
date_default_timezone_set('America/Sao_Paulo');
$data = date('Y-m-d H:i:s');

$idParceiro = filter_input(INPUT_POST, 'idParceiro', FILTER_SANITIZE_STRING);
$nomeFantasia = filter_input(INPUT_POST, 'nomeFantasia', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING);
$sexo = filter_input(INPUT_POST, 'sexo', FILTER_SANITIZE_STRING);
$telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
$cep = filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_STRING);
$rua = filter_input(INPUT_POST, 'rua', FILTER_SANITIZE_STRING);
$numEndereco = filter_input(INPUT_POST, 'numEndereco', FILTER_SANITIZE_STRING);
$bairro = filter_input(INPUT_POST, 'bairro', FILTER_SANITIZE_STRING);
$cidade = filter_input(INPUT_POST, 'cidade', FILTER_SANITIZE_STRING);
$uf = filter_input(INPUT_POST, 'uf', FILTER_SANITIZE_STRING);
$status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

//VALIDANDO DADOS E CONVERTENDO PARA MAIUSCULAS
if (empty($idParceiro)) {
	echo "Por favor retorne a pagina anterior e refaça o procedimento";
	return 0;
}else{
	$v_idParceiro = trim($idParceiro);
}

if (empty($nomeFantasia)) {
	$v_nomeFantasia = null;
}else{
	$v_nomeFantasia = mb_strtoupper(trim($nomeFantasia));
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

if (empty($status)) {
	echo "Obrigatório inserir status do Parceiro de Negócio";
	return 0;
}else{
	$v_status = mb_strtoupper($status);
}


//TÉRMINO DA TRATATIVA

//CONEXÃO BANCO DE DADOS
include '../../conexao.php';

//ANÁLISE DADOS ANTIGOS
$tabelaLog = "SELECT * FROM OCRD WHERE idParceiro = '$v_idParceiro';";
$resultado = mysqli_query($conexao, $tabelaLog);

while($linha = mysqli_fetch_assoc($resultado)){
	$nomeFantasiaAntigo = $linha['nomeFantasia'];
	$sexoAntigo = $linha['sexo'];
	$emailAntigo = $linha['email'];
	$saldoAbertoAntigo = $linha['saldoAberto'];
	$enderecoAntigo = $linha['endereco'];
	$bairroAntigo = $linha['bairro'];
	$CEPAntigo = $linha['CEP'];
	$cidadeAntigo = $linha['cidade'];
	$telefoneAntigo = $linha['telefone'];
	$canceladoAntigo = $linha['cancelado'];
}

//GRAVAR DADOS NO BANCO
$tabela = "UPDATE OCRD SET nomeFantasia = '$v_nomeFantasia', sexo = '$v_sexo', email = '$v_email', endereco = '$v_rua', bairro = '$v_bairro', CEP = '$v_cep', cidade = '$v_cidade', telefone = '$v_telefone', cancelado = '$v_status' WHERE idParceiro = '$v_idParceiro';";
$insert = mysqli_query($conexao, $tabela) or die('Erro ao tentar gravar dados no banco');

if (mysqli_affected_rows($conexao) > 0) {
	$logAntigo = "INSERT INTO log_ParceiroNegocio(idParceiro, nomeFantasia, sexo, email, saldoAberto, endereco, bairro, CEP, cidade, telefone, cancelado, usuario, dataAlteracao) VALUES('$v_idParceiro', '$nomeFantasiaAntigo', '$sexoAntigo', '$emailAntigo', '$saldoAbertoAntigo', '$enderecoAntigo', '$bairroAntigo', '$CEPAntigo', '$cidadeAntigo', '$telefoneAntigo', '$canceladoAntigo', '$usuario', '$data');";

	$logNovo = "INSERT INTO log_ParceiroNegocio(idParceiro, nomeFantasia, sexo, email, saldoAberto, endereco, bairro, CEP, cidade, telefone, cancelado, usuario, dataAlteracao) VALUES('$v_idParceiro', '$v_nomeFantasia', '$v_sexo', '$v_email', '$saldoAbertoAntigo', '$v_rua', '$v_bairro', '$v_cep', '$v_cidade', '$v_telefone', '$v_status', '$usuario', '$data');";
	mysqli_query($conexao, $logAntigo);
	mysqli_query($conexao, $logNovo);
	echo "<script>window.close();</script>";
}else{
	echo "Erro ao tentar atualizar Parceiro";
}