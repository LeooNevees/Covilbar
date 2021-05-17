<?php
include('../../v_login.php');

//CONEXAO BANCO DE DADOS
include '../../conexao.php';

$tabelaOITM = "
SELECT T0.idProduto, T0.nomeProduto, T1.codUnidadeMedida
FROM oitm T0

INNER JOIN unidadeMedida T1
ON T0.idUnidadeMedida = T1.idUnidadeMedida 

WHERE T0.cancelado = 'N'";

$resultadoOITM = mysqli_query($conexao, $tabelaOITM);

$tabelaUnidadeMedida = "SELECT * FROM unidadeMedida WHERE cancelado = 'N' ";
$resultadoUnidadeMedida = mysqli_query($conexao, $tabelaUnidadeMedida);

//RETORNO DO ULTIMO NUMERO DE FICHA TÉCNICA
$IDtabelaOITT = "SELECT * FROM oitt ORDER BY idFicha DESC LIMIT 1;";
$IDresultadoOITT = mysqli_query($conexao, $IDtabelaOITT);
$linhaOITT = mysqli_fetch_assoc($IDresultadoOITT);

if (mysqli_affected_rows($conexao) <= 0) {
    $ProxNumeroFicha = 90000001;
}else{
    $ProxNumeroFicha = $linhaOITT['idFicha'] + 1;
}


