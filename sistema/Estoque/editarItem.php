<?php
include('../../v_login.php');

$item = filter_input(INPUT_POST, 'nomeItemAlt', FILTER_SANITIZE_STRING);
$codItem = trim(substr($item, 0, 8));

//CONEXAO BANCO DE DADOS
include '../../conexao.php';

$tabela = "
SELECT T0.idProduto AS 'idProduto', T0.nomeProduto AS 'nomeProduto', T1.nomeGrupo AS 'nomeGrupo', T2.obsUnidadeMedida AS 'obsUnidadeMedida', T2.codUnidadeMedida AS 'codUnidadeMedida', T3.quantidadeEstoque AS 'quantidadeEstoque', T0.itemCompra AS 'itemCompra', T0.itemVenda AS 'itemVenda', T0.cancelado AS 'cancelado'
FROM OITM T0

INNER JOIN grupoitens T1
ON T0.idGrupoItens = T1.idGrupoItens

INNER JOIN unidademedida T2
ON T0.idUnidadeMedida = T2.idUnidadeMedida

INNER JOIN oitw T3
ON T0.idProduto = T3.idProduto

WHERE T0.idProduto = '$codItem';";

$resultado = mysqli_query($conexao, $tabela);
if (mysqli_affected_rows($conexao) <= 0) {
    echo "Produto não encontrado. Por favor refaça o procedimento";
    return 0;
}
$linha = mysqli_fetch_assoc($resultado);

?>

