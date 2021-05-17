<?php
include('../../v_login.php');

//CONEXAO BANCO DE DADOS
include '../../conexao.php';

$idFicha = filter_input(INPUT_POST, 'idFicha', FILTER_SANITIZE_NUMBER_INT);
if (empty($idFicha)) {
	echo "Id da Ficha está nulo. Por favor refaça o procedimento anterior<br>";
	return 0;
}else{
	$v_idFicha = trim(substr($idFicha, 0, 8));
}

//VALIDANDO A FICHA PASSADA PELO PROCEDIMENTO ANTERIOR
$tabelaOITT = "SELECT * FROM OITT WHERE idFicha = '$v_idFicha';";
$resultadoTabelaOITT = mysqli_query($conexao, $tabelaOITT);
if (mysqli_affected_rows($conexao) <= 0) {
	echo "Não encontrado Ficha técnica. Por favor refaça o procedimento<br>";
	return 0;
}
$linhaOITT = mysqli_fetch_assoc($resultadoTabelaOITT);

$tabelaITT1 = "
SELECT T0.idProduto AS 'idProduto', T1.nomeProduto AS 'nomeProduto', T0.quantidade AS 'quantidade', T2.nomeUnidade AS 'nomeUnidade'
FROM ITT1 T0

INNER JOIN OITM T1
ON T0.idProduto = T1.idProduto

INNER JOIN grupounidademedida T2
ON T0.idGrupoUnidadeMedida = T2.idGrupoUnidadeMedida

WHERE idFicha = '$v_idFicha' ORDER BY numLinha ASC;";
$resultadoITT1 = mysqli_query($conexao, $tabelaITT1);
if (mysqli_affected_rows($conexao) <= 0) {
	echo "Não encontrado as linhas da ITT1. Por favor refaça o procedimento<br>";
	return 0;
}


//TABELA OITM PARA PREENCHIMENTO DO DATALIST
$tabelaOITM = "
SELECT T0.idProduto, T0.nomeProduto, T1.codUnidadeMedida
FROM oitm T0

INNER JOIN unidadeMedida T1
ON T0.idUnidadeMedida = T1.idUnidadeMedida 

WHERE T0.cancelado = 'N'
AND T0.itemCompra = 'Y';";

$resultadoOITM = mysqli_query($conexao, $tabelaOITM);

$tabelaUnidadeMedida = "SELECT * FROM unidadeMedida WHERE cancelado = 'N' ";
$resultadoUnidadeMedida = mysqli_query($conexao, $tabelaUnidadeMedida);


