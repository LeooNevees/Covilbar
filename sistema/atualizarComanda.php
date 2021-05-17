<?php
include('../v_login.php');

//COLETANDO DADOS DO FORMULÁRIO
$usuario = $_SESSION['usuario'];
$idComanda = filter_input(INPUT_POST, 'idComanda', FILTER_SANITIZE_NUMBER_INT);
$comanda = filter_input(INPUT_POST, 'comanda', FILTER_SANITIZE_NUMBER_INT);
$totalComanda = 0;
date_default_timezone_set('America/Sao_Paulo');
$data = date('Y-m-d');


//INICIO TRATATIVAS
if (empty($idComanda)) {
	echo "Faltando o ID da comanda. Por favor refaça o procedimento";
	return 0;
}else{
	$v_idComanda = trim($idComanda);
}

if (empty($comanda)) {
	echo "Faltando o número da comanda. Por favor refaça o procedimento";
	return 0;
}else{
	$v_comanda = trim($comanda);
}


$quantidadeLinhas = filter_input(INPUT_POST, $v_idComanda.'-quantidadeLinhas', FILTER_SANITIZE_NUMBER_INT);

if (empty($quantidadeLinhas)) {
	echo "Quantidade de Linhas faltando. Por favor refaça o procedimento";
	return 0;
}else{
	$v_quantidadeLinhas = trim($quantidadeLinhas);
}

echo 'Id Comanda: '.$v_idComanda.' - Comanda '.$v_comanda.' - Quantidade de Linhas: '.$v_quantidadeLinhas."<br>";

//CONEXAO COM BANCO DE DADOS 
include '../conexao.php';


$CLinha = 0;//LINHA PARA DIFERENCIAR DOS INPUTS EM BRANCO
//COLETANDO OS DADOS DAS LINHAS E FAZENDO A TRATATIVA
for ($i=1; $i <= $v_quantidadeLinhas; $i++) { 
	//TRATATIVA DE PRODUTOS
	$produto[$i] = filter_input(INPUT_POST, $v_idComanda.'-produto'.$i, FILTER_SANITIZE_STRING);
	if (empty($produto[$i])) {
		echo "Linha: ".$i." = Não possui nenhuma informação<br>";
		continue;
	}else{
		$codProduto = substr($produto[$i], 0, 8);
		//ANALISANDO SE O ITEM DA LINHA É UMA FICHA TÉCNICA, CASO SIM, É CANCELADO O LOOP
		$itemFicha = substr($produto[$i], 0, 1);
		if ($itemFicha == 9) {
			echo "Linha: ".$i." = É uma ficha técnica. Loop cancelado<br>";
			continue;
		}else{
			echo "Linha: ".$i." = Não é uma ficha técnica. Dando continuidade na validação<br>";
		}
		$CLinha = $CLinha + 1;
		$codProduto = substr($produto[$i], 0, 8);
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
	}
	

	//TRATATIVA DE QUANTIDADE
	$quantidade[$i] = filter_input(INPUT_POST, $v_idComanda.'-quantidade'.$i, FILTER_SANITIZE_STRING);
	if (empty($quantidade[$i])) {
		echo 'Faltando especificar a Quantidade na linha '.$i.'. Por favor retorne ao procedimento anterior e refaça. -'.$quantidade[$i];
		return 0;
	}else{
		$v_quantidade[$i] = trim($quantidade[$i]);
		$v_CQuantidade[$CLinha] = $v_quantidade[$i];
	}
		
	//TRATATIVA DO VALOR UNITARIO
	$valorUnitario[$i] = filter_input(INPUT_POST, $v_idComanda.'-valorUnitario'.$i, FILTER_SANITIZE_STRING);
		if (empty($valorUnitario[$i])) {
			echo 'Faltando Valor Unitário na linha '.$i.'. Por favor retorne ao procedimento anterior e refaça';
			return 0;
		}else{
			$v_valorUnitario[$i] = trim(str_replace('.', '', $valorUnitario[$i]));
			$validado_valorUnitario[$i] = trim(str_replace(',', '.', $v_valorUnitario[$i]));
			$v_CValidado_valorUnitario[$CLinha] = $validado_valorUnitario[$i];
		}

	//TRATATIVA DO VALOR TOTAL
		$validado_valor[$i] = $v_quantidade[$i] * $validado_valorUnitario[$i];
		$v_CValidado_valor[$CLinha] = $validado_valor[$i];

	echo 'Linha: '.$i.' = Produto: '.$v_codProduto[$i].' - Quantidade: '.$v_quantidade[$i].' - Valor Unitario: '.$validado_valorUnitario[$i].' - Total da Linha: '.$validado_valor[$i]."<br>";

}

