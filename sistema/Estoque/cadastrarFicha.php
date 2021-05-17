<?php
include('../../v_login.php');

$usuario = $_SESSION['usuario'];
$nomeFicha = filter_input(INPUT_POST, 'nomeFicha', FILTER_SANITIZE_STRING);
$quantidadeLinhas = filter_input(INPUT_POST, 'quantidadeLinhas', FILTER_SANITIZE_NUMBER_INT);
$timeZone = date_default_timezone_set('America/Sao_Paulo');
$data = date('Y-m-d');
$dataeHora = date('Y-m-d H:i');


//INICIO TRATATIVAS
if (empty($nomeFicha)) {
	echo "Faltando o nome da Ficha Técnica. Por favor refaça o procedimento";
	return 0;
}else{
	$v_nomeFicha = mb_strtoupper(trim($nomeFicha));
}

//MOSTRANDO DADOS COLETADOS ATÉ O MOMENTO
echo "Usuário: ".$usuario. " - Nome da Ficha: ".$v_nomeFicha." - Quantidade Linhas: ".$quantidadeLinhas."<br><br>";

//CONEXAO COM BANCO DE DADOS 
include '../../conexao.php';

$CLinha = 0;//LINHA PARA DIFERENCIAR DOS INPUTS EM BRANCO

echo "Retorno a seguir referente aos dados das linhas na página anterior<br>";
//COLETANDO OS DADOS DAS LINHAS E FAZENDO A TRATATIVA
for ($i=1; $i <= $quantidadeLinhas; $i++) { 
	//TRATATIVA DE PRODUTOS
	$produto[$i] = filter_input(INPUT_POST, 'produto'.$i, FILTER_SANITIZE_STRING);
	//TRATANDO AS LINHAS QUE FORAM EXCLUÍDAS NA NOTA
	if (empty($produto[$i])) {
		echo "Linha: ".$i." = Não possui nenhuma informação<br>";
		continue;
	}else{
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
			}
		}

		echo "Linha: ".$i." = Produto: ".$v_codProduto[$i]." - Quantidade: ".$v_quantidade[$i]." - ID G UnidadeMedida: ".$v_idGUM[$i]."<br>";

}
echo "<br>";

//ANÁLISE DAS LINHAS QUE FORAM EXCLUÍDAS NA ETAPA ANTERIOR
$numResultado = sizeof($v_CProduto);
if ($numResultado == null || $numResultado == '') {
	echo "Erro na validação das linhas que foram excluídas. Por favor refaça o procedimento";
	return 0;
}

echo "Qtde de linhas válidas (elimando as nulas): ".$numResultado."<br>";

echo "Nova Conferencia, constando apenas os itens que serão adicionados na ITT1 <br>";

for ($z=1; $z <= $numResultado ; $z++) { 
	echo "Linha: ".$z." = Produto: ".$v_CProduto[$z]." - Quantidade: ".$v_CQuantidade[$z]." - Id Grupo Unidade Medida: ".$v_CIdGUM[$z]."<br>";
}


echo "<br>";


//INSERINDO OS DADOS NA TABELA OITT
$insertOITT = "INSERT INTO oitt(nomeFicha, dataCadastro, usuarioCadastro, cancelado) VALUES('$v_nomeFicha', '$data', '$usuario', 'N');";
$resultadoInsertOITT = mysqli_query($conexao, $insertOITT);

$ultIdEntrada = mysqli_insert_id($conexao);

if (mysqli_affected_rows($conexao) <= 0) {
	echo "Erro ao tentar cadastrar a Ficha Técnica na tabela OITT<br>";
	return 0;
}else{
	echo "Ficha ".$ultIdEntrada." inserida com sucesso na tabela OITT<br><br>";
}



//INSERINDO OS DADOS NA TABELA ITT1
for ($l=1; $l <= $numResultado; $l++) { 

	$insertITT1 = "INSERT INTO itt1(idFicha, numLinha, idProduto, quantidade, idGrupoUnidadeMedida, dataCadastro, usuarioCadastro, cancelado) VALUES('$ultIdEntrada', '$l','$v_CProduto[$l]', '$v_CQuantidade[$l]', '$v_CIdGUM[$l]', '$data', '$usuario', 'N');";
	$resultadoInsertITT1 = mysqli_query($conexao, $insertITT1);

	if (mysqli_affected_rows($conexao) <= 0) {
		echo "A linha ".$l." não pode ser inserida na tabela ITT1 </br>";
		//CASO NÃO SEJA ADICIONADO OS DADOS NA TABELA ITT1
		$deleteOITT = "DELETE FROM OITT WHERE idFicha = '$ultIdEntrada';";
		$resultadoDeleteOITT = mysqli_query($conexao, $deleteOITT);
		if (mysqli_affected_rows($conexao) > 0) {
			echo "idFicha: ".$ultIdEntrada." excluído com sucesso da tabela OITT<br>";
		}else{
			echo "idFicha: ".$ultIdEntrada." não foi excluído da tabela OITT<br>";
		}

		return 0;
	}else{
		echo "A linha ".$l." foi inserida na tabela ITT1 com sucesso<br>";
	}
}

//ADICIONANDO FICHA TECNICA NA TABELA DE LOG
echo "<br>Iniciando processo de INSERT na log_fichaTecnica<br>";
$insertLog = "INSERT INTO log_fichatecnica(idFicha, numLinhaProdutos, status, usuario, dataAlteracao) VALUES('$ultIdEntrada', '$numResultado', 'ATIVO', '$usuario', '$dataeHora');";
$resultadoInsertLog = mysqli_query($conexao, $insertLog);
if (mysqli_affected_rows($conexao) <= 0) {
	echo "Erro ao adicionar a ficha técnica na tabela log_fichaTecnica<br>";
	return 0;
}else{
	echo "Ficha técnica adicionada com sucesso na tabela log_fichaTecnica<br>";
}


echo "Ficha adicionada por completo no sistema<br>";
echo "<script>window.close();</script>";
?>