<?php
include('../v_login.php');

//COLETANDO OS DADOS
$usuario = $_SESSION['usuario'];
$comanda = filter_input(INPUT_POST, 'comanda', FILTER_SANITIZE_NUMBER_INT);
$nomeCliente = filter_input(INPUT_POST, 'nomeCliente', FILTER_SANITIZE_STRING);
$horaEntrada = filter_input(INPUT_POST, 'horaEntrada', FILTER_SANITIZE_STRING);
$quantidadeLinhas = filter_input(INPUT_POST, 'quantidadeLinhas', FILTER_SANITIZE_NUMBER_INT);
$timeZone = date_default_timezone_set('America/Sao_Paulo');
$data = date('Y-m-d');
$dataAtual = date('Y-m-d H:i:s');
$totalComanda = 0;


//INICIO TRATATIVAS
if (empty($comanda)) {
	echo "Faltando o número da comanda. Por favor retorne a página anterior e refaça o procedimento";
	return 0;
}else{
	$v_comanda = trim($comanda);
}

if (empty($nomeCliente)) {
	$v_nomeCliente = null;
}else{
	$v_nomeCliente = mb_strtoupper(trim($nomeCliente));
}

if (empty($horaEntrada)) {
	$v_horaEntrada = $dataAtual;
}else{
	$hora = trim($horaEntrada);
	$v_horaEntrada = $data.' '.$hora;
}

if (empty($quantidadeLinhas)) {
	echo "Faltando ID: QuantidadeLinhas. Por favor contate o administrador do sistema";
	return 0;
}else{
	$v_quantidadeLinhas = trim($quantidadeLinhas);
}

echo 'Comanda:'.$v_comanda.' - Nome Cliente:'.$v_nomeCliente.' - Hora Entrada:'.$v_horaEntrada.' - qtde Linhas:'.$v_quantidadeLinhas."<br>";


//CONEXAO COM BANCO DE DADOS 
include '../conexao.php';

$CLinha = 0;
echo "<br>Iniciando coletas de dados das linhas e tratativas <br>";
//COLETANDO OS DADOS DAS LINHAS E FAZENDO A TRATATIVA
for ($i=1; $i <= $v_quantidadeLinhas; $i++) { 
	//TRATATIVA DE PRODUTOS
	$produto[$i] = filter_input(INPUT_POST, 'produto'.$i, FILTER_SANITIZE_STRING);
	$codProduto = substr($produto[$i], 0, 8);
	//ANALISANDO SE O ITEM DA LINHA É UMA FICHA TÉCNICA, CASO SIM, É CANCELADO O LOOP
		$itemFicha = substr($produto[$i], 0, 1);
		if ($itemFicha == 9) {
			echo "Linha: ".$i." = É uma ficha técnica. Cancelando LOOP<br>";
			continue;
		}else{
			echo "Linha: ".$i." = Não é uma ficha técnica. Dando continuidade na validação<br>";
		}
		$CLinha = $CLinha + 1;
		$tabelaOITM = "SELECT * FROM OITM WHERE idProduto = '$produto[$i]' AND cancelado = 'N';";
		$resultadoOITM = mysqli_query($conexao, $tabelaOITM);
		$linhaOITM = mysqli_fetch_assoc($resultadoOITM);
		if (empty($linhaOITM['idProduto'])) {
			echo "Código de item inválido ou CANCELADO, por favor retorne ao procedimento anterior e refaça";
			return 0;
		}else{
			$v_codProduto[$i] = trim($codProduto);
			$v_CProduto[$CLinha] = $v_codProduto[$i];
		}

	//TRATATIVA DE QUANTIDADE
	$quantidade[$i] = filter_input(INPUT_POST, 'quantidade'.$i, FILTER_SANITIZE_STRING);
	if (empty($quantidade[$i])) {
		echo 'Faltando especificar a Quantidade na linha '.$i.'. Por favor retorne ao procedimento anterior e refaça. -'.$quantidade[$i];
		return 0;
	}else{
		$v_quantidade[$i] = trim($quantidade[$i]);
		$v_CQuantidade[$CLinha] = $v_quantidade[$i];
	}
		
	//TRATATIVA DO VALOR UNITARIO
	$valorUnitario[$i] = filter_input(INPUT_POST, 'valorUnitario'.$i, FILTER_SANITIZE_STRING);
		if (empty($valorUnitario[$i])) {
			echo 'Faltando Valor Unitário na linha '.$i.'. Por favor retorne ao procedimento anterior e refaça';
			return 0;
		}else{
			$v_valorUnitario[$i] = trim(str_replace('.', '', $valorUnitario[$i]));
			$validado_valorUnitario[$i] = trim(str_replace(',', '.', $v_valorUnitario[$i]));
			$v_CValidado_valorUnitario[$CLinha] = $validado_valorUnitario[$i];
		}

	//TRATATIVA DO VALOR TOTAL
		echo "Iniciando processo da soma da linha <br>";
			$validado_valor[$i] = $v_quantidade[$i] * $validado_valorUnitario[$i];
			$v_CValidado_valor[$CLinha] = $validado_valor[$i];

	echo 'Linha:'.$i.' = Produto: '.$v_codProduto[$i].' - Quantidade: '.$v_quantidade[$i].' - Valor Unitario: '.$validado_valorUnitario[$i].' - Total da Linha: '.$validado_valor[$i]."<br>";

}