//FAZENDO A TRATATIVA DAS FICHAS TÉCNICAS
echo "<br>Iniciando processo de validação das Fichas Técnicas<br>";
for ($i=1; $i <= $v_quantidadeLinhas; $i++) { 
	//TRATATIVA DE PRODUTOS
	$produto[$i] = filter_input(INPUT_POST, $v_idComanda.'-produto'.$i, FILTER_SANITIZE_STRING);
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
			echo "Linha: ".$i." = E uma ficha técnica. Dando continuidade no processo de validação<br>";
		}
		$CLinha = $CLinha + 1;
		$codProduto = substr($produto[$i], 0, 8);
		$tabelaOITT = "SELECT * FROM OITT WHERE idFicha = '$produto[$i]' AND cancelado = 'N';";
		$resultadoOITT = mysqli_query($conexao, $tabelaOITT);
		$linhaOITT = mysqli_fetch_assoc($resultadoOITT);
		if (empty($linhaOITT['idFicha'])) {
			echo "Ficha Técnica inválida ou CANCELADO, por favor retorne ao procedimento anterior e refaça";
			return 0;
		}else{
			$v_codProduto[$i] = trim($codProduto);
			$v_CProduto[$CLinha] = $v_codProduto[$i];
		}
	}
	

	//TRATATIVA DE QUANTIDADE
	$quantidade[$i] = filter_input(INPUT_POST, $v_idComanda.'-quantidade'.$i, FILTER_SANITIZE_STRING);
	if (empty($quantidade[$i])) {
		echo 'Faltando especificar a Quantidade na linha '.$i.'. Por favor retorne ao procedimento anterior e refaça. -'.$quantidade[$i];
		return 0;
	}else{
		$v_quantidade[$i] = trim($quantidade[$i]);
		$v_CQuantidade[$CLinha] = $v_quantidade[$i];
	}
		
	//TRATATIVA DO VALOR UNITARIO
	$valorUnitario[$i] = filter_input(INPUT_POST, $v_idComanda.'-valorUnitario'.$i, FILTER_SANITIZE_STRING);
		if (empty($valorUnitario[$i])) {
			echo 'Faltando Valor Unitário na linha '.$i.'. Por favor retorne ao procedimento anterior e refaça';
			return 0;
		}else{
			$v_valorUnitario[$i] = trim(str_replace('.', '', $valorUnitario[$i]));
			$validado_valorUnitario[$i] = trim(str_replace(',', '.', $v_valorUnitario[$i]));
			$v_CValidado_valorUnitario[$CLinha] = $validado_valorUnitario[$i];
		}

	//TRATATIVA DO VALOR TOTAL
		$validado_valor[$i] = $v_quantidade[$i] * $validado_valorUnitario[$i];
		$v_CValidado_valor[$CLinha] = $validado_valor[$i];

	echo 'Linha: '.$i.' = Produto: '.$v_codProduto[$i].' - Quantidade: '.$v_quantidade[$i].' - Valor Unitario: '.$validado_valorUnitario[$i].' - Total da Linha: '.$validado_valor[$i]."<br>";

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

echo "<br>";


echo "Iniciando processo de alteração da comanda na tabela OCMD<br>";
//ATUALIZANDO OS DADOS NA TABELA OCMD
$updateOCMD = "UPDATE OCMD SET valorTotal = '$totalNotaEntrada' WHERE idComanda = '$v_idComanda'; ";
$resultadoOCMD = mysqli_query($conexao, $updateOCMD);

if (mysqli_affected_rows($conexao) <= 0) {
	echo "Não precisou alterar o valor da Comanda na tabela OCMD<br>";
}else{
	echo "Valor da Comanda alterada com sucesso na tabela OCMD<br><br>";
}

