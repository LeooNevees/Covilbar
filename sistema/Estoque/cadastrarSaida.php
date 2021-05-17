<?php
include('../../v_login.php');

$usuario = $_SESSION['usuario'];
$dataLancamento = filter_input(INPUT_POST, 'dataLancamento', FILTER_SANITIZE_STRING);
$codigoFornecedor = filter_input(INPUT_POST, 'codigoFornecedor', FILTER_SANITIZE_STRING);
$dataVencimento = filter_input(INPUT_POST, 'dataVencimento', FILTER_SANITIZE_STRING);
$deposito = filter_input(INPUT_POST, 'deposito', FILTER_SANITIZE_STRING);
$parcelas = filter_input(INPUT_POST, 'parcelas', FILTER_SANITIZE_STRING);
$quantidadeLinhas = filter_input(INPUT_POST, 'quantidadeLinhas', FILTER_SANITIZE_NUMBER_INT);
$dataLancamento = filter_input(INPUT_POST, 'dataLancamento', FILTER_SANITIZE_STRING);
$finalizarDia = filter_input(INPUT_GET, 'finalizarDia', FILTER_SANITIZE_NUMBER_INT);
$dataHora = filter_input(INPUT_POST, 'dataeHora', FILTER_SANITIZE_STRING);
$timeZone = date_default_timezone_set('America/Sao_Paulo');
$data = date('Y-m-d');
$totalNotaEntrada = 0;

echo "Quantidade de Linhas retornadas da Saida de Mercadoria: ".$quantidadeLinhas."<br><br>";

//INICIO TRATATIVAS
if (empty($dataLancamento)) {
	$v_dataLancamento = null;
	echo "Faltando a data de lançamento. Por favor retorne a página anterior e refaça o procedimento";
	return 0;
}else{
	$v_dataLancamento = trim($dataLancamento);
}

if (empty($codigoFornecedor)) {
	$v_codigoFornecedor = null;
	echo "Faltando o código do fornecedor. Por favor retorne a página anterior e refaça o procedimento";
	return 0;
}else{
	$v_codigoFornecedor = trim(substr($codigoFornecedor, 0, 6));
}

if (empty($dataVencimento)) {
	$v_dataVencimento = null;
	echo "Faltando a data de vencimento. Por favor retorne a página anterior e refaça o procedimento";
	return 0;
}else{
	$v_dataVencimento = trim($dataVencimento);
}

if (empty($deposito)) {
	$v_deposito = null;
	echo "Faltando o depósito. Por favor retorne a página anterior e refaça o procedimento";
	return 0;
}else{
	$v_deposito = trim($deposito);
}

if (empty($parcelas)) {
	$v_parcelas = null;
	echo "Faltando as Parcelas. Por favor retorne a página anterior e refaça o procedimento";
	return 0;
}else{
	$v_parcelas = trim($parcelas);
}

//FAZENDO A TRATATIVA PARA QUANDO FOR FINALIZAÇÃO DE DIA
if (empty($dataHora)) {
	$v_dataeHora = null;
}else{
	$v_dataeHora = trim($dataHora);
}

if (empty($finalizarDia)) {
	$v_finalizarDia = null;
}else{
	$v_finalizarDia = trim($finalizarDia);
	if ($v_dataeHora == null) {
		echo "FINALIZANDO DIA - Faltando data de fechamento. Por favor refaça o procedimento anterior.";
		return 0;
	}

}

//MOSTRANDO DADOS COLETADOS ATÉ O MOMENTO
echo "Usuário: ".$usuario. " - Data de Lançamento: ".$v_dataLancamento." - Código do Fornecedor: ".$v_codigoFornecedor." - Data de Vencimento: ".$v_dataVencimento." - Depósito: ".$v_deposito." - Parcelas: ".$v_parcelas." - Finalizar Dia: ".$v_finalizarDia." - DataeHora: ".$v_dataeHora."<br><br>";


//CONEXAO COM BANCO DE DADOS 
include '../../conexao.php';

//VALIDANDO OS DADOS DO PARCEIRO DE NEGÓCIO
$tabelaPN = "SELECT * FROM OCRD WHERE idParceiro = '$v_codigoFornecedor' AND cancelado = 'N';";
$resultadoPN = mysqli_query($conexao, $tabelaPN);
$linhaPN = mysqli_fetch_assoc($resultadoPN);

if (empty($linhaPN['idParceiro'])) {
	echo "PN não cadastrado ou CANCELADO. Por favor refaça o procedimento anterior com os dados corretos<br><br>";
	return 0;
}else{
	echo "Encontrado Parceiro de Negócio com sucesso<br><br>";
}

$CLinha = 0;//LINHA PARA DIFERENCIAR DOS INPUTS EM BRANCO

echo "Retorno a seguir referente aos dados das linhas na página anterior<br>";
echo "Iniciando análise dos Produto<br>";