//INICIANDO PROCESSO DE VALIDAÇÃO DAS FICHAS TÉCNICAS
echo "<br>Iniciando processo de validação das Fichas Técnicas<br>";
//COLETANDO OS DADOS DAS LINHAS E FAZENDO A TRATATIVA
for ($i=1; $i <= $quantidadeLinhas; $i++) { 
	$produto[$i] = filter_input(INPUT_POST, 'produto'.$i, FILTER_SANITIZE_STRING);
	//TRATANDO AS LINHAS QUE FORAM EXCLUÍDAS NA NOTA
	if (empty($produto[$i])) {
		echo "Linha: ".$i." = Não possui nenhuma informação<br>";
		continue;
	}else{
		$codProduto = substr($produto[$i], 0, 8);
		//ANALISANDO SE O ITEM DA LINHA É UMA FICHA TÉCNICA, CASO SIM, É CANCELADO O LOOP
		$itemFicha = substr($produto[$i], 0, 1);
		if ($itemFicha != 9) {
			echo "Linha: ".$i." = Não é uma ficha técnica. Loop cancelado<br>";
			continue;
		}else{
			echo "Linha: ".$i." = É uma ficha técnica. Dando continuidade na validação<br>";
		}
		$CLinha = $CLinha + 1;
		$tabelaOITT = "SELECT * FROM OITT WHERE idFicha = '$produto[$i]' AND cancelado = 'N';";
		$resultadoOITT = mysqli_query($conexao, $tabelaOITT);
		$linhaOITT = mysqli_fetch_assoc($resultadoOITT);
		if (empty($linhaOITT['idFicha'])) {
			echo "Código de Ficha Técnica inválida, CANCELADO. Por favor refaça o procedimento anterior";
			return 0;
		}else{
			$v_codProduto[$i] = trim($codProduto);
			$v_CProduto[$CLinha] = $v_codProduto[$i];
		}
	}
	
	//TRATATIVA DE QUANTIDADE
	$quantidade[$i] = filter_input(INPUT_POST, 'quantidade'.$i, FILTER_SANITIZE_STRING);
	if (empty($quantidade[$i])) {
		echo 'Faltando especificar a Quantidade na linha '.$i.'. Por favor retorne ao procedimento anterior e refaça';
		return 0;
	}else{
		$v_quantidade[$i] = trim($quantidade[$i]);
		$v_CQuantidade[$CLinha] = $v_quantidade[$i];
	}

	//TRATATIVA DO VALOR UNITARIO
	$valorUnitario[$i] = filter_input(INPUT_POST, 'valorUnitario'.$i, FILTER_SANITIZE_STRING);
	if (empty($valorUnitario[$i])) {
		echo 'Faltando Valor Unitário na linha '.$i.'. Por favor retorne ao procedimento anterior e refaça';
		return 0;
	}else{
		$v_valorUnitario[$i] = trim(str_replace('.', '', $valorUnitario[$i]));
		$validado_valorUnitario[$i] = trim(str_replace(',', '.', $v_valorUnitario[$i]));
		$v_CValidado_valorUnitario[$CLinha] = $validado_valorUnitario[$i];
	}

	//TRATATIVA DO VALOR TOTAL
	echo "Iniciando processo de soma do valor total da linha<br>";
		$validado_valor[$i] = $v_quantidade[$i] * $validado_valorUnitario[$i];
		$v_CValidado_valor[$CLinha] = $validado_valor[$i];

	echo "Linha: ".$i." = Produto: ".$v_codProduto[$i]." - Quantidade: ".$v_quantidade[$i]." - Valor Unitario: ".$validado_valorUnitario[$i]. " - Valor Linha: ".$validado_valor[$i]."<br>";
}


//ANÁLISE DAS LINHAS QUE FORAM EXCLUÍDAS NA ETAPA ANTERIOR
$numResultado = sizeof($v_CProduto);
if ($numResultado == null || $numResultado == '') {
	echo "Erro na validação das linhas que foram excluídas. Por favor refaça o procedimento";
	return 0;
}