//PROCESSO DE RESTAURAÇÃO
echo "Iniciando processo de salvamento de dados para caso precise de restauração<br>";
$tabelaAntigaCMD1 = "SELECT * FROM CMD1 WHERE idComanda = '$v_idComanda'; ";
$resultadoAntigaCMD1 = mysqli_query($conexao, $tabelaAntigaCMD1);
$numLinhaAntigaCMD1 = mysqli_affected_rows($conexao);
$numAntigo = 0;
while ($linhaAntigaCMD1 = mysqli_fetch_assoc($resultadoAntigaCMD1)) {
	$numAntigo = $numAntigo + 1;
	$idProdutoAntigoCMD1[$numAntigo] = $linhaAntigaCMD1['idProduto'];
	$quantidadeAntigoCMD1[$numAntigo] = $linhaAntigaCMD1['quantidade'];
	$valorUnitarioAntigoCMD1[$numAntigo] = $linhaAntigaCMD1['valorUnitario'];
	$totalLinhaAntigoCMD1[$numAntigo] = $linhaAntigaCMD1['totalLinha'];
	$dataCadastroAntigoCMD1[$numAntigo] = $linhaAntigaCMD1['dataCadastro'];
	$usuarioAntigoCMD1[$numAntigo] = $linhaAntigaCMD1['usuarioCadastro'];
	$canceladoAntigoCMD1[$numAntigo] = $linhaAntigaCMD1['cancelado'];
	echo "Linha ".$numAntigo." - Id Produto: ".$idProdutoAntigoCMD1[$numAntigo]." - Quantidade: ".$quantidadeAntigoCMD1[$numAntigo]." - ValorUnitario: ".$valorUnitarioAntigoCMD1[$numAntigo]." - Total Linha: ".$totalLinhaAntigoCMD1[$numAntigo]." - Data Cadastro: ".$dataCadastroAntigoCMD1[$numAntigo]." - usuario: ".$usuarioAntigoCMD1[$numAntigo]." - cancelado: ".$canceladoAntigoCMD1[$numAntigo]."<br>";
}
echo "Dados antigos capturados no banco de dados com sucesso<br>";


//INICIANDO DELETE DA CMD1 
echo "<br>Iniciando processo de DELETE na CMD1<br>";
$deleteCMD1 = "DELETE FROM CMD1 WHERE idComanda = '$v_idComanda';";
$resultadoDeleteCMD1 = mysqli_query($conexao, $deleteCMD1);
if (mysqli_affected_rows($conexao) <= 0) {
	echo "Erro ao deletar a comanda ".$v_idComanda." na tabela CMD1. Por favor refaça o procedimento<br>";
	return 0;
}else{
	echo "Comanda ".$v_idComanda." deletada com sucesso na tabela CMD1<br>";
}

//PROCESSO DE INSERT NA CMD1
echo "<br>Iniciando processo de INSERT na CMD1<br>";
for ($l=1; $l <= $numResultado; $l++) {
	$insertCMD1 = "INSERT INTO CMD1(idComanda, numLinha, idProduto, quantidade, valorUnitario, totalLinha, dataCadastro, usuarioCadastro, cancelado) VALUES('$v_idComanda', '$l', '$v_CProduto[$l]', '$v_CQuantidade[$l]', '$v_CValidado_valorUnitario[$l]', '$v_CValidado_valor[$l]', '$data', '$usuario', 'N');";
	$resultadoInsertCMD1 = mysqli_query($conexao, $insertCMD1);

	if (mysqli_affected_rows($conexao) <= 0) {
		echo "Erro ao inserir a linha ".$l."<br>";
		$nErro = mysqli_errno($conexao);
		$erro = mysqli_error($conexao);	
		echo 'Erro: '.$nErro.'-'.$erro."<br>";
		return 0;
	}else{
		echo "Sucesso ao cadastrar a linha ".$l." na tabela CMD1<br>";
	}
}

echo "Sucesso na atualização da OCMD e CMD1<br><br>";

//ANALISAR SE O ITEM INSERIDO NA TABELA JÁ ESTAVA ADICIONADO ANTERIORMENTE
$analiseTabelaCMD1 = "
SELECT idLinha, idComanda, numLinha, idProduto, SUM(quantidade) AS 'quantidade', valorUnitario, SUM(totalLinha) AS 'totalLinha'
FROM CMD1
WHERE idComanda = '$v_idComanda'
GROUP BY idProduto, valorUnitario;";