date_default_timezone_set('America/Sao_Paulo');
$data = date('d/m');
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
            var contador = 1;
            var cContador = null;

            
            function validar() {
                var finalizar = 0;
                var vNLinha = document.getElementById('quantidadeLinhas').value;
                var validadorLinhas = parseFloat("0");

                //ANÁLISE SE A PESSOA ESTÁ TENTANDO ALTERAR A COMANDA SEM NENHUMA LINHA DE PRODUTO
                for (var b = 1; b <= vNLinha; b++) {
                    var analiseProd = document.getElementById('produto'+b);
                    if (analiseProd !== null) {
                        validadorLinhas = parseFloat(validadorLinhas) + parseFloat("1");
                    }else{
                        validadorLinhas = parseFloat(validadorLinhas) + parseFloat("0");
                    }  
                }

                if (validadorLinhas == 0) {
                    alert('Para alterar é preciso ter pelo menos uma linha de produto');
                    return false;
                }


                //ANALISE SOBRE OS ITENS PRESENTES NA TABELA
                for (var i = 1; i <= contador; i++) {
                    var analiseLinha = document.getElementById('produto'+i);

                    if (analiseLinha !== null) {
                        if (document.getElementById('produto'+i).value == '') {
                        alert('Por favor insira um Produto na linha ' + i);
                        return false;
                        finalizar = 1;
                        }

                        if (document.getElementById('quantidade'+i).value == '' || document.getElementById('quantidade'+i).value <= 0 || document.getElementById('quantidade'+i).value > 9999) {
                            alert('Por favor insira uma Quantidade válida na linha ' + i + ' (Min: 1 - Max: 9999)');
                            return false;
                            finalizar = 1;
                        }

                        if (document.getElementById('grupoUnidadeMedida'+i).value == 0 || document.getElementById('grupoUnidadeMedida'+i).value == null) {
                            alert('Por favor insira uma Unidade de Medida na linha ' + i);
                            return false;
                            finalizar = 1;
                        }
                    }else{
                        continue;
                    }

                }  

                if (finalizar == 0) {
                    var resultadoAdicionarNota = confirm('Realmente deseja alterar a Ficha Técnica?');
                    if (resultadoAdicionarNota == false) {
                        alert('Ação cancelada');
                        return false;
                    }else{
                        alert('Alterando a Ficha Técnica');
                        setTimeout(function () {
                        window.close();
                        }, 500);
                    }
                }

                 
            }
                

            function criar() {
                cContador = document.getElementById('quantidadeLinhas').value;
                cContador = parseInt(cContador) + parseInt(1);
                document.getElementById('quantidadeLinhas').value = cContador;
                contador = cContador;


                //SETANDO ONDE VAI SER ADICIONADO OS NOVOS CAMPOS
                var formulario = document.getElementById('formulario');


                //-------------------------DIV LINHA-----------------------------//
                //CAMPOS QUE SERAO INCREMENTADOS
                var divLinha = document.createElement("div");
                divLinha.setAttribute("id", "linha"+contador); 
                divLinha.setAttribute("class", "form-row distanciaMetade");    
                
                //AÇÃO
                formulario.appendChild(divLinha);

                //-------------------------PRODUTO-----------------------------//
                //CAMPOS QUE SERAO INCREMENTADOS
                var div = document.createElement("div");
                div.setAttribute("class", "col-md-5 mb-1");

                var input = document.createElement("input");
                input.setAttribute("type", "text");
                input.setAttribute("class", "form-control");
                input.setAttribute("id", "produto" + contador);
                input.setAttribute("name", "produto" + contador);
                input.setAttribute("list", "listaItens");

                //AÇÃO
                formulario.appendChild(divLinha);
                divLinha.appendChild(div);
                div.appendChild(input);

                //-------------------------QUANTIDADE-----------------------------//
                //CAMPOS QUE SERAO INCREMENTADOS
                var div = document.createElement("div");
                div.setAttribute("class", "distanciaQuantidade col-md-2 mb-1");

                var input = document.createElement("input");
                input.setAttribute("type", "number");
                input.setAttribute("class", "form-control");
                input.setAttribute("id", "quantidade" + contador);
                input.setAttribute("name", "quantidade" + contador);

                //AÇÃO
                formulario.appendChild(divLinha);
                divLinha.appendChild(div);
                div.appendChild(input);

                
                //-------------------------UNIDADE MEDIDA-----------------------------//
                //CAMPOS QUE SERAO INCREMENTADOS
                var div = document.createElement("div");
                div.setAttribute("class", "distanciaUM col-md-2 mb-1");

                var select = document.createElement("select");
                select.setAttribute("class", "form-control");
                select.setAttribute("id", "grupoUnidadeMedida" + contador);
                select.setAttribute("name", "grupoUnidadeMedida" + contador);

                var option0 = document.createElement("option");
                option0.setAttribute("value", "0");
                option0.textContent = "Selecione";

                var option1 = document.createElement("option");
                option1.setAttribute("value", "UN");
                option1.textContent = "UN";

                var option2 = document.createElement("option");
                option2.setAttribute("value", "KG");
                option2.textContent = "KG";

                var option3 = document.createElement("option");
                option3.setAttribute("value", "LT");
                option3.textContent = "LT";

                //AÇÃO
                formulario.appendChild(divLinha);
                divLinha.appendChild(div);
                div.appendChild(select);
                select.appendChild(option0);
                select.appendChild(option1);
                select.appendChild(option2);
                select.appendChild(option3);

                //-------------------------EXCLUIR-----------------------------//
                //CAMPOS QUE SERAO INCREMENTADOS
                var div = document.createElement("div");
                div.setAttribute("class", "col-md-1 mb-1");

                var button = document.createElement("button");
                button.setAttribute("type", "button");
                button.setAttribute("class", "btn btn-danger botaoExcluirLinha");
                button.setAttribute("id", "botaoExcluir" + contador);
                button.setAttribute("name", "botaoExcluir" + contador);
                button.setAttribute("onclick", "excluirLinha("+contador+")");
                button.textContent = "X";


                //AÇÃO
                formulario.appendChild(divLinha);
                divLinha.appendChild(div);
                div.appendChild(button);
            }

            function excluirLinha(num) {
            	var divExcluir = 'linha'+num;
                document.getElementById(divExcluir).remove();
            }

        </script>

        <style type="text/css">
            
        </style>

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

        <!--INICIO ADICIONAR FICHA TÉCNICA-->
        <div class="tab-content" id="v-pills-tabContent">
            <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                <div class="EditarFichaMargem">
                    <img src="../../img/covilLogin.png" width="180px;" height="100px;" style="display: block; margin-left: auto; margin-right: auto;">
                    <h2 style="text-align: center; margin-left: 1%;"><font color="yellow">Cadastro Ficha Técnica</font></h1><br><br>
                    <form class="needs-validation" method="POST" action="atualizarFichaTecnica.php" novalidate target="_blank">
                        <div class="form-row">
                            <div class="col-md-2 mb-3">
                                <span><font size="2" color="yellow"><b>Número Ficha</b></font></span>
                                <input type="text" class="form-control" id="idFicha" name="idFicha" value="<?php echo $linhaOITT['idFicha'] ?>" readonly>		
                            </div>

                            <div class="col-md-7 mb-3 ">
                                <span><font color="yellow">Nome Ficha Técnica</font></span>
                                <input type="text" class="form-control" id="nomeFicha" name="nomeFicha" value="<?php echo $linhaOITT['nomeFicha'] ?>" readonly>
                            </div>
                            <?php if($linhaOITT['cancelado'] == 'S'){ ?>
                            <div class="form-group col-md-3">
                                <span><font color="yellow">Status <font color="red">*</font></b></font></span>
                                <select class="form-control btn-danger" id="editar" name="editar">
                                    <option value="inativar">Inativo</option>
                                    <option value="ativar">Ativo</option>
                                    </select>
                                </div>
                            <?php }else{ ?>
                                <div class="form-group col-md-3">
                                <span><font color="yellow">Status <font color="red">*</font></b></font></span>
                                <select class="form-control btn-success" id="editar" name="editar">
                                    <option value="ativar">Ativo</option>
                                    <option value="inativar">Inativo</option>
                                    </select>
                                </div>
                            <?php } ?>
                        </div>

                        <table class="distanciaTop table table-sm table-dark">
                            <thead>
                                <tr>
                                    <th scope="col">Produto</th>
                                    <th scope="col" style="text-align: right;">Quantidade</th>
                                    <th scope="col" style="text-align: center;">Uni Medida</th>
                                </tr>
                            </thead>
                        </table>

                        <div id="formulario">
                            <!----------------------INICIO DATALIST----------------------->
                            <!--PRODUTO-->
                            <datalist id="listaItens">
                                <?php while ($linhaOITM = mysqli_fetch_assoc($resultadoOITM)) { ?>
                                    <option value="<?php echo $linhaOITM['idProduto'] . '-' . $linhaOITM['nomeProduto']. '(' . $linhaOITM['codUnidadeMedida'].')'; ?>"></option>
                                <?php } ?>
                            </datalist>
                            <!----------------------FIM DATALIST----------------------->
                                

                            <?php
                                $contLinha = 0;
                                while ($linhaITT1 = mysqli_fetch_assoc($resultadoITT1)) {
                                	$contLinha = $contLinha + 1;
                                	$produto = $linhaITT1['idProduto'].' - '.$linhaITT1['nomeProduto'].'('.$linhaITT1['nomeUnidade'].')';
                                	$quantidade = $linhaITT1['quantidade'];
                                	$grupoUnidadeMedida = $linhaITT1['nomeUnidade'];
                                
                                
	                            	echo "<div id='linha$contLinha' class='form-row'>"
		                                ."<div class='col-md-5 mb-1'>"
		                                    ."<input type='text' class='form-control' id='produto$contLinha' name='produto$contLinha' value='$produto' list='listaItens'>"
		                                ."</div>"
		                                ."<div class='distanciaQuantidade col-md-2 mb-1'>"
		                                    ."<input type='number' class='form-control' id='quantidade$contLinha' name='quantidade$contLinha' value='$quantidade' min='1' max='9999'>"
		                                ."</div>"
		                                ."<div class='distanciaUM col-md-2 mb-1'>"
		                                    ."<select class='form-control' id='grupoUnidadeMedida$contLinha' name='grupoUnidadeMedida$contLinha'>";
		                                    	if ($grupoUnidadeMedida == 'UN') {
		                                    		echo "<option value='0'>Selecione</option>"
				                                    	."<option value='UN' selected>UN</option>"
				                                    	."<option value='KG'>KG</option>"
				                                    	."<option value='LT'>LT</option>";
		                                    	}else if($grupoUnidadeMedida == 'KG'){
		                                    		echo "<option value='0'>Selecione</option>"
				                                    	."<option value='UN'>UN</option>"
				                                    	."<option value='KG' selected>KG</option>"
				                                    	."<option value='LT'>LT</option>";
		                                    	}else{
		                                    		echo "<option value='0'>Selecione</option>"
				                                    	."<option value='UN'>UN</option>"
				                                    	."<option value='KG'>KG</option>"
				                                    	."<option value='LT' selected>LT</option>";
		                                    	}
		                                    echo "</select>"
		                                ."</div>"
		                                ."<div class='col-md-1 mb-1'>"
		                                    ."<button type='button' class='btn btn-danger botaoExcluirLinha' id='botaoExcluir1' name='botaoExcluir1' onclick='excluirLinha($contLinha)'>X</button>"
		                                ."</div>"
		                            ."</div>";
	                            }
	                            echo "<input type='hidden' id='quantidadeLinhas' name='quantidadeLinhas' value='$contLinha'>";
                            ?>
                        </div>

                        <!--INICIO SEPARADOR-->
                        <div class="distanciaTop1">
                            <ul class="nav nav-tabs justify-content-center">
                            </ul>
                        </div>
                        <!--FIM SEPARADOR-->

                        <button type="button" class="btn btn-outline-danger distanciaTop1" onclick="criar()"><font><b>+</b></font></button>
                        <div id="button" class="buttonAdicionar">
                            <button class="btn btn-outline-primary" onclick="return validar()">Alterar</button>
                        </div>
                    </form>
                </div><br>
            </div>
            <!--FIM ADICIONAR FICHA TÉCNICA-->

        </div>

        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
    </body>
</html>
<?php 