echo "<br>Qtde de linhas válidas (elimando as nulas): ".$numResultado."<br>";

echo "Nova Conferencia, constando apenas os itens que serão adicionados na CMD1 <br>";

for ($z=1; $z <= $numResultado ; $z++) { 
	echo "Linha: ".$z." = Produto: ".$v_CProduto[$z]." - Quantidade: ".$v_CQuantidade[$z]." - Valor Unitario: ".$v_CValidado_valorUnitario[$z]. " - Valor Linha: ".$v_CValidado_valor[$z]."<br>";
}

echo "<br>Iniciando análise do valor total da nota<br>";
$totalNotaEntrada = 0;
//BUSCANDO VALOR TOTAL DA NOTA
	echo "Iniciando processo de cálculo manual somado por linha<br>";
	for ($iu=1; $iu <= $numResultado; $iu++){
		$totalNotaEntrada = $totalNotaEntrada + $v_CValidado_valor[$iu];
		echo "Valor Linha ".$iu." = ".$v_CValidado_valor[$iu]."<br>";
	}
	echo "Valor total somado das linhas: ".$totalNotaEntrada."<br>";



echo "<br>Iniciando inserção de dados na tabela OCMD<br>";
//INSERINDO OS DADOS NA TABELA OCMD
$insertOCMD = "INSERT INTO ocmd(comanda, nomeCliente, horaEntrada, horaSaida, valorTotal, cortesia, statusPagamento, dataCadastro, statusEstoque, usuarioCadastro, cancelado) VALUES('$v_comanda', '$v_nomeCliente', '$v_horaEntrada', NULL, '$totalNotaEntrada', 'N', 'ABERTO','$data', 'ABERTO', '$usuario', 'N');";
$resultadoInsertOCMD = mysqli_query($conexao, $insertOCMD) or die("Descrição do erro: " . mysqli_error($conexao));

$ultIdComanda = mysqli_insert_id($conexao);

if (mysqli_affected_rows($conexao) <= 0) {
	echo "Erro ao tentar cadastrar Comanda na tabela OCMD<br>";
	echo "Descrição do erro: " . mysqli_error($conexao);
	return 0;
}else{
	echo "Comanda ".$v_comanda." inserida com sucesso na tabela OCMD<br>";
}

echo "<br>Iniciando inserção de dados na tabela CMD1<br>";
//INSERINDO OS DADOS NA TABELA CMD1
for ($l=1; $l <= $numResultado; $l++) { 

	$insertCMD1 = "INSERT INTO cmd1(idComanda, numLinha, idProduto, quantidade, valorUnitario, totalLinha, dataCadastro, usuarioCadastro, cancelado) VALUES('$ultIdComanda', '$l','$v_CProduto[$l]', '$v_CQuantidade[$l]', '$v_CValidado_valorUnitario[$l]', '$v_CValidado_valor[$l]', '$data', '$usuario', 'N');";
	$resultadoInsertCMD1 = mysqli_query($conexao, $insertCMD1);

	if (mysqli_affected_rows($conexao) <= 0) {
		echo "A linha ".$l." não pode ser inserida na tabela CMD1 </br>";
		//CASO NÃO SEJA ADICIONADO OS DADOS NA TABELA PCH1
		$deleteOCMD = "DELETE FROM OCMD WHERE idComanda = '$ultIdComanda';";
		$resultadoDeleteOCMD = mysqli_query($conexao, $deleteOCMD);
		if (mysqli_affected_rows($conexao) > 0) {
			echo "IdComanda: ".$ultIdComanda." excluído com sucesso da tabela OCMD</br>";
		}else{
			echo "IdComanda: ".$ultIdComanda." não foi excluído da tabela OCMD";
		}

		return 0;
	}else{
		echo "Linha ".$l." inserida com sucesso na tabela CMD1<br>";
	}
}

echo "<br>Iniciando inserção de dados na tabelga LOG_COMANDA<br>";
//INSERINDO OS DADOS NA TABELA LOG_COMANDA
$dataAlteracao = date('Y-m-d H:i:s');
$insertLogComanda = "INSERT INTO log_comanda(idComanda, statusPagamento, statusEstoque, usuario, dataAlteracao) VALUES('$ultIdComanda', 'ABERTO', 'ABERTO', '$usuario', '$dataAlteracao');";
$resultadoLogComanda = mysqli_query($conexao, $insertLogComanda);

if (mysqli_affected_rows($conexao) <= 0) {
	echo "Erro ao gerar o log da comanda";
	return 0;
}else{
	echo "Comanda ".$v_comanda." inserida com sucesso na tabela LOG_COMANDA<br>";
}

echo "<br>Procedimento encerrado com sucesso<br>";
echo "<script>window.close();</script>";
?>