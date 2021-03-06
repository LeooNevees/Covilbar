<?php

include('../v_login.php');

//COLENTADO OS DADOS
$usuario = $_SESSION['usuario'];
$finalizarDia = filter_input(INPUT_POST, 'finalizarDia', FILTER_SANITIZE_STRING);
date_default_timezone_set('America/Sao_Paulo');
$data = date('Y-m-d');


if (empty($finalizarDia)) {
	echo "Por favor retorne ao procedimento anterior e insira a data de finalização<br>";
	return 0;
}else{
	$v_finalizarDia = trim($finalizarDia);
}

//CONTAGEM PARA A DATA DE VENCIMENTO
$dataVencSaida = date('Y-m-d', strtotime('+30 days'));

//CONEXAO COM BANCO DE DADOS 
include '../conexao.php';

$tabelaOCRD = "SELECT * FROM OCRD WHERE cancelado = 'N' AND tipo = 'CLIENTE' ";
$resultadoIdOcrd = mysqli_query($conexao, $tabelaOCRD);

$tabelaOWHS = "SELECT * FROM OWHS WHERE cancelado = 'N'";
$resultadoOWHS = mysqli_query($conexao, $tabelaOWHS);

//RETORNO DO ULTIMO NUMERO NOTA
$IDtabelaOINV = "SELECT * FROM OINV ORDER BY idSaida DESC LIMIT 1;";
$IDresultadoOINV = mysqli_query($conexao, $IDtabelaOINV);
$linhaOINV = mysqli_fetch_assoc($IDresultadoOINV);

if (mysqli_affected_rows($conexao) <= 0) {
    $ProxNumeroNota = 1;
}else{
    $ProxNumeroNota = $linhaOINV['idSaida'] + 1;
}


date_default_timezone_set('America/Sao_Paulo');

//FORMATANDO HORA PARA PEGAR AS COMANDAS DO RESPECTIVO DIA A PARTIR DAS 07:00
$dataHora = $v_finalizarDia.' 07:00';


//BUSCANDO COMANDAS QUE ESTÃO COM O STATUS FECHADAS DO RESPECTIVO DIA
$tabelaFaturamentoFechado = "
SELECT T1.idProduto AS 'idProduto', T2.nomeProduto AS 'nomeProduto', SUM(T1.quantidade) AS 'quantidade', T4.nomeUnidade AS 'nomeUnidade', T3.codUnidadeMedida AS 'codUnidadeMedida', SUM(T1.totalLinha) AS 'vendaTotal'
FROM CMD1 T1

LEFT JOIN OCMD T0
ON T1.idComanda  = T0.idComanda

LEFT JOIN OITM T2
ON T1.idProduto = T2.idProduto

LEFT JOIN UNIDADEMEDIDA T3
ON T2.idUnidadeMedida = T3.idUnidadeMedida

LEFT JOIN GRUPOUNIDADEMEDIDA T4
ON T2.idGrupoUnidadeMedida = T4.idGrupoUnidadeMedida

WHERE T0.cancelado = 'N' 
AND T0.horaEntrada >= '$dataHora'
AND T0.statusEstoque = 'ABERTO'
GROUP BY T1.idProduto ;";
$resultadoFaturamentoFechado = mysqli_query($conexao, $tabelaFaturamentoFechado);
$numLinhaFat = mysqli_affected_rows($conexao);

$tabelaValorTotal = "
SELECT SUM(T0.valorTotal) AS 'valorTotal'
FROM OCMD T0
WHERE T0.cancelado = 'N' 
AND T0.horaEntrada >= '$dataHora'
AND T0.statusEstoque = 'ABERTO';";
$resultadoValorTotal = mysqli_query($conexao, $tabelaValorTotal);
$linhaValorTotal = mysqli_fetch_assoc($resultadoValorTotal);

?>