//COLETANDO OS DADOS DAS LINHAS E FAZENDO A TRATATIVA
for ($i=1; $i <= $quantidadeLinhas; $i++) { 
	//TRATATIVA DE PRODUTOS
	$produto[$i] = filter_input(INPUT_POST, 'produto'.$i, FILTER_SANITIZE_STRING);
	//TRATANDO AS LINHAS QUE FORAM EXCLUÍDAS NA NOTA
	if (empty($produto[$i])) {
		echo "Linha: ".$i." = Não possui nenhuma informação<br>";
		continue;
	}else{
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
		$tabelaOITM = "SELECT * FROM OITM WHERE idProduto = '$produto[$i]' AND cancelado = 'N' AND itemVenda = 'Y';";
		$resultadoOITM = mysqli_query($conexao, $tabelaOITM);
		$linhaOITM = mysqli_fetch_assoc($resultadoOITM);
		if (empty($linhaOITM['idProduto'])) {
			echo "Código de item inválido, CANCELADO ou não é um item de Venda. Por favor refaça o procedimento anterior";
			return 0;
		}else{
			$v_codProduto[$i] = trim($codProduto);
			$v_CProduto[$CLinha] = $v_codProduto[$i];
		}
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

	//TRATATIVA DA UNIDADE DE MEDIDA
	$grupoUnidadeMedida[$i] = mb_strtoupper(filter_input(INPUT_POST, 'grupoUnidadeMedida'.$i, FILTER_SANITIZE_STRING));
		//TRAZER O CÓDIGO DA UNIDADE DE MEDIDA DO BANCO PARA POSSÍVEL VALIDAÇÃO A PARTIR DO NOME DA UM.
		$tabelaUM = "SELECT * FROM grupoUnidadeMedida WHERE nomeUnidade = '$grupoUnidadeMedida[$i]' AND cancelado = 'N'; ";
		$resultadoUM = mysqli_query($conexao, $tabelaUM);
		$linhaUM = mysqli_fetch_assoc($resultadoUM);
		//TRATATIVA DA UNIDADE DE MEDIDA
		if (empty($linhaUM['idGrupoUnidadeMedida'])) {
			echo "Unidade de Medida inexistente ou CANCELADO. Por favor refaça o procedimento.";
			return 0;
		}else{
			$v_idUM = $linhaUM['idUnidadeMedida'];
			$v_idGUM[$i] = $linhaUM['idGrupoUnidadeMedida'];
			$v_idUnidadeMedidaOITM = $linhaOITM['idUnidadeMedida'];
			$v_CIdGUM[$CLinha] = $v_idGUM[$i];

			if ($v_idUM != $v_idUnidadeMedidaOITM) {
				echo "Unidade de medida da linha ".$i." diferente da UM do item. Por favor refaça o procedimento";
				return 0;
			}else{
				//TRATATIVA DA QUANTIDADE TOTAL
				$qtdeConvertidaUM = (double)$linhaUM['valorConvertido'];
				$qtdeTotal[$i] = ($v_quantidade[$i] * $qtdeConvertidaUM);
				$v_qtdeTotal[$i] = trim((double) $qtdeTotal[$i]);
				$v_CQtdeTotal[$CLinha] = $v_qtdeTotal[$i];
				//VALOR ABAIXO CONVERTIDO PARA QUANDO FOR ADICIONADO UM NOVO ITEM SEM LANÇAMENTO DE ESTOQUE ANTERIOR
				$v_qtdeTotalConvertido[$i] = $v_qtdeTotal[$i] * -1;
			}
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
		$validado_valor[$i] = $v_quantidade[$i] * $validado_valorUnitario[$i];
		$v_CValidado_valor[$CLinha] = $validado_valor[$i];

		echo "Linha: ".$i." = Produto: ".$v_codProduto[$i]." - Quantidade: ".$v_quantidade[$i]." - ID G UnidadeMedida: ".$v_idGUM[$i]." - Quantidade Convertida: ".$v_qtdeTotal[$i]." - Valor Unitario: ".$validado_valorUnitario[$i]. " - Valor Linha: ".$validado_valor[$i]." - Quantidade Convertida: ".$v_qtdeTotalConvertido[$i]."<br>";

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

	//TRATATIVA DA UNIDADE DE MEDIDA
	$grupoUnidadeMedida[$i] = mb_strtoupper(filter_input(INPUT_POST, 'grupoUnidadeMedida'.$i, FILTER_SANITIZE_STRING));
	if (empty($grupoUnidadeMedida[$i])) {
		echo "Faltando a Unidades de Medida na linha ".$i.". Por favor refaça o procedimento<br>";
		return 0;
	}else{
		$v_grupoUnidadeMedida[$i] = trim($grupoUnidadeMedida[$i]);
		if ($v_grupoUnidadeMedida[$i] != 'UN') {
			echo "Unidade de Medida para ficha técnica tem que ser UNIDADE. Por favor refaça o procedimento<br>";
			return 0;
		}else{
			echo "Unidade da ficha técnica: ".$v_grupoUnidadeMedida[$i]."<br>";
		}
	}
		//TRAZER O CÓDIGO DA UNIDADE DE MEDIDA DO BANCO PARA POSSÍVEL VALIDAÇÃO A PARTIR DO NOME DA UM.
		$tabelaUM = "SELECT * FROM grupoUnidadeMedida WHERE nomeUnidade = '$grupoUnidadeMedida[$i]' AND cancelado = 'N'; ";
		$resultadoUM = mysqli_query($conexao, $tabelaUM);
		$linhaUM = mysqli_fetch_assoc($resultadoUM);
		//TRATATIVA DA UNIDADE DE MEDIDA
		if (empty($linhaUM['idGrupoUnidadeMedida'])) {
			echo "Unidade de Medida inexistente ou CANCELADO. Por favor refaça o procedimento.";
			return 0;
		}else{
			$v_idUM = $linhaUM['idUnidadeMedida'];
			$v_idGUM[$i] = $linhaUM['idGrupoUnidadeMedida'];
			$v_CIdGUM[$CLinha] = $v_idGUM[$i];

			//TRATATIVA DA QUANTIDADE TOTAL
			$qtdeConvertidaUM = (double)$linhaUM['valorConvertido'];
			$qtdeTotal[$i] = ($v_quantidade[$i] * $qtdeConvertidaUM);
			$v_qtdeTotal[$i] = trim((double) $qtdeTotal[$i]);
			$v_CQtdeTotal[$CLinha] = $v_qtdeTotal[$i];
			//VALOR ABAIXO CONVERTIDO PARA QUANDO FOR ADICIONADO UM NOVO ITEM SEM LANÇAMENTO DE ESTOQUE ANTERIOR
			$v_qtdeTotalConvertido[$i] = $v_qtdeTotal[$i] * -1;
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
		$v_valorTotal[$i] = $v_quantidade[$i] * $validado_valorUnitario[$i];
		$v_valorTotal[$i] = trim(str_replace('.', '', $v_valorTotal[$i]));
		$validado_valor[$i] = str_replace(',', '.', $v_valorTotal[$i]);
		$v_CValidado_valor[$CLinha] = $validado_valor[$i];

		echo "Linha: ".$i." = Produto: ".$v_codProduto[$i]." - Quantidade: ".$v_quantidade[$i]." - ID G UnidadeMedida: ".$v_idGUM[$i]." - Quantidade Convertida: ".$v_qtdeTotal[$i]." - Valor Unitario: ".$validado_valorUnitario[$i]. " - Valor Linha: ".$validado_valor[$i]."<br>";

}



//ANÁLISE DAS LINHAS QUE FORAM EXCLUÍDAS NA ETAPA ANTERIOR
$numResultado = sizeof($v_CProduto);
if ($numResultado == null || $numResultado == '') {
	echo "Erro na validação das linhas que foram excluídas. Por favor refaça o procedimento";
	return 0;
}

echo "<br>Qtde de linhas válidas (elimando as nulas): ".$numResultado."<br>";

echo "Nova Conferencia, constando apenas os itens que serão adicionados na PCH1 <br>";

for ($z=1; $z <= $numResultado ; $z++) { 
	echo "Linha: ".$z." = Produto: ".$v_CProduto[$z]." - Quantidade: ".$v_CQuantidade[$z]." - Id Grupo Unidade Medida: ".$v_CIdGUM[$z]." - Quantidade Convertida: ".$v_CQtdeTotal[$z]." - Valor Unitario: ".$v_CValidado_valorUnitario[$z]. " - Valor Linha: ".$v_CValidado_valor[$z]."<br>";
}

echo "<br>Iniciando análise do valor total da nota<br>";
//BUSCANDO VALOR TOTAL DA NOTA
echo "Iniciando processo de cálculo manual somado por linha<br>";
for ($iu=1; $iu <= $numResultado; $iu++){
	$totalNotaEntrada = $totalNotaEntrada + $v_CValidado_valor[$iu];
	echo "Valor Linha: ".$v_CValidado_valor[$iu]."<br>";
}
echo "Valor total somado das linhas: ".$totalNotaEntrada."<br>";


//INICIANDO ANÁLISE DAS PARCELAS CONTIDAS NA NOTA
echo "<br>Iniciando processo de análise dos vencimentos das parcelas<br>";

$valorParcela = $totalNotaEntrada/$v_parcelas;

echo "Valor Parcela: ".$valorParcela."<br>";

//COLETANDO OS DADOS DOS VENCIMENTOS DAS PARCELAS
for ($c=1; $c <= $v_parcelas; $c++) { 
	$dataVencimentoParc[$c] = filter_input(INPUT_POST, 'dataVencimentoParc'.$c, FILTER_SANITIZE_STRING);
	if ($dataVencimentoParc[$c] == null || $dataVencimentoParc[$c] == '') {
		echo "Data de vencimento da parcela ".$c." é inválido. Por favor retorne na página anterior e insira a data";
		return 0;
	}
	echo "Parcela ".$c." - ".$dataVencimentoParc[$c]."<br>";
}
echo "<br>";

//INSERINDO OS DADOS NA TABELA OINV
$insertOINV = "INSERT INTO OINV(idParceiroNegocio, idDeposito, dataLancamento, dataCadastro, dataVencimento, status, parcelas, valorTotal, usuarioCadastro, cancelado) VALUES('$v_codigoFornecedor', '$v_deposito', '$v_dataLancamento', '$data', '$v_dataVencimento', 'ABERTO', '$v_parcelas', '$totalNotaEntrada', '$usuario', 'N');";
$resultadoInsertOINV = mysqli_query($conexao, $insertOINV) or die('Erro ao cadastrar os dados na tabela OINV');


$ultIdEntrada = mysqli_insert_id($conexao);

if (mysqli_affected_rows($conexao) <= 0) {
	echo "Erro ao tentar cadastrar Saida de Mercadoria na tabela OINV<br>";
	return 0;
}else{
	echo "Nota ".$ultIdEntrada." inserida com sucesso na tabela OINV<br><br>";
}


//INSERINDO OS DADOS NA TABELA INV1
for ($l=1; $l <= $numResultado; $l++) {

	$insertINV1 = "INSERT INTO INV1(idSaida, numLinha, idProduto, quantidadeSaida, idGrupoUnidadeMedida, valorUnitario, totalLinha, quantidadeConvertida, idDeposito, dataCadastro, usuarioCadastro, cancelado) VALUES('$ultIdEntrada', '$l','$v_CProduto[$l]', '$v_CQuantidade[$l]', '$v_CIdGUM[$l]', '$v_CValidado_valorUnitario[$l]', '$v_CValidado_valor[$l]', '$v_CQtdeTotal[$l]', '$v_deposito', '$data', '$usuario', 'N');";
	$resultadoInsertINV1 = mysqli_query($conexao, $insertINV1);

	if (mysqli_affected_rows($conexao) <= 0) {
		echo "A linha ".$l." não pode ser inserida na tabela INV1 </br>";
		//CASO NÃO SEJA ADICIONADO OS DADOS NA TABELA INV1
		$deleteOINV = "DELETE FROM deleteOINV WHERE idSaida = '$ultIdEntrada';";
		$resultadoDeleteOINV = mysqli_query($conexao, $deleteOINV);
		if (mysqli_affected_rows($conexao) > 0) {
			echo "idSaida: ".$ultIdEntrada." excluído com sucesso da tabela OINV<br>";
		}else{
			echo "idSaida: ".$ultIdEntrada." não foi excluído da tabela OINV<br>";
		}

		return 0;
	}else{
		echo "A linha ".$l." foi inserida na tabela INV1 com sucesso<br>";
	}
}
echo "<br>";

//INSERINDO OS DADOS NA TABELA PCH2
for ($k=1; $k <= $v_parcelas; $k++) { 
	$insertINV2 = "INSERT INTO inv2(idSaida, parcela, totalParcelas, valorParcela, status, dataVencParcela, dataCadastro, usuarioCadastro, cancelado) VALUES('$ultIdEntrada', '$k', '$v_parcelas', '$valorParcela', 'ABERTO', '$dataVencimentoParc[$k]', '$data', '$usuario', 'N');";
	$resultadoInsertINV2 = mysqli_query($conexao, $insertINV2);

	if (mysqli_affected_rows($conexao) <= 0) {
		echo "A linha ".$k." não pode ser inserida na tabela INV2 </br>";
		//CASO NÃO SEJA ADICIONADO OS DADOS NA TABELA INV2
		$deleteOINV = "DELETE FROM deleteOINV WHERE idSaida = '$ultIdEntrada';";
		$resultadoDeleteOINV = mysqli_query($conexao, $deleteOINV);
		if (mysqli_affected_rows($conexao) > 0) {
			echo "idSaida: ".$ultIdEntrada." excluído com sucesso da tabela OINV</br>";
		}else{
			echo "idSaida: ".$ultIdEntrada." não foi excluído da tabela OINV";
		}

		$deleteINV1 = "DELETE FROM INV1 WHERE idSaida = '$ultIdEntrada';";
		$resultadoDeleteINV1 = mysqli_query($conexao, $deleteINV1);
		if (mysqli_affected_rows($conexao) > 0) {
			echo "idSaida: ".$ultIdEntrada." excluído com sucesso da tabela INV1</br>";
		}else{
			echo "idSaida: ".$ultIdEntrada." não foi excluído da tabela INV1";
		}
		return 0;
	}else{
		echo "A linha ".$k." foi inserida na tabela INV2 com sucesso<br>";
	}
}


echo "<br>Coletando os dados da tabela OITW para salvar por precaução<br>";

//COLETANDO DADOS CASO DE PROBLEMA NA ATUALIZAÇÃO DOS ITENS NA OITW NO PRÓXIMO PASSO
$tabelaAntigaOITW = "SELECT * FROM OITW";
$resultadoAntigaOITW = mysqli_query($conexao, $tabelaAntigaOITW);
$numRetornoOITW = mysqli_affected_rows($conexao);
echo "Retornou ".$numRetornoOITW." linhas ta tabela OITW<br>";
$a = 0;
while ($linhaRetornoOITW = mysqli_fetch_assoc($resultadoAntigaOITW)) {
	$a = $a + 1;
	$idProdutoAntigoOITW[$a] = $linhaRetornoOITW['idProduto'];
	$quantidadeEstoqueAntigoOITW[$a] = $linhaRetornoOITW['quantidadeEstoque'];
	echo "Linha:".$a." - ".$idProdutoAntigoOITW[$a]." = ".$quantidadeEstoqueAntigoOITW[$a]."<br>";
}


echo "<br>Iniciando processo de atualização da tabela OITW (estoque)<br>";

//INSERINDO OS DADOS NA TABELA OITW
echo "Iniciando processo de análise de item<br>";
for ($o=1; $o <= $numResultado; $o++) { 
	//PROCESSO DE ANÁLISE DA FICHA TÉCNICA
	$v_itemFicha = substr($v_CProduto[$o], 0, 1);
	if ($v_itemFicha == 9) {
		echo "Linha: ".$o." = O item é uma ficha técnica<br>";
		$tabelaITT1 = "SELECT * FROM ITT1 WHERE idFicha = '$v_CProduto[$o]' ORDER BY numLinha ASC;";
		$resultadoITT1 = mysqli_query($conexao, $tabelaITT1);
		if (mysqli_affected_rows($conexao) <= 0) {
			echo "Não encontrado ".$v_CProduto[$o]." na tabela ITT1. Por favor reveja a Ficha<br>";
			//CASO NÃO SEJA ADICIONADO OS DADOS NA TABELA OITW
			echo "Iniciando processo de restauração da tabela OINV<br>";
			$deleteOINV = "DELETE FROM OINV WHERE idSaida = '$ultIdEntrada';";
			$resultadoDeleteOINV = mysqli_query($conexao, $deleteOINV);
			if (mysqli_affected_rows($conexao) > 0) {
				echo "idSaida: ".$ultIdEntrada." excluído com sucesso da tabela OINV<br>";
			}else{
				echo "idSaida: ".$ultIdEntrada." não foi excluído da tabela OINV<br>";
			}

			echo "Iniciando processo de restauração da tabela INV1<br>";
			$deleteINV1 = "DELETE FROM INV1 WHERE idSaida = '$ultIdEntrada';";
			$resultadoDeleteINV1 = mysqli_query($conexao, $deleteINV1);
			if (mysqli_affected_rows($conexao) > 0) {
				echo "idSaida: ".$ultIdEntrada." excluído com sucesso da tabela INV1</br>";
			}else{
				echo "idSaida: ".$ultIdEntrada." não foi excluído da tabela INV1";
			}

			echo "Iniciando processo de restauração da tabela INV2<br>";
			$deleteINV2 = "DELETE FROM INV2 WHERE idSaida = '$ultIdEntrada';";
			$resultadoDeleteINV2 = mysqli_query($conexao, $deleteINV2);
			if (mysqli_affected_rows($conexao) > 0) {
				echo "idSaida: ".$ultIdEntrada." excluído com sucesso da tabela INV2</br>";
			}else{
				echo "idSaida: ".$ultIdEntrada." não foi excluído da tabela INV2";
			}

			echo "<br>Iniciando processo de restauração da tabela OITW<br>";
			for ($m=1; $m <= $numRetornoOITW; $m++) { 
				$updateAjusteEstoque = "UPDATE OITW SET quantidadeEstoque = '$quantidadeEstoqueAntigoOITW[$m]' WHERE idProduto = '$idProdutoAntigoOITW[$m]';";
				$resultadoUpdateAjuste = mysqli_query($conexao, $updateAjusteEstoque);
				if (mysqli_affected_rows($conexao) <= 0) {
					echo "Não foi preciso atualizar o item: ".$idProdutoAntigoOITW[$m]."<br>";
				}else{
					echo "Item: ".$idProdutoAntigoOITW[$m]." atualizado com sucesso <br>";
				}
			}
			return 0;	
		}else{
			echo "Encontrado ".$v_CProduto[$o]." na tabela ITT1<br>";
			echo "Iniciando busca dos itens que compoe a Ficha técnica<br>";
			while ($linhaFicha = mysqli_fetch_assoc($resultadoITT1)) {
				$idProdutoFicha[$o] = $linhaFicha['idProduto'];
				$quantidadeFicha[$o] = $linhaFicha['quantidade'];
				echo "Item ".$linhaFicha['numLinha']." = idProduto: ".$idProdutoFicha[$o]." - Quantidade: ".$quantidadeFicha[$o]."<br>";
				echo "Iniciando SELECT do item na tabela OITW<br>";
				$tabelaFichaOITW = "SELECT quantidadeEstoque FROM OITW WHERE idProduto = '$idProdutoFicha[$o]';";
				$resultadoFichaOITW = mysqli_query($conexao, $tabelaFichaOITW);
				if (mysqli_affected_rows($conexao) <= 0) {
					echo "Não foi encontrado o item. Por favor contate o administrador<br>";
					return 0;
				}else{
					echo "Encontrado item na tabela OITW<br>";
					$linhaAntigaOITW = mysqli_fetch_assoc($resultadoFichaOITW);
					$antigoQTDE = $linhaAntigaOITW['quantidadeEstoque'];
					$novoQTDE = $antigoQTDE - ($v_CQuantidade[$o] * $quantidadeFicha[$o]);
					echo "Quantidade antiga: ".$antigoQTDE." - Quantidade Nova: ".$novoQTDE."<br>"; 
					echo "Iniciando UPDATE<br>";
					$updateFichaOITW = "UPDATE OITW SET quantidadeEstoque = '$novoQTDE' WHERE idProduto = '$idProdutoFicha[$o]';";
					$resultadoUpdateFichaOITW = mysqli_query($conexao, $updateFichaOITW);
					if (mysqli_affected_rows($conexao) <= 0) {
						echo "Erro ao atualizar o estoque do item ".$idProdutoFicha[$o].". Por favor contate o administrador<br>";
						//CASO NÃO SEJA ADICIONADO OS DADOS NA TABELA OITW
						echo "Iniciando processo de restauração da tabela OINV<br>";
						$deleteOINV = "DELETE FROM OINV WHERE idSaida = '$ultIdEntrada';";
						$resultadoDeleteOINV = mysqli_query($conexao, $deleteOINV);
						if (mysqli_affected_rows($conexao) > 0) {
							echo "idSaida: ".$ultIdEntrada." excluído com sucesso da tabela OINV<br>";
						}else{
							echo "idSaida: ".$ultIdEntrada." não foi excluído da tabela OINV<br>";
						}

						echo "Iniciando processo de restauração da tabela INV1<br>";
						$deleteINV1 = "DELETE FROM INV1 WHERE idSaida = '$ultIdEntrada';";
						$resultadoDeleteINV1 = mysqli_query($conexao, $deleteINV1);
						if (mysqli_affected_rows($conexao) > 0) {
							echo "idSaida: ".$ultIdEntrada." excluído com sucesso da tabela INV1</br>";
						}else{
							echo "idSaida: ".$ultIdEntrada." não foi excluído da tabela INV1";
						}

						echo "Iniciando processo de restauração da tabela INV2<br>";
						$deleteINV2 = "DELETE FROM INV2 WHERE idSaida = '$ultIdEntrada';";
						$resultadoDeleteINV2 = mysqli_query($conexao, $deleteINV2);
						if (mysqli_affected_rows($conexao) > 0) {
							echo "idSaida: ".$ultIdEntrada." excluído com sucesso da tabela INV2</br>";
						}else{
							echo "idSaida: ".$ultIdEntrada." não foi excluído da tabela INV2";
						}

						echo "<br>Iniciando processo de restauração da tabela OITW<br>";
						for ($m=1; $m <= $numRetornoOITW; $m++) { 
							$updateAjusteEstoque = "UPDATE OITW SET quantidadeEstoque = '$quantidadeEstoqueAntigoOITW[$m]' WHERE idProduto = '$idProdutoAntigoOITW[$m]';";
							$resultadoUpdateAjuste = mysqli_query($conexao, $updateAjusteEstoque);
							if (mysqli_affected_rows($conexao) <= 0) {
								echo "Erro ao atualizar o item: ".$idProdutoAntigoOITW[$m]."<br>";
							}else{
								echo "Item: ".$idProdutoAntigoOITW[$m]." atualizado com sucesso <br>";
							}
						}

						return 0;
					}else{
						echo "Sucesso ao atualizar o item ".$idProdutoFicha[$o]."<br>";
					}
				}

			}
		}
	}else{
		//INICIO PROCESSO DE ITENS QUE NÃO SÃO FICHAS TÉCNICAS
		echo "Linha: ".$o." = O item não é ficha técnica<br>";
		$tabelaOITW = "SELECT * FROM OITW WHERE idProduto = '$v_CProduto[$o]';";
		$resultadoOITW = mysqli_query($conexao, $tabelaOITW);
		if (mysqli_affected_rows($conexao) > 0) {
		echo "Item já cadastrado na tabela OITW. Iniciando processo de UPDATE<br>";
		$linhaOITW = mysqli_fetch_assoc($resultadoOITW);
		$v_quantidadeEstoque = $linhaOITW['quantidadeEstoque'];
		$v_novoEstoque = ($v_quantidadeEstoque - $v_CQtdeTotal[$o]);
		echo "Estoque antigo do item: ".$v_quantidadeEstoque." - Estoque Novo: ".$v_novoEstoque."<br>";
		$updateOITW = "UPDATE oitw SET quantidadeEstoque = '$v_novoEstoque' WHERE idProduto = '$v_CProduto[$o]';";
		$resultadoUpdateOITW = mysqli_query($conexao, $updateOITW);

		if (mysqli_affected_rows($conexao) <= 0) {
			echo "Erro ao atualizar a tabela OITW do item ".$v_CProduto[$o]." na linha ".$o."<br>";
			//CASO NÃO SEJA ADICIONADO OS DADOS NA TABELA OITW
			echo "Iniciando processo de restauração da tabela OINV<br>";
			$deleteOINV = "DELETE FROM OINV WHERE idSaida = '$ultIdEntrada';";
			$resultadoDeleteOINV = mysqli_query($conexao, $deleteOINV);
			if (mysqli_affected_rows($conexao) > 0) {
				echo "idSaida: ".$ultIdEntrada." excluído com sucesso da tabela OINV<br>";
			}else{
				echo "idSaida: ".$ultIdEntrada." não foi excluído da tabela OINV<br>";
			}

			echo "Iniciando processo de restauração da tabela INV1<br>";
			$deleteINV1 = "DELETE FROM INV1 WHERE idSaida = '$ultIdEntrada';";
			$resultadoDeleteINV1 = mysqli_query($conexao, $deleteINV1);
			if (mysqli_affected_rows($conexao) > 0) {
				echo "idSaida: ".$ultIdEntrada." excluído com sucesso da tabela INV1</br>";
			}else{
				echo "idSaida: ".$ultIdEntrada." não foi excluído da tabela INV1";
			}

			echo "Iniciando processo de restauração da tabela INV2<br>";
			$deleteINV2 = "DELETE FROM INV2 WHERE idSaida = '$ultIdEntrada';";
			$resultadoDeleteINV2 = mysqli_query($conexao, $deleteINV2);
			if (mysqli_affected_rows($conexao) > 0) {
				echo "idSaida: ".$ultIdEntrada." excluído com sucesso da tabela INV2</br>";
			}else{
				echo "idSaida: ".$ultIdEntrada." não foi excluído da tabela INV2";
			}

			echo "<br>Iniciando processo de restauração da tabela OITW<br>";
			for ($m=1; $m <= $numRetornoOITW; $m++) { 
				$updateAjusteEstoque = "UPDATE OITW SET quantidadeEstoque = '$quantidadeEstoqueAntigoOITW[$m]' WHERE idProduto = '$idProdutoAntigoOITW[$m]';";
				$resultadoUpdateAjuste = mysqli_query($conexao, $updateAjusteEstoque);
				if (mysqli_affected_rows($conexao) <= 0) {
					echo "Erro ao atualizar o item: ".$idProdutoAntigoOITW[$m]."<br>";
				}else{
					echo "Item: ".$idProdutoAntigoOITW[$m]." atualizado com sucesso <br>";
				}
			}

			return 0;
		}
		echo "Atualizado o item ".$v_CProduto[$o]." na tabela OITW com sucesso<br>";
	}else{
		echo "Item não cadastrado na tabela OITW. Iniciando processo de INSERT<br>";
		$insertOITW = "INSERT INTO oitw(idProduto, idDeposito, quantidadeEstoque) VALUES('$v_CProduto[$o]', 1, '$v_qtdeTotalConvertido[$o]');";
		$resultadoInsertOITW = mysqli_query($conexao, $insertOITW);
		if (mysqli_affected_rows($conexao) <= 0) {
			echo "Erro ao adicionar quantidade do item ".$v_CProduto[$o]." na tabela OITW";
			//CASO NÃO SEJA ADICIONADO OS DADOS NA TABELA OITW
			echo "Iniciando processo de restauração da tabela OINV<br>";
			$deleteOINV = "DELETE FROM OINV WHERE idSaida = '$ultIdEntrada';";
			$resultadoDeleteOINV = mysqli_query($conexao, $deleteOINV);
			if (mysqli_affected_rows($conexao) > 0) {
				echo "idSaida: ".$ultIdEntrada." excluído com sucesso da tabela OINV<br>";
			}else{
				echo "idSaida: ".$ultIdEntrada." não foi excluído da tabela OINV<br>";
			}

			echo "Iniciando processo de restauração da tabela INV1<br>";
			$deleteINV1 = "DELETE FROM INV1 WHERE idSaida = '$ultIdEntrada';";
			$resultadoDeleteINV1 = mysqli_query($conexao, $deleteINV1);
			if (mysqli_affected_rows($conexao) > 0) {
				echo "idSaida: ".$ultIdEntrada." excluído com sucesso da tabela INV1</br>";
			}else{
				echo "idSaida: ".$ultIdEntrada." não foi excluído da tabela INV1";
			}

			echo "Iniciando processo de restauração da tabela INV2<br>";
			$deleteINV2 = "DELETE FROM INV2 WHERE idSaida = '$ultIdEntrada';";
			$resultadoDeleteINV2 = mysqli_query($conexao, $deleteINV2);
			if (mysqli_affected_rows($conexao) > 0) {
				echo "idSaida: ".$ultIdEntrada." excluído com sucesso da tabela INV2</br>";
			}else{
				echo "idSaida: ".$ultIdEntrada." não foi excluído da tabela INV2";
			}

			echo "<br>Iniciando processo de restauração da tabela OITW<br>";
			for ($m=1; $m <= $numRetornoOITW; $m++) { 
				$updateAjusteEstoque = "UPDATE OITW SET quantidadeEstoque = '$quantidadeEstoqueAntigoOITW[$m]' WHERE idProduto = '$idProdutoAntigoOITW[$m]';";
				$resultadoUpdateAjuste = mysqli_query($conexao, $updateAjusteEstoque);
				if (mysqli_affected_rows($conexao) <= 0) {
					echo "Erro ao atualizar o item: ".$idProdutoAntigoOITW[$m]."<br>";
				}else{
					echo "Item: ".$idProdutoAntigoOITW[$m]." atualizado com sucesso <br>";
				}
			}

			return 0;
		}else{
			echo "Adicionado quantidade do item ".$v_CProduto[$o]." na tabela OITW com sucesso<br>";
		}
	}
	}	
}
echo "Nota adicionada por completo no sistema<br>";

if ($v_finalizarDia == 1) {
	//INICIO PROCESSO DE FINALIZAÇÃO DE COMANDA
	echo "<br>Iniciando processo de finalização das comanda <br>";

	$tabelaFaturamentoFechado = "SELECT T0.idComanda FROM OCMD T0 WHERE T0.cancelado = 'N' AND T0.horaEntrada >= '$v_dataeHora';";
	$resultadoFaturamentoFechado = mysqli_query($conexao, $tabelaFaturamentoFechado);
	$numRetFaturamento = mysqli_affected_rows($conexao);
	echo "Obteve ".$numRetFaturamento." comandas da OCMD que serão finalizadas<br>";

	$nFat = 0;
	while ($linhaRetFaturamento = mysqli_fetch_assoc($resultadoFaturamentoFechado)) {
		$nFat = $nFat + 1;
		$idComandaFaturamento[$nFat] = $linhaRetFaturamento['idComanda'];
		echo $idComandaFaturamento[$nFat]."<br>";
	}

	echo "Iniciando UPDATE na tabela OCMD para status FINALIZADO<br>";

	for ($rt=1; $rt <= $numRetFaturamento; $rt++) { 
		$updateFaturamento = "UPDATE OCMD SET statusEstoque = 'FINALIZADO' WHERE idComanda = '$idComandaFaturamento[$rt]';";
		$resultadoUpdateFat = mysqli_query($conexao, $updateFaturamento);
		//INICIO ANALISANDO SE DEU CERTO UPDATE, CASO NÃO IRÁ REVERTER O STATUS DAS COMANDAS
		if (mysqli_affected_rows($conexao) <= 0) {
			echo "Erro ao atualizar a comanda ".$idComandaFaturamento[$rt].". Por favor contate o administrador<br>";
			echo "Iniciando processo de restauração das comandas já atualizadas anteriormente<br>";
			for ($rest=1; $rest <= $numRetFaturamento; $rest++) { 
				$updateRestFat = "UPDATE OCMD SET statusEstoque = 'ABERTO' WHERE idComanda = '$idComandaFaturamento[$rest]';";
				$resultadoUpdateRest = mysqli_query($conexao, $updateRestFat);
				if (mysqli_affected_rows($conexao) <= 0) {
					echo "Não foi necessário atualizar a comanda ".$idComandaFaturamento[$rest]."<br>";
				}else{
					echo "Comanda ".$idComandaFaturamento[$rest]." atualizada com sucesso <br>";
				}
			}
			return 0;
		}else{
			echo "Sucesso ao atualizar a comanda ".$idComandaFaturamento[$rt]."<br>";
		}
	}

	//PROCESSO DE BACKUP SERÁ UTILIZADO APENAS APÓS A INSERÇÃO DA NOTA FISCAL DE SAÍDA--
	echo "Iniciando processo de backup<br>";

	$str = exec('start /B C:\Users\leozi\Documents\CovilBar\backupAutomatico.bat');

	echo "Backup realizado com sucesso. Por favor confira a pasta CovilBar<br>";

	echo "Procedimento finalizado com sucesso<br>";
	echo "Processo de atualização de comanda na OCMD finalizado com sucesso<br>";
}


echo "<script>window.close();</script>";
?>