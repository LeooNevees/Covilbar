<?php
include('../../v_login.php');

$usuario = $_SESSION['usuario'];
$idFicha = filter_input(INPUT_POST, 'idFicha', FILTER_SANITIZE_NUMBER_INT);
$nomeFicha = filter_input(INPUT_POST, 'nomeFicha', FILTER_SANITIZE_STRING);
$quantidadeLinhas = filter_input(INPUT_POST, 'quantidadeLinhas', FILTER_SANITIZE_NUMBER_INT);
$editar = filter_input(INPUT_POST, 'editar', FILTER_SANITIZE_STRING);
$timeZone = date_default_timezone_set('America/Sao_Paulo');
$data = date('Y-m-d');
$dataeHora = date('Y-m-d H:i');


//INICIO TRATATIVAS
if (empty($idFicha)) {
	echo "Faltando o ID da Ficha. Por favor refaça o procedimento<br>";
	return 0;
}else{
	$v_idFicha = trim($idFicha);
}

if (empty($nomeFicha)) {
	echo "Faltando o nome da Ficha Técnica. Por favor refaça o procedimento";
	return 0;
}else{
	$v_nomeFicha = mb_strtoupper(trim($nomeFicha));
}

if($editar == 'ativar'){
	$v_editar = 'N';
}else if($editar == 'inativar'){
	$v_editar = 'S';
}else{
	echo "Por favor refaça o procedimento novamente.";
	return 0;
}

//MOSTRANDO DADOS COLETADOS ATÉ O MOMENTO
echo "Usuário: ".$usuario. " - ID da Ficha: ".$v_idFicha. " - Nome da Ficha: ".$v_nomeFicha." - Quantidade Linhas: ".$quantidadeLinhas." - Status Cancelado: ".$v_editar."<br><br>";

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


//ALTERANDO OS DADOS NA TABELA OITT
echo "<br>Iniciando processo de alteração de status na OITT<br>";
$tabelaOITT = "SELECT * FROM OITT WHERE idFicha = '$v_idFicha';";
$resultadoOITT = mysqli_query($conexao, $tabelaOITT);
if (mysqli_affected_rows($conexao) <= 0) {
	echo "Não foram encontrados os dados da OITT. Por favor refaça o procedimento<br>";
	return 0;
}else{
	echo "Encontrado os dados da OITT<br>";
}
$linhaOITT = mysqli_fetch_assoc($resultadoOITT);
$statusAntigoOITT = $linhaOITT['cancelado'];
echo 'Status Antigo: '.$statusAntigoOITT."<br>";
echo "Status Novo: ".$v_editar."<br>";
if ($statusAntigoOITT != $v_editar) {
	echo "Necessário atualizar tabela OITT<br>";
	echo "Iniciando processo de atualização do status da ficha ".$v_idFicha."<br>";
	$updateOITT = "UPDATE OITT SET cancelado = '$v_editar' WHERE idFicha = '$v_idFicha';";
	$resultadoUpdateOITT = mysqli_query($conexao, $updateOITT);
	if (mysqli_affected_rows($conexao) <= 0) {
		echo "Erro ao atualizar o status da Ficha Técnica. Por favor refaça o procedimento<br>";
		return 0;
	}else{
		echo "Sucesso ao atualizar o status da Ficha Técnica<br>";
	}
}else{
	echo "Não é preciso atualizar a tabela OITT<br>";
}


echo "<br>Iniciando o processo de recuperação de dados caso precise<br>";
$tabelaAntigaITT1 = "SELECT * FROM ITT1 WHERE idFicha = '$v_idFicha';";
$resultadoAntigaITT1 = mysqli_query($conexao, $tabelaAntigaITT1);
if (mysqli_affected_rows($conexao) <= 0) {
	echo "Não foi encontrado nenhuma linha na ITT1 para salvar<br>";
	return 0;
}