<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <!-- ICONE NA BARRA DO NAVEGADOR-->
        <link rel="shortcut icon" href="../img/zillaMonstro.png">

        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Covil Bar</title>

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
        <link rel="stylesheet" href="style.css">

        <!-----ICONES------------>
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <script src="https://kit.fontawesome.com/70c48f08c7.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>


        <script type="text/javascript"> 
            var contador = null;

            function validar() {
                var finalizar = 0;

                if (document.getElementById('codigoFornecedor').value == '' || document.getElementById('codigoFornecedor').value == null) {
                    alert('Por favor insira um fornecedor válido');
                    return false;
                    finalizar = 1;
                }

                if (document.getElementById('dataLancamento').value == '') {
                    alert('Por favor insira uma data de lançamento válida');
                    return false;
                    finalizar = 1;
                }

                if (document.getElementById('dataVencimento').value == '') {
                    alert('Por favor insira uma data de vencimento válida');
                    return false;
                    finalizar = 1;
                }

                if (document.getElementById('dataVencimento').value < document.getElementById('dataLancamento').value) {
                    alert('Data de vencimento não pode ser inferior a data de Lançamento');
                    return false;
                    finalizar = 1;
                }

                if (document.getElementById('parcelas').value == 0) {
                    alert('Por favor insira as Parcelas');
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

                        if (document.getElementById('grupoUnidadeMedida'+i).value == '') {
                            alert('Por favor insira uma Unidade de Medida na linha ' + i);
                            return false;
                            finalizar = 1;
                        }

                        if (document.getElementById('valorUnitario'+i).value == '' || document.getElementById('valorUnitario'+i).value == 'R$') {
                            alert('Por favor insira o Valor Unitario na linha ' + i);
                            return false;
                            finalizar = 1;
                        }
                    }else{
                        continue;
                    }

                }  

                /*if (finalizar == 0) {
                    alert("Cadastrando Nota Fiscal de Saida");
                    setTimeout(function () {
                        window.close();
                    }, 500);
                }*/

            }

            function soma(idNum) {
            	var qtde = 'quantidade'+idNum;
            	var valor = 'valorUnitario'+idNum;
            	var tot = 'valorTotal'+idNum;

                var quantidade = parseFloat(document.getElementById(qtde).value.replace(',', '.'));
                if (quantidade == null || quantidade == '') {
                    quantidade = 0;
                }

                var valorunitario = document.getElementById(valor).value.replace('.', '');
                var valorUnitario = valorunitario.replace(',', '.');
                if (isNaN(valorUnitario)) {
                    valorUnitario = 0;
                }


                var total = quantidade * valorUnitario;

                var totalMod = total.toLocaleString('pt-br', {minimumFractionDigits: 2});
                if (totalMod == 'NaN') {
                    totalMod = 0;
                }

                //valorNotaTotal += total;
                //calcularTotalFinal();
                return document.getElementById(tot).value = totalMod;
                
            }

            function calcularTotalFinal() {
                var valorNotaTotal = parseFloat("0");

                for (var t = 1; t <= contador; t++) {
                    var valorTot = document.getElementById("valorTotal"+t);

                    if (valorTot !== null) {
                        var valoralterado = document.getElementById('valorTotal'+t).value.replace('.', '');
                        var valorAlterado = valoralterado.replace(',', '.');
                        valorNotaTotal = parseFloat(valorNotaTotal) + parseFloat(valorAlterado);
                    }else{
                        continue;
                    }
                }

                var totalMod = valorNotaTotal.toLocaleString('pt-br', {minimumFractionDigits: 2});
                document.getElementById('totalNota').value = totalMod;
            }
                

            function MascaraMoeda(objTextBox, SeparadorMilesimo, SeparadorDecimal, e) {
                var sep = 0;
                var key = '';
                var i = j = 0;
                var len = len2 = 0;
                var strCheck = '0123456789';
                var aux = aux2 = '';
                var whichCode = (window.Event) ? e.which : e.keyCode;
                if (whichCode == 13)
                    return true;
                key = String.fromCharCode(whichCode); // Valor para o código da Chave
                if (strCheck.indexOf(key) == -1)
                    return false; // Chave inválida
                len = objTextBox.value.length;
                for (i = 0; i < len; i++)
                    if ((objTextBox.value.charAt(i) != '0') && (objTextBox.value.charAt(i) != SeparadorDecimal))
                        break;
                aux = '';
                for (; i < len; i++)
                    if (strCheck.indexOf(objTextBox.value.charAt(i)) != -1)
                        aux += objTextBox.value.charAt(i);
                aux += key;
                len = aux.length;
                if (len == 0)
                    objTextBox.value = '';
                if (len == 1)
                    objTextBox.value = '0' + SeparadorDecimal + '0' + aux;
                if (len == 2)
                    objTextBox.value = '0' + SeparadorDecimal + aux;
                if (len > 2) {
                    aux2 = '';
                    for (j = 0, i = len - 3; i >= 0; i--) {
                        if (j == 3) {
                            aux2 += SeparadorMilesimo;
                            j = 0;
                        }
                        aux2 += aux.charAt(i);
                        j++;
                    }
                    objTextBox.value = '';
                    len2 = aux2.length;
                    for (i = len2 - 1; i >= 0; i--)
                        objTextBox.value += aux2.charAt(i);
                    objTextBox.value += SeparadorDecimal + aux.substr(len - 2, len);
                }
                return false;
            }

            function criar() {
                contador = document.getElementById('quantidadeLinhas').value;
                contador = parseInt(contador) + parseInt(1);
                document.getElementById('quantidadeLinhas').value = contador;

                //SETANDO ONDE VAI SER ADICIONADO OS NOVOS CAMPOS
                var formulario = document.getElementById('formulario');

                //-------------------------DIV LINHA-----------------------------//
                //CAMPOS QUE SERAO INCREMENTADOS
                var divLinha = document.createElement("div");
                divLinha.setAttribute("id", "linha"+contador); 
                divLinha.setAttribute("class", "form-row");    
                
                //AÇÃO
                formulario.appendChild(divLinha);


                //-------------------------PRODUTO-----------------------------//
                //CAMPOS QUE SERAO INCREMENTADOS
                var div = document.createElement("div");
                div.setAttribute("class", "col-md-3 mb-1");

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
                div.setAttribute("class", "FinalizarDiaDistanciaQuantidade col-md-1 mb-1");

                var input = document.createElement("input");
                input.setAttribute("type", "number");
                input.setAttribute("class", "form-control");
                input.setAttribute("id", "quantidade" + contador);
                input.setAttribute("name", "quantidade" + contador);
                input.setAttribute("onblur", "soma("+contador+"), calcularTotalFinal()");

                //AÇÃO
                formulario.appendChild(divLinha);
                divLinha.appendChild(div);
                div.appendChild(input);

                //-------------------------UNIDADE MEDIDA-----------------------------//
                //CAMPOS QUE SERAO INCREMENTADOS
                var div = document.createElement("div");
                div.setAttribute("class", "distanciaUM col-md-1 mb-1");

                var input = document.createElement("input");
                input.setAttribute("type", "text");
                input.setAttribute("class", "form-control");
                input.setAttribute("id", "grupoUnidadeMedida" + contador);
                input.setAttribute("name", "grupoUnidadeMedida" + contador);
                input.setAttribute("list", "listaUnidadeMedida");

                //AÇÃO
                formulario.appendChild(divLinha);
                divLinha.appendChild(div);
                div.appendChild(input);

                //-------------------------VALOR UNITARIO-----------------------------//
                //CAMPOS QUE SERAO INCREMENTADOS
                var div = document.createElement("div");
                div.setAttribute("class", "FinalizarDiadistanciaUnitario col-md-1 mb-1");

                var input = document.createElement("input");
                input.setAttribute("type", "text");
                input.setAttribute("class", "form-control");
                input.setAttribute("id", "valorUnitario" + contador);
                input.setAttribute("name", "valorUnitario" + contador);
                input.setAttribute("onKeyPress", "return(MascaraMoeda(this,'.',',',event))");
                input.setAttribute("onblur", "soma(" + contador + "), calcularTotalFinal()");
                input.setAttribute("value", "R$");

                //AÇÃO
                formulario.appendChild(divLinha);
                divLinha.appendChild(div);
                div.appendChild(input);


                //-------------------------TOTAL-----------------------------//
                //CAMPOS QUE SERAO INCREMENTADOS
                var div = document.createElement("div");
                div.setAttribute("class", "FinalizarDiaDistanciaTotal col-md-1 mb-1");

                var input = document.createElement("input");
                input.setAttribute("type", "text");
                input.setAttribute("style", "text-align: center; font-size: 13px;");
                input.setAttribute("class", "form-control");
                input.setAttribute("id", "valorTotal" + contador);
                input.setAttribute("name", "valorTotal" + contador);
                input.setAttribute("readonly", "true");

                //AÇÃO
                formulario.appendChild(divLinha);
                divLinha.appendChild(div);
                div.appendChild(input);

                 //-------------------------EXCLUIR-----------------------------//
                //CAMPOS QUE SERAO INCREMENTADOS
                var div = document.createElement("div");
                div.setAttribute("class", "col-md-1 mb-1");

                var button = document.createElement("button");
                button.setAttribute("type", "button");
                button.setAttribute("class", "btn btn-danger botaoExcluirLinha2");
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
                calcularTotalFinal();
            }

            function validarVisualizacao() {
                var analisador = 0;

                if (document.getElementById('idNota').value == '' || document.getElementById('idNota').value == null) {
                    alert('Número de nota invalida. Por favor refaça o procedimento');
                    analisador = 1;
                    return false;
                }

                if (analisador == 0) {
                    setTimeout(function () {
                        alert("Nota aberta na outra guia");
                        window.location.href = "http://localhost/CovilBar/sistema/Estoque/saida.php";
                    }, 500);
                }
            }

            //FUNÇÃO PARA QUE APÓS INSERIR A QUANTIDADE DE PARCELAS ABRA ESSA TELA DE DATAS DE VENCIMENTOS
            function inserirDataVencimento() {
                var parcelasVencimento = document.getElementById('parcelas').value;
                var thumb = [];

                for (var c = 1; c <= parcelasVencimento; c++) {
                    thumb[c] = document.getElementById("dataVencimentoParc"+c);

                    if (!thumb[c]) {
                        if (c == parcelasVencimento) {
                        //SETANDO ONDE VAI SER ADICIONADO OS NOVOS CAMPOS
                        var formulario = document.getElementById('content');

                        //CAMPOS QUE SERAO INCREMENTADOS
                        var div = document.createElement("div");
                        div.setAttribute("class", "distanciaTop1");

                        var label = document.createElement("label");
                        label.textContent = "Data de Vencimento "+c+": ";

                        var input = document.createElement("input");
                        input.setAttribute("type", "date");
                        input.setAttribute("id", "dataVencimentoParc"+c);
                        input.setAttribute("name", "dataVencimentoParc"+c);
                        input.setAttribute("onblur", "addDataVencimento()");

                        //AÇÃO
                        formulario.appendChild(div);
                        div.appendChild(label);
                        div.appendChild(input);
                        }else{
                        //SETANDO ONDE VAI SER ADICIONADO OS NOVOS CAMPOS
                        var formulario = document.getElementById('content');

                        //CAMPOS QUE SERAO INCREMENTADOS
                        var div = document.createElement("div");
                        div.setAttribute("class", "distanciaTop1");

                        var label = document.createElement("label");
                        label.textContent = "Data de Vencimento "+c+": ";

                        var input = document.createElement("input");
                        input.setAttribute("type", "date");
                        input.setAttribute("id", "dataVencimentoParc"+c);
                        input.setAttribute("name", "dataVencimentoParc"+c);

                        //AÇÃO
                        formulario.appendChild(div);
                        div.appendChild(label);
                        div.appendChild(input);
                        }
                    }
                    
                }

                document.getElementById("popup-1").classList.toggle("active");
            }

            //FUNÇÃO PARA ATRIBUIR A DATA DE VENCIMENTO DAS PARCELAS CONFORME DIA BASE ESPECIFICADO
            function diaBaseVencimento() {
                var dBase = document.getElementById('diaBase').value;
                if (dBase > 31) {
                    alert('Por favor insira um dia válido (de 1 a 31)');
                    return false;
                }
                var qtdeParcelas = document.getElementById('parcelas').value;
                var data_lanc = document.getElementById('dataLancamento').value;
                if (data_lanc == null || data_lanc == '') {
                    var dt = new Date();
                    var dia = dt.getDate();
                    if (dia >= 1 && dia <= 9) {
                        var vZero =  "0";
                        var Dia = vZero.concat(dia);
                    }else{
                        Dia = dia;
                    }
                    var Mes = dt.getMonth()+1;
                    var Ano = dt.getFullYear();
                    var DataCompleta = Ano+'-'+Mes+'-'+Dia;
                    document.getElementById('dataLancamento').value = DataCompleta;
                    var dataLanc = document.getElementById('dataLancamento').value;
                    alert('Devido a não ter especificado a Data de Lançamento, foi utilizada como base a data atual');
                }else{
                    var dataLanc = data_lanc;
                }
                var dt = [];
                var dia = [];
                var mes = [];
                var ano = [];
                var dataCompleta = [];
                var addMes = [];
                var mesN = [];
                var v_mesN = [];
                var zero = 0;

                for (var p = 1; p <= qtdeParcelas; p++) {
                    dt[p] = new Date(dataLanc);
                    dia[p] = dBase;
                    addMes[p] = p + 1;
                    mes[p] = dt[p].getMonth()+addMes[p];
                        if (mes[p] >= 13) {
                            mesN[p] = parseInt(mes[p]) - 12;
                                if (mesN[p] <= 9) {
                                    v_mesN[p] = "" + zero + mesN[p];
                                }else{
                                    v_mesN[p] = mesN[p];
                                }
                            ano[p] = dt[p].getFullYear()+1;
                            dataCompleta[p] = ano[p]+'-'+v_mesN[p]+'-'+dia[p];
                        }else{
                            ano[p] = dt[p].getFullYear();
                            dataCompleta[p] = ano[p]+'-'+mes[p]+'-'+dia[p];
                        }
                    document.getElementById('dataVencimentoParc'+p).value = dataCompleta[p];
                 }
            }

            function addDataVencimento(date) {
                var parN = document.getElementById('parcelas').value;
                var ultDataVenc = document.getElementById('dataVencimentoParc'+parN).value;
                return document.getElementById('dataVencimento').value = ultDataVenc;
            }
        </script>

        

    </head>
    <body class="fundo1">
        <!-- NAVBAR -->
        <div> 
            <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #8B0000">
                <a href="../index.php"><img src="../img/covilFaturamento.png" height="50px" width="110px"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

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
                <li class="nav-item active">
                    <a class="nav-link active" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true" title="Adicionar novo parceiro"><i class="fas fa-plus" style="color: red;"></i></a>
                </li>
            </ul>
        </div>
        <!--FIM SEPARADOR-->

        <!--INICIO ADICIONAR SAIDA-->
        <div class="tab-content" id="v-pills-tabContent">
            <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                <div class="FinalizarDiaMargem">
                    <img src="../img/covilLogin.png" width="180px;" height="100px;" style="display: block; margin-left: auto; margin-right: auto;">
                    <h2 style="text-align: center; margin-left: 1%;"><font color="yellow">Saída de Mercadoria</font></h1><br><br>
                    <form class="needs-validation" method="POST" action="Estoque/cadastrarSaida.php?finalizarDia=1" novalidate target="_blank">
                        <div class="form-row">
                        	<input type="hidden" name="dataeHora" id="dataeHora" value="<?php echo $dataHora ?>">
                            <div class="col-md-1 mb-3">
                                <span><font size="2" color="yellow"><b>Número Nota</b></font></span>
                                <input type="text" class="form-control" id="numeroNota" name="numeroNota" value="<?php echo $ProxNumeroNota ?>" readonly>		
                            </div>

                            <div class="FinalizarDiaDistancia1 col-md-2 mb-3">
                                <span><font size="2" color="yellow">Data de Lançamento</font></span>
                                <input type="date" class="form-control" id="dataLancamento" name="dataLancamento" value="<?php echo $v_finalizarDia ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-md-3 mb-3">
                                <span><font size="2" color="yellow">Código Fornecedor</font></span>
                                <input type="text" class="form-control" id="codigoFornecedor" name="codigoFornecedor" value="C00001 - CONSUMIDOR FINAL" list="listaPN">
                            </div>

                            <div class="distancia2 col-md-2 mb-3">
                                <span><font size="2" color="yellow">Data de Vencimento</font></span>
                                <input type="date" class="form-control" id="dataVencimento" name="dataVencimento" value="<?php echo $dataVencSaida ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-md-1 mb-3">
                                <span><font size="2" color="yellow">Depósito</font></span>
                                <select type="text" class="form-control" id="deposito" name="deposito">
                                    <?php while ($linhaOWHS = mysqli_fetch_assoc($resultadoOWHS)) { ?>
                                        <option value="<?php echo $linhaOWHS['idDeposito'] ?>"><?php echo $linhaOWHS['nomeDeposito']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="distancia3 col-md-1 mb-3">
                                <span><font size="2" color="yellow">Parcelas</font></span>
                                <select type="text" class="form-control" id="parcelas" name="parcelas"  onblur="inserirDataVencimento()">
                                    <option value="0">Insira</option>
                                    <option value="1" selected>À vista</option>
                                    <option value="2">2x</option>
                                    <option value="3">3x</option>
                                    <option value="4">4x</option>
                                    <option value="5">5x</option>
                                    <option value="6">6x</option>
                                    <option value="7">7x</option>
                                    <option value="8">8x</option>
                                    <option value="9">9x</option>
                                    <option value="10">10x</option>
                                    <option value="11">11x</option>
                                    <option value="12">12x</option>
                                </select>
                            </div>
                        </div>

                        <table class="distanciaTop table table-sm table-dark">
                            <thead>
                                <tr>
                                    <th scope="col">Produto</th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th scope="col">Quantidade</th>
                                    <th scope="col">Uni Medida</th>
                                    <th scope="col">Valor Unit</th>
                                    <th scope="col">Valor Total</th>
                                </tr>
                            </thead>
                        </table>

                        
                            <!----------------------INICIO DATALIST----------------------->
                            <!--PRODUTO-->
                            <datalist id="listaItens">
                                <?php while ($linhaOITM = mysqli_fetch_assoc($resultadoOITM)) { ?>
                                    <option value="<?php echo $linhaOITM['idProduto'] . '-' . $linhaOITM['nomeProduto']. '(' . $linhaOITM['codUnidadeMedida'].')'; ?>"></option>
                                <?php } ?>
                            </datalist>

                            <!--UNIDADE DE MEDIDA-->
                            <datalist id="listaUnidadeMedida" >
                                <?php while ($linhaUnidadeMedida = mysqli_fetch_assoc($resultadoUnidadeMedida)) { ?>
                                    <option><?php echo $linhaUnidadeMedida['nomeUnidade']; ?></option>
                                <?php } ?>
                            </datalist>

                            <!--PARCEIRO DE NEGOCIO-->
                            <datalist id="listaPN">
                                <?php while ($linhaNome = mysqli_fetch_assoc($resultadoIdOcrd)) { ?>
                                    <option value="<?php echo $linhaNome['idParceiro'] . ' - ' . $linhaNome['nomeParceiro']; ?>"></option>
                                <?php } ?>
                            </datalist>
                            <!----------------------FIM DATALIST----------------------->
                            <div id="formulario">
                            <input type="hidden" id="quantidadeLinhas" name="quantidadeLinhas" value="<?php echo $numLinhaFat ?>">

                            <?php
                            $contLinha = 0;
                            while ($linhaFaturamento = mysqli_fetch_assoc($resultadoFaturamentoFechado)) {
                            	$contLinha = $contLinha + 1;
                                $Item = $linhaFaturamento['idProduto'];
                                $analiseProduto = substr($linhaFaturamento['idProduto'], 0, 1);
                                if ($analiseProduto == 9) {
                                    $tabelaOITT = "SELECT * FROM OITT WHERE idFicha = '$Item';";
                                    $resultadoTabelaOITT = mysqli_query($conexao, $tabelaOITT);
                                    $linhaOITT = mysqli_fetch_assoc($resultadoTabelaOITT);
                                    $produto = $linhaOITT['idFicha'].'-'.$linhaOITT['nomeFicha'].'(UN)';
                                    $codUnidadeMedida = 'UN';
                                }else{
                                    $codUnidadeMedida = $linhaFaturamento['nomeUnidade'];
                                    $produto = $linhaFaturamento['idProduto'].' - '.$linhaFaturamento['nomeProduto'].'('.$codUnidadeMedida.')'; 
                                }
                            	$quantidade = $linhaFaturamento['quantidade'];
                            	$vUnitario = ($linhaFaturamento['vendaTotal']/$quantidade);
                            	$valorUnitario = number_format($vUnitario, 2, ',', '.');
                            	$valorLinha = number_format($linhaFaturamento['vendaTotal'], 2, ',', '.');
	                        	echo "<div id='linha$contLinha' class='form-row' >" 
	                        		."<div class='col-md-3 mb-1'>"
	                                ."<input type='text' class='form-control' id='produto$contLinha' name='produto$contLinha' list='listaItens' value='$produto'>"
		                            ."</div>"
		                            ."<div class='FinalizarDiaDistanciaQuantidade col-md-1 mb-1'>"
		                                ."<input type='number' class='form-control' id='quantidade$contLinha' name='quantidade$contLinha' onblur='soma($contLinha), calcularTotalFinal()' min='1' max='9999' value='$quantidade'>"
		                            ."</div>"
		                            ."<div class='distanciaUM col-md-1 mb-1'>"
		                                ."<input type='text' class='form-control' id='grupoUnidadeMedida$contLinha' name='grupoUnidadeMedida$contLinha' list='listaUnidadeMedida' value='$codUnidadeMedida'>"
		                            ."</div>"
		                            ."<div class='FinalizarDiadistanciaUnitario col-md-1 mb-1'>"
		                                ."<input type='text' class='form-control' id='valorUnitario$contLinha' name='valorUnitario$contLinha' onblur='soma($contLinha), calcularTotalFinal()' value='$valorUnitario'>"
		                            ."</div>"
		                            ."<div class='FinalizarDiaDistanciaTotal col-md-1 mb-1'>"
		                                ."<input type='text' style='text-align: center; font-size: 13px;' class='form-control' id='valorTotal$contLinha' name='valorTotal$contLinha' value='$valorLinha'>"
		                            ."</div>"
		                            ."<div>"
                                    ."<button type='button' class='btn btn-danger botaoExcluirLinha' id='botaoExcluir1' name='botaoExcluir1' onclick='excluirLinha('linha$contLinha')'>X</button>"
                                    ."</div>"
		                        ."</div>";
                            }
                          
                        ?>
                    	</div>

                        <!-- INICIO LINHA DO VALOR TOTAL AUTOCOMPLETE -->
                         <div class="form-row" id="formulario">
                            <div class="col-md-3 mb-1">
                                <input type="hidden" class="form-control">
                            </div>
                            <div class="FinalizarDiaDistanciaQuantidade col-md-1 mb-1">
                                <input type="hidden" class="form-control">
                            </div>


                            <div class="distanciaUM col-md-1 mb-1">
                                <input type="hidden" class="form-control">
                            </div>


                            <div class="FinalizarDiadistanciaUnitario col-md-1 mb-1">
                                <input type="hidden" class="form-control">
                            </div>

                            <div class="FinalizarDiaDistanciaTotal col-md-1 mb-1">
                                <input type="text" style="text-align: center; font-size: 13px; background-color: #8B0000; color: white;" class="form-control" id="totalNota" name="totalNota" value="<?php echo number_format($linhaValorTotal['valorTotal'], 2, ',', '.'); ?>" readonly>

                            </div>
                        </div>
                        <!-- FIM LINHA DO VALOR TOTAL AUTOCOMPLETE -->

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

                        <!-- INICIO TABELA DE DATA DE VENCIMENTO PARCELAS -->
                            <div class="popup" id="popup-1">
                                <div class="overlay"></div>
                                <div class="content" id="content">
                                    <h2>Parcelas</h2>
                                    <span>Dia a ser baseado</span>
                                    <input type="number" name="diaBase" id="diaBase" min="1" max="31" onblur="diaBaseVencimento(), addDataVencimento()"><br>
                                    <div class="close-btn" onclick="inserirDataVencimento()">&times;</div>
                                    <div class="distanciaTop1">
                                    	<label>Data de Vencimento 1: </label>
                                    	<input type="date" id="dataVencimentoParc1" name="dataVencimentoParc1" onblur="addDataVencimento()" value="<?php echo $dataVencSaida ?>">
                                    </div>
                                </div>
                            </div>
                        <!-- FIM TABELA DE DATA DE VENCIMENTO PARCELAS -->
                    </form>
                </div>
            </div><br>
            <!--FIM ADICIONAR SAIDA-->
        </div>

        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
    </body>
</html>
<?php 