//RETORNO DE TODA A TABELA OITT PARA CONSULTA
$tabelaoOITT = "SELECT * FROM OITT ORDER BY idFicha";
$resultadoOITT = mysqli_query($conexao, $tabelaoOITT);

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
            
            function validar() {
                var finalizar = 0;

                document.getElementById('quantidadeLinhas').value = contador;

                if (document.getElementById('nomeFicha').value == '' || document.getElementById('nomeFicha').value == null) {
                    alert('Por favor insira o Nome da Ficha');
                    return false;
                    finalizar = 1;
                }

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
                    var resultadoAdicionarNota = confirm('Realmente deseja adicionar a Ficha Técnica?');
                    if (resultadoAdicionarNota == false) {
                        alert('Ação cancelada');
                        return false;
                    }else{
                        alert('Cadastrando a Ficha Técnica');
                        setTimeout(function () {
                        window.location.href = "http://localhost/CovilBar/sistema/Estoque/fichaTecnica.php";
                        }, 500);
                    }
                }

                 
            }                

            function criar() {
                contador = contador + 1;

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
                input.setAttribute("onblur", "soma('quantidade" + contador + "', 'valorUnitario" + contador + "', 'valorTotal" + contador + "'), calcularTotalFinal()");

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
                button.setAttribute("onclick", "excluirLinha('linha"+contador+"')");
                button.textContent = "X";


                //AÇÃO
                formulario.appendChild(divLinha);
                divLinha.appendChild(div);
                div.appendChild(button);
            }

            function excluirLinha(divExcluir) {
                document.getElementById(divExcluir).remove();
            }

            function validarVisualizacao() {
                var analisador = 0;

                if (document.getElementById('idFicha').value == '' || document.getElementById('idFicha').value == null) {
                    alert('Número de nota invalida. Por favor refaça o procedimento');
                    analisador = 1;
                    return false;
                }

                if (analisador == 0) {
                    setTimeout(function () {
                        alert("Nota aberta na outra guia");
                        window.location.href = "http://localhost/CovilBar/sistema/Estoque/fichaTecnica.php";
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
                    <a class="nav-link active" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true" title="Adicionar novo parceiro"><i class="fas fa-plus" style="color: red;"></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="v-pills-profile-tab" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false" title="Pesquisar Parceiro"><i class="fa fa-search" style="color: red;"></i></a>
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
                    <form class="needs-validation" method="POST" action="cadastrarFicha.php" novalidate target="_blank">
                        <div class="form-row">
                            <div class="col-md-2 mb-3 distanciaFicha">
                                <span><font size="2" color="yellow"><b>Número Ficha</b></font></span>
                                <input type="text" class="form-control" id="numeroFicha" name="numeroFicha" value="<?php echo $ProxNumeroFicha ?>" readonly>		
                            </div>

                            <div class="col-md-6 mb-3 ">
                                <span><font size="2" color="yellow">Nome Ficha Técnica</font></span>
                                <input type="text" class="form-control" id="nomeFicha" name="nomeFicha" >
                            </div>
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
                                <input type="hidden" id="quantidadeLinhas" name="quantidadeLinhas">

                            <div id="linha1" class="form-row">
                                <div class="col-md-5 mb-1">
                                    <input type="text" class="form-control" id="produto1" name="produto1" list="listaItens">
                                </div>

                                <div class="distanciaQuantidade col-md-2 mb-1">
                                    <input type="number" class="form-control" id="quantidade1" name="quantidade1" min="1" max="9999">
                                </div>

                                <div class="distanciaUM col-md-2 mb-1">
                                    <select class="form-control" id="grupoUnidadeMedida1" name="grupoUnidadeMedida1">
                                    	<option value="0">Selecione</option>
                                    	<?php while($linhaUnidadeMedida = mysqli_fetch_assoc($resultadoUnidadeMedida)){?>
                                    		<option value="<?php echo $linhaUnidadeMedida['codUnidadeMedida'] ?>"><?php echo $linhaUnidadeMedida['codUnidadeMedida']; ?></option>
                                    	<?php } ?>
                                    </select>
                                </div>

                                <div class="col-md-1 mb-1">
                                    <button type="button" class="btn btn-danger botaoExcluirLinha" id="botaoExcluir1" name="botaoExcluir1" onclick="excluirLinha('linha1')">X</button>
                                </div>
                            </div>
                        </div>

                        <!--INICIO SEPARADOR-->
                        <div class="distanciaTop1">
                            <ul class="nav nav-tabs justify-content-center">
                            </ul>
                        </div>
                        <!--FIM SEPARADOR-->

                        <button type="button" class="btn btn-outline-danger distanciaTop1" onclick="criar()"><font><b>+</b></font></button>
                        <div id="button" class="buttonAdicionar">
                            <button class="btn btn-outline-primary" onclick="return validar()">Adicionar</button>
                        </div>
                    </form>
                </div><br>
            </div>
            <!--FIM ADICIONAR FICHA TÉCNICA-->

            <!--INICIO PESQUISAR FICHA TÉCNICA-->
            <div class="tab-pane fade alinhamentoTopParceiro" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                <div class="margem2">
                    <img src="../../img/covilLogin.png" width="200px;" height="140px;" style="display: block; margin-left: auto; margin-right: auto;">
                    <form class="needs-validation" method="POST" action="editarFichaTecnica.php" target="_blank">
                        <div class="form-group">
                            <label for="inputAddress"><font color="yellow">Pesquisar Ficha Técnica <font color="red">*</font></b></font></label>
                            <input type="text" class="form-control" id="idFicha" name="idFicha" placeholder="Busque a Ficha Técnica pelo nome" list="listaFichaTecnica" required>
                            <datalist id="listaFichaTecnica">
                                <?php while ($linhaConOITT = mysqli_fetch_assoc($resultadoOITT)) { 
                                	$status = $linhaConOITT['cancelado'];
                                	if ($status == 'N') {
                                		$v_status = 'ATIVO';
                                	}else{
                                		$v_status = 'CANCELADO';
                                	}
                                ?>

                                    <option aria-disabled="true" value="<?php echo $linhaConOITT['idFicha'].' - '.$linhaConOITT['nomeFicha'].' - '.$v_status; ?>" ></option>
                                <?php } ?>
                            </datalist>
                        </div><br>
                        <button type="submit" class="btn btn-outline-primary" style="margin-left: 43%;" onclick="return validarVisualizacao()">Analisar</button>
                    </form>
                </div><br><br>
            </div>
            <!--FIM PESQUISAR FICHA TÉCNICA-->
        </div>

        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
    </body>
</html>
<?php 