$resultadoAnaliseCMD1 = mysqli_query($conexao, $analiseTabelaCMD1);

$retornoLinhasAnalise = mysqli_affected_rows($conexao);

if ($retornoLinhasAnalise <= 0) {
	echo "Busca de dados na tabela CMD1 com o id da Comanda".$v_idComanda." interrompida<br>";
	$nErro = mysqli_errno($conexao);
	$erro = mysqli_error($conexao);	
	echo 'Erro: '.$nErro.'-'.$erro."<br>";
	return 0;
}

echo "RETORNANDO DADOS DO BANCO DE DADOS PARA UNIFICAR AS INFORMACOES <br>";
if ($numResultado != $retornoLinhasAnalise) {
	echo "Necessário atualizar os dados na tabela CMD1 devido ao GROUP BY do SELECT<br>";
	//FAZENDO UNIFICAÇÃO DO ITEM E A QUANTIDADE (COMO PARAMETRO O ID DO PRODUTO E O VALOR UNITARIO TEM QUE ESTAR IGUAIS)
	$numLinhaAnaliseCMD1 = 0;
	while ($linhaAnaliseCMD1 = mysqli_fetch_assoc($resultadoAnaliseCMD1)) {
		$numLinhaAnaliseCMD1 = $numLinhaAnaliseCMD1 + 1;
		$p_idProduto[$numLinhaAnaliseCMD1] = $linhaAnaliseCMD1['idProduto'];
		$p_quantidade[$numLinhaAnaliseCMD1] = $linhaAnaliseCMD1['quantidade'];
		$p_valorUnitario[$numLinhaAnaliseCMD1] = $linhaAnaliseCMD1['valorUnitario'];
		$p_totalLinha[$numLinhaAnaliseCMD1] = $linhaAnaliseCMD1['totalLinha'];

		echo "Linha: ".$numLinhaAnaliseCMD1." - Produto: ".$p_idProduto[$numLinhaAnaliseCMD1]." - Quantidade: ".$p_quantidade[$numLinhaAnaliseCMD1]." - Valor Unitario: ".$p_valorUnitario[$numLinhaAnaliseCMD1]." - Total Linha: ".$p_totalLinha[$numLinhaAnaliseCMD1]."<br>";
	}

	//DELETANDO OS DADOS DA TABELA PARA INSERIR OS NOVOS
	echo "Deletando os dados da tabela para inserir os novos<br>";
	$deleteCMD1 = "DELETE FROM CMD1 WHERE idComanda = '$v_idComanda';";
	$resultadoDeleteCMD1 = mysqli_query($conexao, $deleteCMD1);

	if (mysqli_affected_rows($conexao) <= 0) {
		echo "Erro ao deletar os dados da tabela CMD1<br>";
		$nErro = mysqli_errno($conexao);
		$erro = mysqli_error($conexao);	
		echo 'Erro: '.$nErro.'-'.$erro."<br>";
		return 0;
	}

	//INSERINDO OS NOVOS DADOS
	echo "Inserindo os novos dados<br>";
	for ($e=1; $e <= $retornoLinhasAnalise; $e++) { 
		$novaTabelaCMD1 = "INSERT INTO CMD1(idComanda, numLinha, idProduto, quantidade, valorUnitario, totalLinha, dataCadastro, usuarioCadastro, cancelado) VALUES('$v_idComanda', '$e', '$p_idProduto[$e]', '$p_quantidade[$e]', '$p_valorUnitario[$e]', '$p_totalLinha[$e]', '$data', '$usuario', 'N');";
		$resultadoNovaCMD1 = mysqli_query($conexao, $novaTabelaCMD1);

		if (mysqli_affected_rows($conexao) <= 0) {
			echo "Erro ao adicionar a linha ".$e.". Contate o administrador <br>";
			$nErro = mysqli_errno($conexao);
			$erro = mysqli_error($conexao);	
			echo 'Erro: '.$nErro.'-'.$erro."<br>";
			return 0;
		}else{
			echo "Linha ".$e." adicionada com sucesso <br>";
		}
	}

}else{
	echo "Não é necessário atualizar os dados na tabela CMD1 pois os dados são os mesmos<br>";
}

echo "Processo finalizado com sucesso";
echo "<script>window.close();</script>";