//SALVANDO OS DADOS ANTIGOS EM VARIAVES
$contLinha = 0;
while ($linhaAntigaITT1 = mysqli_fetch_assoc($resultadoAntigaITT1)) {
	$contLinha = $contLinha + 1;
	$idProdutoAntigo[$contLinha] = $linhaAntigaITT1['idProduto'];
	$quantidadeAntigo[$contLinha] = $linhaAntigaITT1['quantidade'];
	$unidadeMedidaAntigo[$contLinha] = $linhaAntigaITT1['idGrupoUnidadeMedida'];
	$dataCadastroAntigo[$contLinha] = $linhaAntigaITT1['dataCadastro'];
	$usuarioCadastroAntigo[$contLinha] = $linhaAntigaITT1['usuarioCadastro'];
	$canceladoAntigo[$contLinha] = $linhaAntigaITT1['cancelado'];
	echo 'Linha '.$contLinha.' = IdProduto: '.$idProdutoAntigo[$contLinha].' - Quantidade: '.$quantidadeAntigo[$contLinha].' - Unidade Medida: '.$unidadeMedidaAntigo[$contLinha].' - Data Cadastro: '.$dataCadastroAntigo[$contLinha].' - usuarioCadastro: '.$usuarioCadastroAntigo[$contLinha].' - Cancelado: '.$canceladoAntigo[$contLinha]."<br>";
}

echo "Dados salvos com sucesso<br>";

//INICIANDO PROCESSO DE DELETE DA TABELA ITT1
echo "<br>Iniciando processo de DELETE ITT1<br>";
$deleteITT1 = "DELETE FROM ITT1 WHERE idFicha = '$v_idFicha';";
$resultadoDeleteITT1 = mysqli_query($conexao, $deleteITT1);
if (mysqli_affected_rows($conexao) <= 0) {
	echo "Não foi possível excluir a ficha ".$v_idFicha." da tabela ITT1. Por favor refaça o procedimento <br>";
	return 0;
}else{
	echo "Ficha ".$v_idFicha." excluída com sucesso na tabela ITT1<br>";
}

//INSERINDO OS DADOS NA TABELA ITT1
echo "<br>Iniciando processo de INSERT ITT1<br>";
for ($l=1; $l <= $numResultado; $l++) { 

	$insertITT1 = "INSERT INTO itt1(idFicha, numLinha, idProduto, quantidade, idGrupoUnidadeMedida, dataCadastro, usuarioCadastro, cancelado) VALUES('$v_idFicha', '$l','$v_CProduto[$l]', '$v_CQuantidade[$l]', '$v_CIdGUM[$l]', '$data', '$usuario', 'N');";
	$resultadoInsertITT1 = mysqli_query($conexao, $insertITT1);

	if (mysqli_affected_rows($conexao) <= 0) {
		echo "A linha ".$l." não pode ser inserida na tabela ITT1 </br>";
		//CASO NÃO SEJA ADICIONADO OS DADOS NA TABELA ITT1
		echo "Iniciando processo de restauração<br>";
		for ($u=1; $u <= $contLinha; $u++) { 
			$insertAntigoITT1 = "INSERT INTO itt1(idFicha, numLinha, idProduto, quantidade, idGrupoUnidadeMedida, dataCadastro, usuarioCadastro, cancelado) VALUES('$v_idFicha', '$u','$idProdutoAntigo[$u]', '$quantidadeAntigo[$u]', '$unidadeMedidaAntigo[$u]', '$dataCadastroAntigo[$u]', '$usuarioCadastroAntigo[$u]', '$canceladoAntigo[$u]');";
			$resultadoInsertAntigoITT1 = mysqli_query($conexao, $insertAntigoITT1);
			if (mysqli_affected_rows($conexao) <= 0) {
				echo "Erro ao inserir o item ".$idProdutoAntigo[$u]." antigo na tabela ITT1. Por favor contate o administrador<br>";
				return 0;
			}else{
				echo "Sucesso ao inserir o item ".$idProdutoAntigo[$u]." antigo na tabela ITT1. Por favor contate o administrador<br>";
			}
		}
		return 0;
	}else{
		echo "Sucesso ao inserir o item ".$v_CProduto[$l]." na tabela ITT1<br>";
	}
}

//ADICIONANDO FICHA TECNICA NA TABELA DE LOG
if ($v_editar == 'S') {
	$v_novoStatus = 'CANCELADO';
}else{
	$v_novoStatus = 'ATIVO';
}
echo "<br>Iniciando processo de INSERT na log_fichaTecnica<br>";
$insertLog = "INSERT INTO log_fichatecnica(idFicha, numLinhaProdutos, status, usuario, dataAlteracao) VALUES('$v_idFicha', '$numResultado', '$v_novoStatus', '$usuario', '$dataeHora');";
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