<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <!-- ICONE NA BARRA DO NAVEGADOR-->
        <link rel="shortcut icon" href="../../img/zillaMonstro.png">

        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Covil Bar</title>

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
        <link rel="stylesheet" href="styleEstoque.css">

        <!-----ICONES------------>
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <script src="https://kit.fontawesome.com/70c48f08c7.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

        <script type="text/javascript">

    	function validarItem() {
    		var finalizar = 0;

	    	if (document.getElementById('numero').value > 0) {
	    		alert('Por favor faça toda a saída do item para inativá-lo.');
	    		return false;
	    		finalizar = 1;
	    	}

	    	var statusAtual = document.getElementById('statusAtual').value;
	    	var editar = document.getElementById('editar').value;
	    	if (editar == 'inativar') {
	    		var statusMod = 'S';
	    	}else{
	    		var statusMod = 'N';
	    	}

	    	if (statusAtual == statusMod) {
	    		alert('Impossível continuar. Status antigo do item igual ao novo status');
	    		finalizar = 1;
	    		return false;
	    	}

	    	if(finalizar == 0){
				alert("Alterando Item");
				setTimeout(function() {
		    		window.close();
				}, 500);
			}
	    }
        </script>

    </head>
    <body class="fundo1">
        <!-- NAVBAR -->
        <div> 
            <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #8B0000">
                <a href="../index.php"><img src="../../img/covilFaturamento.png" height="50px" width="110px"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item afastamentoNavBar">
                            <a class="nav-link" href="../index.php"> Início <span class="sr-only">(current)</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../Parceiros/index.php">Parceiros</a>
                        </li>
                        <li class="nav-item dropdown active">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Estoque
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="entrada.php"><font color="blue">Entrada</font></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="saida.php"><font color="blue">Saida</font></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="cadastroItem.php"><font color="blue">Cadastro do Item</font></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../Estoque/fichaTecnica.php"><font color="blue">Ficha Técnica</font></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="deposito.php"><font color="blue">Depósitos</font></a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Financeiro
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="../Financeiro/contasPagar.php"><font color="black">Contas a Pagar</font></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../Estoque/saida.php"><font color="black">Contas a Receber</font></a>
                            </div>
                        </li>
                    </ul>
                    <form class="form-inline my-2 my-lg-0">
                        <div>
                            <input class="form-control mr-sm-2" type="search" placeholder="Procurar" aria-label="Procurar">
                            <button class="btn btn-outline-light my-2 my-sm-0" type="submit">OK</button>&nbsp;&nbsp;&nbsp;
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><font size="2"><?php echo strtolower($_SESSION['usuario']) ?></font></button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="../../logout.php">Logout</a>
                            </div>
                        </div>   
                    </form>
                </div>
            </nav>
        </div>

        <!--FIM NAVBAR-->

        <!--INICIO SEPARADOR-->
        <div>
            <ul class="nav nav-tabs justify-content-center">
                <li class="nav-item">
                    <a class="nav-link active" id="v-pills-profile-tab" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false" title="Pesquisar Parceiro"><i class="fa fa-search" style="color: red;"></i></a>
                </li>
            </ul>
        </div>
        <!--FIM SEPARADOR-->

        <!--INICIO ATUALIZAR ITEM-->
        <div class="tab-content" id="v-pills-tabContent">
            <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                <div class="CadastroItemMargem">
                    <img src="../../img/covilLogin.png" width="200px;" height="140px;" style="display: block; margin-left: auto; margin-right: auto;">
                    <h1 style="text-align: center;"><font color="yellow">Editar dados do Item</font></h1><br><br>
                    <form class="needs-validation" method="POST" action="atualizarItem.php" target="_blank">
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for="inputZip"><font color="yellow">Código do Item <font color="red">*</font></b></font></font></label>
                                <input type="text" class="form-control" id="id" name="id" value="<?php echo $linha['idProduto'] ?>" readonly>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="inputCity"><font color="yellow">Nome do Item <font color="red">*</font></b></font></font></label>
                                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $linha['nomeProduto'] ?>" readonly>
                            </div>

                            <div class="form-group col-md-2">
                                <label for="inputCity"><font color="yellow">Em Estoque<font color="red">*</font></b></font></font></label>
                                <input type="text" class="form-control" id="quantidade" name="quantidade" value="<?php echo $linha['quantidadeEstoque'].' '.$linha['codUnidadeMedida'] ?>" readonly>
                            </div>

                            <input type="hidden" name="numero" id="numero" value="<?php echo $linha['quantidadeEstoque'] ?>">

                            <input type="hidden" name="statusAtual" id="statusAtual" value="<?php echo $linha['cancelado'] ?>">

                            <?php if($linha['cancelado'] == 'S'){ ?>
                            <div class="form-group col-md-4">
                                <label><font color="yellow">Status <font color="red">*</font></b></font></label>
                                <select class="form-control btn-danger" id="editar" name="editar">
                                	<option value="inativar">Inativo</option>
                                    <option value="ativar">Ativo</option>
                                    </select>
	                            </div>
                            <?php }else{ ?>
                            	<div class="form-group col-md-4">
                                <label><font color="yellow">Status <font color="red">*</font></b></font></label>
                                <select class="form-control btn-success" id="editar" name="editar">
                                    <option value="ativar">Ativo</option>
                                    <option value="inativar">Inativo</option>
                                    </select>
	                            </div>
                            <?php } ?>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="inputZip"><font color="yellow">Grupo de Item<font color="red">*</font></b></font></font></label>
                                <input type="text" class="form-control" id="grupoItem" name="grupoItem" value="<?php echo $linha['nomeGrupo'] ?>" readonly>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="inputCity"><font color="yellow">Unidade de Medida Padrão <font color="red">*</font></b></font></font></label>
                                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $linha['obsUnidadeMedida'] ?>" readonly>
                            </div>
                        </div>

                        <label class="switch">
                            <?php if($linha['itemCompra'] == 'Y'){ ?>
                                <input type="checkbox" id="itemCompra" name="itemCompra" checked disabled>
                            <?php }else{ ?>
                                <input type="checkbox" id="itemCompra" name="itemCompra" disabled>
                            <?php } ?>
                            <span class="slider round"></span>
                        </label>
                        <span style="color: yellow; font-size: 15px;">Item de Compra</span><br>

                        <label class="switch">
                            <?php if($linha['itemVenda'] == 'Y'){ ?>
                                <input type="checkbox" id="itemVenda" name="itemVenda" checked disabled>
                            <?php }else{ ?>
                                <input type="checkbox" id="itemVenda" name="itemVenda" disabled>
                            <?php } ?>
                            <span class="slider round"></span>
                        </label>
                        <span style="color: yellow; font-size: 15px;">Item de Venda</span><br><br><br>

                        <span><font color="red">(*) Campos Obrigatórios</font></span><br>
                        <span><font color="grey">Campos bloqueados por segurança</font></span><br><br>

                        <button type="submit" class="btn btn-outline-primary" style="margin-left: 43%;" onclick="return validarItem()">Atualizar</button>

                    </form>
                </div>
            </div><br><br><br>
            <!--FIM ATUALIZAR ITEM-->
        </div>



            <!-- Optional JavaScript -->
            <!-- jQuery first, then Popper.js, then Bootstrap JS -->
            <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
            <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
    </body>
</html>