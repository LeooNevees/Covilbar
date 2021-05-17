<?php
include('../v_login.php');

//CONEXAO BANCO DE DADOS
include '../conexao.php';

date_default_timezone_set('America/Sao_Paulo');
$data = date('d/m');
$dateNow = date('Y-m-d');


//FORMATANDO HORA PARA PEGAR AS COMANDAS DO RESPECTIVO DIA
$HoraAtual = date('H:i');
if ($HoraAtual >= '00:00' && $HoraAtual < '07:00') {
    $dataAtual = date('Y-m-d', strtotime('-1 days'));
}else{
    $dataAtual = date('Y-m-d');
}
$dataHora = $dataAtual.' 07:00';


//SELECTS PARA POPULAR O DATALIST
$tabelaOITM = "
SELECT T0.idProduto, T0.nomeProduto
FROM OITM T0
WHERE T0.cancelado = 'N'
AND T0.itemVenda = 'Y'

UNION ALL

SELECT T1.idFicha, T1.nomeFicha
FROM OITT T1
WHERE T1.cancelado = 'N';";

$resultadoOITM = mysqli_query($conexao, $tabelaOITM);

$tabelaOCMD = "
SELECT idComanda AS 'idComanda', comanda AS 'comanda', nomeCliente AS 'nomeCliente', DATE_FORMAT(`horaEntrada`, '%H:%i') AS 'horaEntrada', valorTotal AS 'valorTotal'
FROM OCMD 
WHERE cancelado = 'N' 
AND statusPagamento = 'ABERTO'
AND horaEntrada >= '$dataHora'
ORDER BY comanda ASC;";
$resultadoOCMD = mysqli_query($conexao, $tabelaOCMD);

$tabelaRetornoOCMD = "
SELECT idComanda AS 'idComanda', comanda AS 'comanda', nomeCliente AS 'nomeCliente', DATE_FORMAT(`horaEntrada`, '%H:%i') AS 'horaEntrada', valorTotal AS 'valorTotal'
FROM OCMD 
WHERE cancelado = 'N' 
AND statusPagamento = 'ABERTO' 
ORDER BY comanda ASC;";
$resultadoRetornoOCMD = mysqli_query($conexao, $tabelaRetornoOCMD);
$analiseRetornoOCMD = mysqli_affected_rows($conexao);

$tabelaCMD1 = "SELECT * FROM CMD1 WHERE cancelado = 'N' ORDER BY comanda ASC;";
$resultadoCMD1 = mysqli_query($conexao, $tabelaCMD1);


$tabelaFaturamentoAberto = "
SELECT T1.idProduto AS 'idProduto', T2.nomeProduto AS 'nomeProduto', SUM(T1.quantidade) AS 'quantidade', SUM(T1.totalLinha) AS 'vendaTotal'
FROM CMD1 T1

LEFT JOIN OCMD T0
ON T0.idComanda  = T1.idComanda

LEFT JOIN OITM T2
ON T1.idProduto = T2.idProduto

WHERE T0.cancelado = 'N'
AND T0.horaEntrada >= '$dataHora'

GROUP BY T1.idProduto;";
$resultadoFaturamentoAberto = mysqli_query($conexao, $tabelaFaturamentoAberto);

$tabelaComandaAberta = "
SELECT COUNT(T0.idComanda) AS 'quantidadeComanda', SUM(T0.valorTotal) AS 'valorTotal' 
FROM OCMD T0
WHERE T0.cancelado = 'N'
AND T0.horaEntrada >= '$dataHora';";

$resultadoComandaAberta = mysqli_query($conexao, $tabelaComandaAberta);
$linhaComandaAberta = mysqli_fetch_assoc($resultadoComandaAberta);
$numComandaAberta = $linhaComandaAberta['quantidadeComanda'];
$valorComandaAberta = number_format($linhaComandaAberta['valorTotal'], 2, ',', '.');

$tabelaVisualizarComanda = "
SELECT idComanda AS 'idComanda', comanda AS 'comanda', nomeCliente AS 'nomeCliente', date_format(horaEntrada, '%d/%m/%Y') as 'horaEntrada', valorTotal AS 'valorTotal', valorPago AS 'valorPago', cortesia AS 'cortesia'
FROM OCMD
WHERE cancelado = 'N'";
$resultadoVisualizarComanda = mysqli_query($conexao, $tabelaVisualizarComanda);

$contador = 0;

//RELATÓRIO DAS COMANDAS INADIMPLENTES
$tabelaInadimplentes = "
SELECT idComanda AS 'idComanda', comanda AS 'comanda', nomeCliente AS 'nomeCliente', DATE_FORMAT(`horaEntrada`, '%H:%i') AS 'horaEntrada', valorTotal AS 'valorTotal'
FROM OCMD
WHERE cancelado = 'N'
AND valorPago = 0
AND valorDesconto = 0
ORDER BY comanda ASC;";
$resultadoInadimplentes = mysqli_query($conexao, $tabelaInadimplentes);
?>

<!doctype html>
<html lang="pt-br">
    <head>
        <!-- ICONE NA BARRA DO NAVEGADOR-->
        <link rel="shortcut icon" href="../img/zillaMonstro.png">

        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Covil Bar</title>

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
        <link rel="stylesheet" href="style.css">

        <!-----ICONES------------>
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <script src="https://kit.fontawesome.com/70c48f08c7.js"></script>
        <script type="text/javascript">
            var contador = 1;
            var contadorLinha = [];

            //FUNÇÃO PARA VALIDAR A ADIÇÃO DE NOVA COMANDA
            function validar() {
                var finalizar = 0;

                if (document.getElementById('comanda').value == '' || document.getElementById('comanda').value == null) {
                    alert('Por favor insira o número da Comanda');
                    return false;
                    finalizar = 1;
                }

                if (document.getElementById('nomeCliente').value == '' || document.getElementById('nomeCliente').value == null) {
                    alert('Somente notificação: Nome do cliente vazio');
                }

                if (document.getElementById('horaEntrada').value == '' || document.getElementById('horaEntrada').value == null) {
                    alert('Por favor insira uma hora de entrada válida');
                    return false;
                    finalizar = 1;
                }

                for (var i = 1; i <= contador; i++) {
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

                    if (document.getElementById('valorUnitario'+i).value == '' || document.getElementById('valorUnitario'+i).value == 'R$') {
                        alert('Por favor insira o Valor Unitario na linha ' + i);
                        return false;
                        finalizar = 1;
                    }

                }  

                document.getElementById('quantidadeLinhas').value = contador;

                if (finalizar == 0) {
                    var resultadoAdicionarComanda = confirm('Realmente deseja adicionar a comanda?');
                    if (resultadoAdicionarComanda == false) {
                        alert('Ação cancelada');
                        setTimeout(function () {
                        window.location.href = "http://localhost/CovilBar/sistema/index.php";
                        }, 1);
                        return false;
                    }else{
                        alert('Cadastrando a comanda');
                        setTimeout(function () {
                        window.location.href = "http://localhost/CovilBar/sistema/index.php";
                        }, 500);
                    }
                }
            }

            //FUNÇÃO PARA VALIDAR OS ITENS ADICIONADOS NAS COMANDAS
            function validarCMD1(iComanda) {
                var finalizarValidar = 0;
                var vNLinha = document.getElementById(iComanda+'-quantidadeLinhas').value;
                var validadorLinhas = parseFloat("0");

                //ANÁLISE SE A PESSOA ESTÁ TENTANDO ALTERAR A COMANDA SEM NENHUMA LINHA DE PRODUTO
                for (var b = 1; b <= vNLinha; b++) {
                    var analiseProd = document.getElementById(iComanda+'-produto'+b);
                    if (analiseProd !== null) {
                        validadorLinhas = parseFloat(validadorLinhas) + parseFloat("1");
                    }else{
                        validadorLinhas = parseFloat(validadorLinhas) + parseFloat("0");
                    }  
                }

                if (validadorLinhas == 0) {
                    alert('Para alterar é preciso ter pelo menos uma linha de produto');
                    finalizarValidar = 1;
                    return false;
                }


                //ANÁLISE A PESSOA INSERIU DADOS EM TODOS OS CAMPOS
                for (var o = 1; o <= vNLinha; o++) {
                    var analisadorProd = document.getElementById(iComanda+'-produto'+o);
                    if (analisadorProd !== null) {
                        if (document.getElementById(iComanda+'-produto'+o).value == '' || document.getElementById(iComanda+'-produto'+o).value == null) {
                            alert('Por favor insira um produto na linha '+o);
                            finalizarValidar = 1;
                            return false;
                        }

                        if (document.getElementById(iComanda+'-quantidade'+o).value == '' || document.getElementById(iComanda+'-quantidade'+o).value == null) {
                            alert('Por favor insira uma quantidade na linha '+o);
                            finalizarValidar = 1;
                            return false;
                        }

                        if (document.getElementById(iComanda+'-valorUnitario'+o).value == '' || document.getElementById(iComanda+'-valorUnitario'+o).value == null || document.getElementById(iComanda+'-valorUnitario'+o).value == 'R$') {
                            alert('Por favor insira um valor unitario na linha '+o);
                            finalizarValidar = 1;
                            return false;
                        }
                    }else{
                        continue;
                    }

                }

                if (finalizarValidar == 0) {
                    var resultadoValidar = confirm('Realmente deseja fazer a alteração?');
                    if (resultadoValidar == false) {
                        alert('Ação cancelada');
                        return false;
                    }else{
                        alert('Alterando comanda');
                        setTimeout(function () {
                            window.location.href = "http://localhost/CovilBar/sistema/index.php";
                        }, 600);
                    }
                }

            }

            //FUNÇÃO PARA VALIDAR A FINALIZAÇÃO DA COMANDA
            function validarFinalizarComanda() {
                var finComanda = 0;

                if (document.getElementById('valorFornecido').value == '' || document.getElementById('valorFornecido').value == null) {
                    alert('Por favor insira o valor Fornecido');
                    return false;
                    finComanda = 1;
                }

                var valorFloat = document.getElementById('trocoComanda').value.replace('.', '');
                var validadoFloat = valorFloat.replace(',', '.');

                if (validadoFloat < 0) {
                    alert('Valor final não compatível. Por favor reveja os valores inseridos');
                    return false;
                    finComanda = 1;
                }

                if (finComanda == 0) {
                   var resultado = confirm('Realmente deseja finalizar a comanda?');
                    if (resultado == false) {
                        alert('Cancelando Ação');
                        setTimeout(function () {
                            window.location.href = "http://localhost/CovilBar/sistema/index.php";
                        }, 1);
                        return false;
                    }else{
                        alert('Finalizando a Comanda');
                        setTimeout(function () {
                            window.location.href = "http://localhost/CovilBar/sistema/index.php";
                        }, 600);
                    }
                }
            }

            //FUNÇÃO PARA ABRIR O NOVA COMANDA E ATRIBUIR A HORA NO RESPECTIVO CAMPO
            function novaComanda() {
                var dt = new Date();
                var hora = dt.getHours();
                var minuto = dt.getMinutes();


                var Hora = null;
                var Minuto = null;
                var v_horaCompleta = null;
                var zero = '0';

                if (hora >= 1 && hora <= 9) {
                    Hora = zero + hora;
                }else{
                    Hora = hora;
                }


                if (minuto >= 1 && minuto <= 9) {
                    Minuto = zero + minuto;
                }else{
                    Minuto = minuto;
                }

                v_horaCompleta = Hora + ':' + Minuto;

                document.getElementById('horaEntrada').value = v_horaCompleta;
                document.getElementById('popup-1').classList.toggle("active");
            }


            //FUNÇÃO PARA CONVERTER O NUMEROS DE FLOAT PARA MOEDA
            function MascaraMoeda(objTextBox, Separadormilesimo, Separadordecimal, e) {
                if (Separadormilesimo == 0 || Separadordecimal == 0) {
                    var SeparadorMilesimo = '.';
                    var SeparadorDecimal = ',';
                }else{
                    var SeparadorMilesimo = Separadormilesimo;
                    var SeparadorDecimal = Separadordecimal;
                }
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

            //FUNÇÃO PARA REALIZAR A SOMA TOTAL DAS LINHAS -- NO MOMENTO DE ADICIONAR COMANDA
            function soma(qtde, valor, tot) {

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

            //FUNÇÃO PARA PEGAR O VALOR TOTAL DE TODAS AS LINHAS E ATRIBUIR AO VALOR FINAL TOTAL DA COMANDDA -- NO MOMENTO DE ADICIONAR COMANDA
            function calcularTotalFinal() {
                var valorNotaTotal = parseFloat("0");
                for (var t = 1; t <= contador; t++) {
                    var valoralterado = document.getElementById('valorTotal'+t).value.replace('.', '');
                    var valorAlterado = valoralterado.replace(',', '.');
                    valorNotaTotal = parseFloat(valorNotaTotal) + parseFloat(valorAlterado);
                }

                var totalMod = valorNotaTotal.toLocaleString('pt-br', {minimumFractionDigits: 2});
                return document.getElementById('totalNota').value = totalMod;
            }

            //FUNÇÃO PARA REALIZAR A SOMA DAS LINHAS DAS COMANDAS -- COMANDAS JÁ ADICIONADAS
            function somaCMD1(idC, idL) {
                var p_quantidade = '-quantidade';
                var v_quantidade = idC+p_quantidade+idL;

                var p_valorUnit = '-valorUnitario';
                var v_valorUnit = idC+p_valorUnit+idL;

                var p_total = '-valorTotal';
                var v_total = idC+p_total+idL;

                var quantidade = parseFloat(document.getElementById(v_quantidade).value.replace(',', '.'));
                if (quantidade == null || quantidade == '') {
                    quantidade = 0;
                }

                var valorunitario = document.getElementById(v_valorUnit).value.replace('.', '');
                var valorUnitario = valorunitario.replace(',', '.');
                if (isNaN(valorUnitario)) {
                    valorUnitario = 0;
                }

                var total = quantidade * valorUnitario;

                var totalMod = total.toLocaleString('pt-br', {minimumFractionDigits: 2});
                if (totalMod == 'NaN') {
                    totalMod = 0;
                }

                var p_total = '-valorTotal';
                var v_total = idC+p_total+idL;
                return document.getElementById(v_total).value = totalMod;
                
            }


            //FUNÇÃO PARA PEGAR O VALOR TOTAL DE TODAS AS LINHAS E ATRIBUIR AO VALOR FINAL TOTAL DA COMANDDA -- COMANDAS JÁ ADICIONADAS
            function calcularTotalFinalCMD1(v_idCom) {
                var valorNotaTotal = parseFloat("0");
                var num_linha = null;

                if (contadorLinha[v_idCom] == null || contadorLinha[v_idCom] == '') {
                    num_linha = document.getElementById(v_idCom+'-quantidadeLinhas').value;
                }else{
                    num_linha = contadorLinha[v_idCom];
                }

                for (var t = 1; t <= num_linha; t++) {
                    var valorTot = document.getElementById(v_idCom+"-valorTotal"+t);

                    if (valorTot !== null) {
                        var valoralterado = document.getElementById(v_idCom+'-valorTotal'+t).value.replace('.', '');
                        var valorAlterado = valoralterado.replace(',', '.');
                        valorNotaTotal = parseFloat(valorNotaTotal) + parseFloat(valorAlterado);
                    }else{
                        continue;
                    }
                }

                var totalMod = valorNotaTotal.toLocaleString('pt-br', {minimumFractionDigits: 2});
                return document.getElementById(v_idCom+'-totalNota').value = totalMod;
            }


            //FUNÇAÕ PARA CRIAR LINHA DE ITEM NO MOMENTO DE ADICIONAR A COMANDA
            function criar() {
                contador = contador + 1;

                //SETANDO ONDE VAI SER ADICIONADO OS NOVOS CAMPOS
                var formulario = document.getElementById('formularioCriar');

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
                div.setAttribute("class", "IndexDistanciaQuantidade col-md-2 mb-1");

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

                //-------------------------VALOR UNITARIO-----------------------------//
                //CAMPOS QUE SERAO INCREMENTADOS
                var div = document.createElement("div");
                div.setAttribute("class", "IndexDistanciaUnitario col-md-2 mb-1");

                var input = document.createElement("input");
                input.setAttribute("type", "text");
                input.setAttribute("class", "form-control");
                input.setAttribute("id", "valorUnitario" + contador);
                input.setAttribute("name", "valorUnitario" + contador);
                input.setAttribute("onKeyPress", "return(MascaraMoeda(this,'.',',',event))");
                input.setAttribute("onblur", "soma('quantidade" + contador + "', 'valorUnitario" + contador + "', 'valorTotal" + contador + "'), calcularTotalFinal()");
                input.setAttribute("value", "R$");

                //AÇÃO
                formulario.appendChild(divLinha);
                divLinha.appendChild(div);
                div.appendChild(input);

                //-------------------------TOTAL-----------------------------//
                //CAMPOS QUE SERAO INCREMENTADOS
                var div = document.createElement("div");
                div.setAttribute("class", "distanciaTotal col-md-2 mb-1");

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
            }

            //FUNÇÃO PARA CRIAR LINHA NA COMANDA JÁ QUANDO ESTÁ ADICIONADA
            function criarOculto(v_form, linha) {
                var cContador = 0;

                if (contadorLinha[v_form] == null || contadorLinha[v_form] == '') {
                    contadorLinha[v_form] = linha; 
                    contadorLinha[v_form] += 1;
                    cContador = contadorLinha[v_form];
                }else{
                    contadorLinha[v_form] += 1;
                    cContador = contadorLinha[v_form];
                }
                                            
                //SETANDO ONDE VAI SER ADICIONADO OS NOVOS CAMPOS
                var formulario = document.getElementById('formulario'+v_form);

                //-------------------------DIV LINHA-----------------------------//
                //CAMPOS QUE SERAO INCREMENTADOS
                var divLinha = document.createElement("div");
                divLinha.setAttribute("id", "linha"+cContador); 
                divLinha.setAttribute("class", "form-row");    
                
                //AÇÃO
                formulario.appendChild(divLinha);

                //-------------------------PRODUTO-----------------------------//
                //CAMPOS QUE SERAO INCREMENTADOS
                var div = document.createElement("div");
                div.setAttribute("class", "col-md-5 mb-1");

                var input = document.createElement("input");
                input.setAttribute("type", "text");
                input.setAttribute("class", "form-control");
                input.setAttribute("id", v_form+"-produto" + cContador);
                input.setAttribute("name", v_form+"-produto" + cContador);
                input.setAttribute("list", "listaItens");

                //AÇÃO
                formulario.appendChild(divLinha);
                divLinha.appendChild(div);
                div.appendChild(input);

                //-------------------------QUANTIDADE-----------------------------//
                //CAMPOS QUE SERAO INCREMENTADOS
                var div = document.createElement("div");
                div.setAttribute("class", "distanciaQuantidade2 col-md-2 mb-1");

                var input = document.createElement("input");
                input.setAttribute("type", "number");
                input.setAttribute("class", "form-control");
                input.setAttribute("id", v_form+"-quantidade" + cContador);
                input.setAttribute("name", v_form+"-quantidade" + cContador);
                input.setAttribute("onblur", "somaCMD1("+v_form+", "+cContador+"), calcularTotalFinalCMD1("+v_form+")");

                //AÇÃO
                formulario.appendChild(divLinha);
                divLinha.appendChild(div);
                div.appendChild(input);

                //-------------------------VALOR UNITARIO-----------------------------//
                //CAMPOS QUE SERAO INCREMENTADOS
                var div = document.createElement("div");
                div.setAttribute("class", "distanciaUnitario2 col-md-2 mb-1");

                var input = document.createElement("input");
                input.setAttribute("type", "text");
                input.setAttribute("class", "form-control");
                input.setAttribute("id", v_form+"-valorUnitario" + cContador);
                input.setAttribute("name", v_form+"-valorUnitario" + cContador);
                input.setAttribute("onKeyPress", "return(MascaraMoeda(this,'.',',',event))");
                input.setAttribute("onblur", "somaCMD1("+v_form+", "+cContador+"), calcularTotalFinalCMD1("+v_form+")");
                input.setAttribute("value", "R$");

                //AÇÃO
                formulario.appendChild(divLinha);
                divLinha.appendChild(div);
                div.appendChild(input);

                //-------------------------TOTAL-----------------------------//
                //CAMPOS QUE SERAO INCREMENTADOS
                var div = document.createElement("div");
                div.setAttribute("class", "IndexDistanciaTotal2 col-md-2 mb-1");

                var input = document.createElement("input");
                input.setAttribute("type", "text");
                input.setAttribute("style", "text-align: center; font-size: 13px;");
                input.setAttribute("class", "form-control");
                input.setAttribute("id", v_form+"-valorTotal" + cContador);
                input.setAttribute("name", v_form+"-valorTotal" + cContador);
                input.setAttribute("readonly", "true");

                //AÇÃO
                formulario.appendChild(divLinha);
                divLinha.appendChild(div);
                div.appendChild(input);

                //-------------------------EXCLUIR-----------------------------//
                //CAMPOS QUE SERAO INCREMENTADOS
                var div = document.createElement("div");

                var button = document.createElement("button");
                button.setAttribute("type", "button");
                button.setAttribute("class", "btn btn-danger IndexBotaoExcluirLinha");
                button.setAttribute("id", v_form+"-botaoExcluir" + cContador);
                button.setAttribute("name", v_form+"-botaoExcluir" + cContador);
                button.setAttribute("onclick", "excluirLinha("+v_form+", "+cContador+")");
                button.textContent = "X";


                //AÇÃO
                formulario.appendChild(divLinha);
                divLinha.appendChild(div);
                div.appendChild(button);

                //RETORNANDO NUMERO DE LINHAS 
                document.getElementById(v_form+"-quantidadeLinhas").value = cContador;
                
            }

            //FUNÇÃO PARA MOSTRAR A DIV DE ADICIONAR COMANDA
            function abrirComanda(idComanda) {
                var ocmd = 'OCMD';
                var numComanda = ocmd+idComanda;

                document.getElementById(numComanda).classList.toggle("active");
            }


            //FUNÇÃO PARA FINALIZAR O PAGAMENTO DA COMANDA
            function abrirFechamento(aComanda) {
                //0 VEM DO BOTAO FECHAR
                if (aComanda == 0) {
                    aComanda = document.getElementById('retornoIdComanda').value;
                }
                var validadorLinhasFaturamento = parseFloat("0");
                var vNLinhaFaturamento = document.getElementById(aComanda+'-quantidadeLinhas').value;

                //ANÁLISE SE A PESSOA ESTÁ TENTANDO ALTERAR A COMANDA SEM NENHUMA LINHA DE PRODUTO
                for (var ba = 1; ba <= vNLinhaFaturamento; ba++) {
                    var analiseProdFaturamento = document.getElementById(aComanda+'-produto'+ba);
                    if (analiseProdFaturamento !== null) {
                        validadorLinhasFaturamento = parseFloat(validadorLinhasFaturamento) + parseFloat("1");
                    }else{
                        validadorLinhasFaturamento = parseFloat(validadorLinhasFaturamento) + parseFloat("0");
                    }  
                }

                if (validadorLinhasFaturamento == 0) {
                    alert('Para finalizar é preciso ter pelo menos uma linha de produto');
                    return false;
                }

                
                document.getElementById('retornoIdComanda').value = aComanda;
                var string = '-totalNota';
                var stringVId = aComanda + string;
                var valorComandaAtual = document.getElementById(stringVId).value;
                document.getElementById('valorComanda').value = valorComandaAtual;
                document.getElementById('popup-pagamento').classList.toggle("active2");


            }

            //FUNÇÃO PARA RETORNAR O TROCO NO MOMENTO DO PAGAMENTO DA COMANDA
            function retornarTroco() {
                var vComanda = document.getElementById('valorComanda').value.replace('.', '');
                var valComanda = vComanda.replace(',', '.');
                if (isNaN(valComanda)) {
                    valComanda = 0;
                }

                var vDesconto = document.getElementById('desconto').value.replace('.', '');
                var valorDesconto = vDesconto.replace(',', '.');
                if (isNaN(valorDesconto)) {
                    valorDesconto = 0;
                }


                var vFornecido = document.getElementById('valorFornecido').value.replace('.', '');
                var valorFornecido = vFornecido.replace(',', '.');
                if (isNaN(valorFornecido)) {
                    valorFornecido = 0;
                }

                var somaComandaDesconto = valComanda - valorDesconto;
                var subValores = valorFornecido - somaComandaDesconto;

                var valorSubtraido = subValores.toLocaleString('pt-br', {minimumFractionDigits: 2});
                if (valorSubtraido == 'NaN') {
                    valorSubtraido = 0;
                }
                return document.getElementById('trocoComanda').value = valorSubtraido;
            }

            //FUNÇÃO PARA VALIDAR O CANCELAMENTO DA COMANDA
            function cancelarComanda() {
                var resultadoComanda = confirm('Realmente deseja cancelar a comanda?');
                if (resultadoComanda == false) {
                    alert('Ação cancelada');
                    setTimeout(function () {
                            window.location.href = "http://localhost/CovilBar/sistema/index.php";
                        }, 1);
                    return false;
                }else{
                    alert('Iniciando cancelamento');
                    setTimeout(function () {
                            window.location.href = "http://localhost/CovilBar/sistema/index.php";
                        }, 100);
                }
            }

            //FUNÇÃO PARA ABRIR O FORMULARIO DE FINALIZAMENTO DO DIA
            function finalizarDia() {
                /*var analiseRetOCMD = document.getElementById('analiseRetornoOCMD').value;
                if (analiseRetOCMD > 0) {
                    alert('Necessário finalizar todas as comandas para realizar essa etapa');
                    return false;
                }*/
               
                document.getElementById('popup-finalizarDia').classList.toggle("active2");
            }

            //FUNÇÃO PARA VALIDAR O DIA DO FORMULARIO DE FINALIZAR DIA
            function validarFinalizarDia() {
                if (document.getElementById('finalizarDia').value == '' || document.getElementById('finalizarDia').value == null) {
                    alert('Por favor insira uma data válida');
                    return false;
                }
                var resultadoFinalizarDia = confirm('Realmente deseja finalizar o dia?');
                if (resultadoFinalizarDia == false) {
                    alert('Ação cancelada');
                    setTimeout(function () {
                            window.location.href = "http://localhost/CovilBar/sistema/index.php";
                        }, 1);
                    return false;
                }else{
                    alert('Finalizando o dia');
                    //window.open('http://localhost/CovilBar/sistema/gerarCMD1.php','_blank'); -- SERVE PARA ABRIR O DOWNLOAD DA TABELA CMD1 DO DIA ATUAL
                    setTimeout(function () {
                            window.location.href = "http://localhost/CovilBar/sistema/index.php";
                        }, 100);
                }

            }

            //FUNÇÃO PARA ABRIR O RELATÓRIO DE FATURAMENTO DIÁRIO
            function faturamentoDia() {
                document.getElementById('popup-faturamentoDia').classList.toggle("active3");
            }

            function excluirLinha(xForm, xLinha) {
                $("#formulario"+xForm).find("#linha"+xLinha).remove();
                calcularTotalFinalCMD1(xForm);
            }

            function validarVisualizacao() {
                var analisador = 0;

                if (document.getElementById('idVisualizacaoComanda').value == '' || document.getElementById('idVisualizacaoComanda').value == null) {
                    alert('Número de comanda inválida. Por favor refaça o procedimento');
                    analisador = 1;
                    return false;
                }

                if (analisador == 0) {
                    setTimeout(function () {
                        alert("Comanda aberta na outra guia");
                        window.location.href = "http://localhost/CovilBar/sistema/index.php";
                    }, 500);
                }
            }

            function analiseCortesia() {
                var check = document.getElementById('cortesia').checked;
                if (check == true) {
                    document.getElementById('desconto').value = '0,00';
                    document.getElementById('desconto').readOnly = true;
                    document.getElementById('valorFornecido').value = '0,00';
                    document.getElementById('valorFornecido').readOnly = true;
                    document.getElementById('trocoComanda').value = '0,00';
                    document.getElementById('trocoComanda').readOnly = true;
                }else{
                    document.getElementById('desconto').readOnly = false;
                   document.getElementById('valorFornecido').readOnly = false;
                    document.getElementById('trocoComanda').readOnly = false;
                }
            }
        </script>
    </head>


    <body class="fundo1">

        <!-- INÍCIO NAVBAR -->
        <div> 
            <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #8B0000">
                <a href="index.php"><img src="../img/covilFaturamento.png" height="50px" width="110px"></a>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item active afastamentoNavBar">
                            <a class="nav-link" href="index.php">Início <span class="sr-only">(current)</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="Parceiros/index.php">Parceiros</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Estoque
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="Estoque/entrada.php"><font color="blue">Entrada</font></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="Estoque/saida.php"><font color="blue">Saida</font></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="Estoque/cadastroItem.php"><font color="blue">Cadastro do Item</font></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="Estoque/fichaTecnica.php"><font color="blue">Ficha Técnica</font></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="Estoque/deposito.php"><font color="blue">Depósitos</font></a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Financeiro
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="Financeiro/contasPagar.php"><font color="black">Contas a Pagar</font></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="Estoque/saida.php"><font color="black">Contas a Receber</font></a>
                            </div>
                        </li>
                    </ul>
                    <form class="form-inline my-2 my-lg-0">
                        <div>
                            <input class="form-control mr-sm-2" type="search" placeholder="Procurar" aria-label="Procurar">
                            <button class="btn btn-outline-light my-2 my-sm-0" type="submit">OK</button>&nbsp;&nbsp;&nbsp;
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo strtolower($_SESSION['usuario']) ?></button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="../logout.php">Logout</a>
                            </div>
                        </div>   
                    </form>
                </div>
            </nav>
        </div>

        <!-- FIM NAVBAR -->

       <!--INICIO SEPARADOR-->
        <div>
            <ul class="nav nav-tabs justify-content-center">
                <li class="nav-item">
                    <a class="nav-link active" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true" title="Adicionar comandas"><i class="fas fa-plus" style="color: red;"></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="v-pills-profile-tab" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false" title="Pesquisar comandas"><i class="fa fa-search" style="color: red;"></i></a>
                </li>
            </ul>
        </div>
        <!--FIM SEPARADOR-->

        <div class="tab-content" id="v-pills-tabContent">

        <!--INICIO TELA DE MANUSEIO DE COMANDAS-->        
        <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">

        <!--INICIO BOTAO ADICIONAR NOVA COMANDA -->
        <div class="alinhamentoComprar">
            <button type="button" class="btn btn-outline-danger distanciaTop1" onclick="novaComanda()" style="height: 100px; width: 100px;" title="Adicionar Nova Comanda"><font size="30px"><b><i class="fas fa-plus"></i></b></font></button>
            <button type="button" class="btn btn-outline-danger distanciaTop2" onclick="faturamentoDia()" style="height: 100px; width: 100px;" title="Verificar Faturamento"><font size="30px"><b><i class="fas fa-search-dollar"></i></b></font></button>
            <button type="button" class="btn btn-outline-success distanciaTop2" onclick="finalizarDia()" style="height: 100px; width: 100px;" title="Finalizar o Dia"><font size="30px"><b><i class="fas fa-donate"></i></b></font></button>
        </div>
        <!--FIM BOTAO ADICIONAR NOVA COMANDA -->


        <!-- INICÍO RETORNO DAS COMANDAS JÁ CADASTRADAS NO BANCO DE DADOS -->
        <div class="fundoComandas" id="comandas">
            <?php 
                while($linhaOCMD = mysqli_fetch_assoc($resultadoOCMD)){
                $v_idComanda = $linhaOCMD['idComanda'];
                $v_comanda = $linhaOCMD['comanda'];
                $v_nomeCliente = $retorno_nomeCliente = mb_convert_case($linhaOCMD['nomeCliente'], MB_CASE_TITLE, "UTF-8");
                $v_horaEntrada = $linhaOCMD['horaEntrada'];
                $v_valorTotal = number_format($linhaOCMD['valorTotal'], 2, ',', '.');

                echo "<div class='comanda' id='comanda$v_idComanda' style='cursor:pointer;' onclick='abrirComanda($v_idComanda)'>"
                    ."<form method='POST' action='finalizarComanda.php'>"
                    ."<input type='hidden' id='numeroComanda' name='numeroComanda' value='$v_idComanda'>"
                    ."<input type='number' id='comanda$v_idComanda' name='comanda$v_idComanda' class='mesaComanda form-group col-md-4 form-control' value='$v_comanda' style='color:red; font-weight: bold; font-size:20px;' readonly>" 
                    ."<span class='center' style='color:yellow;'>Cliente</span>"
                    ."<input type='text' id='nomeCliente$v_idComanda' name='nomeCliente$v_idComanda' class='center col-md-11 form-control' value='$v_nomeCliente' readonly>"   
                    ."<span class='center' style='color:yellow;'>Hora Entrada</span>"
                    ."<input type='time' id='horaEntrada$v_idComanda' name='horaEntrada$v_idComanda' class='center form-group col-md-4 form-control' value='$v_horaEntrada' readonly>"
                    ."<span class='centerValorTitle' style='color:yellow;'>Valor Gasto</span>"
                    ."<input type='text' id='valorGasto$v_idComanda' name='valorGasto$v_idComanda' class='centerValor col-md-6 form-control' value='$v_valorTotal' readonly>"
                    ."<a href='cancelarComanda.php?idComanda=$v_idComanda' type='button' onclick='return cancelarComanda()' class='centerButtonAdicionar btn btn-outline-danger' target='_blank' title='Cancelar'><i class='fa fa-close'></i></a>"
                    ."<button type='button' class='centerButtonAdicionar2 btn btn-outline-success' onclick='abrirFechamento($v_idComanda)' title='Finalizar'><i class='fa fa-check'></i></button>"
                    ."</form>"
                    ."</div>";
                }
            ?>
        </div><br>

        <!-- FIM RETORNO DAS COMANDAS JÁ CADASTRADAS NO BANCO DE DADOS -->


        <!--INICIO DIV COM A TABELA CMD1 OCULTA (COMANDAS JÁ CADASTRADAS)-->
        <?php
            while ($linhaRetornaOCMD = mysqli_fetch_assoc($resultadoRetornoOCMD)) {
                $retorno_idComanda = $linhaRetornaOCMD['idComanda'];
                $retorno_comanda = $linhaRetornaOCMD['comanda'];
                $retorno_nomeCliente = mb_convert_case($linhaRetornaOCMD['nomeCliente'], MB_CASE_TITLE, "UTF-8");
                
                $retorno_horaEntrada = $linhaRetornaOCMD['horaEntrada'];
                $retorno_valorTotal = number_format($linhaRetornaOCMD['valorTotal'], 2, ',', '.');

                echo "<div class='popup' id='OCMD$retorno_idComanda'>"
                        ."<div class='overlay'></div>"
                            ."<div class='content' id='content'>"
                                ."<div class='IndexMargem'>"
                                    ."<img src='../img/covilLogin.png' width='180px;' height='100px;' style='display: block; margin-left: auto; margin-right: auto;'>"
                                    ."<h2 style='text-align: center;'><font color='yellow'>Comanda $retorno_nomeCliente</font></h2><br><br>"
                                        ."<form class='needs-validation' method='POST' action='atualizarComanda.php' novalidate target='_blank'>"
                                            ."<div class='form-row' style='text-align: left;'>"
                                                ."<div class='col-md-2 mb-3'>"
                                                    ."<span><font size='2' color='yellow'><b>Comanda </b></font></span>"
                                                    ."<input type='text' class='form-control' id='comanda' name='comanda' value='$retorno_comanda' readonly>"
                                                    ."<input type='hidden' id='idComanda' name='idComanda' value='$retorno_idComanda'>"   
                                                ."</div>"
                                            ."</div>"
                                            ."<div class='form-row'>"
                                                ."<div class='col-md-5 mb-3' style='text-align: left;'>"
                                                    ."<span><font size='2' color='yellow'>Cliente </font></span>"
                                                    ."<input type='text' class='form-control' id='nomeCliente' name='nomeCliente' value='$retorno_nomeCliente' readonly>"
                                                ."</div>"
                                                ."<div class='IndexDistancia1 col-md-2 mb-3' >"
                                                    ."<span><font size='2' color='yellow'>Hora Entrada </font></span>"
                                                    ."<input type='time' class='form-control' id='$retorno_idComanda-horaEntrada' name='$retorno_idComanda-horaEntrada' value='$retorno_horaEntrada' readonly>"
                                                ."</div>"
                                            ."</div>"
                                            ."<table class='distanciaTop table table-sm table-dark'>"
                                                ."<thead>"
                                                    ."<tr>"
                                                        ."<th scope='col' style='text-align: left;'>Produto</th>"
                                                        ."<th></th>"
                                                        ."<th></th>"
                                                        ."<th></th>"
                                                        ."<th></th>"
                                                        ."<th></th>"
                                                        ."<th></th>"
                                                        ."<th></th>"
                                                        ."<th></th>"
                                                        ."<th></th>"
                                                        ."<th></th>"
                                                        ."<th></th>"
                                                        ."<th scope='col' style='text-align: left;'>Qtde</th>"
                                                        ."<th></th>"
                                                        ."<th scope='col' style='text-align: right;'>Unit</th>"
                                                        ."<th></th>"
                                                        ."<th></th>"
                                                        ."<th scope='col' style='text-align: center;'>Valor Total</th>"
                                                        ."<th></th>"
                                                    ."</tr>"
                                                ."</thead>"
                                            ."</table>"
                    ."<div class='divProdutosAberto'>"
                    ."<div id='formulario$retorno_idComanda'>";

                    $tabelaRetornoCMD1 = "
                    SELECT T0.idProduto AS 'idProduto', T1.nomeProduto AS 'nomeProduto', T0.quantidade AS 'quantidade', T0.valorUnitario AS 'valorUnitario', T0.totalLinha AS 'totalLinha'
                    FROM CMD1 T0 

                    LEFT JOIN OITM T1
                    ON T0.idProduto = T1.idProduto

                    WHERE idComanda = '$retorno_idComanda' 

                    ORDER BY numLinha ASC;";

                    $resultadoRetornoCMD1 = mysqli_query($conexao, $tabelaRetornoCMD1);
                    $varContador = 0;
                    while($linhaRetornoCMD1 = mysqli_fetch_assoc($resultadoRetornoCMD1)){
                        $varContador += 1;
                        $ficha = $linhaRetornoCMD1['idProduto'];
                        //ANÁLISE SE O PRODUTO DA OCMD É UMA FICHA TÉCNICA OU ITEM
                        if (substr($linhaRetornoCMD1['idProduto'], 0, 1) == 9) {
                            $tabelaOITT = "SELECT * FROM OITT WHERE idFicha = '$ficha';";
                            $resultadoOITT = mysqli_query($conexao, $tabelaOITT);
                            $linhaRetOITT = mysqli_fetch_assoc($resultadoOITT);
                            $retorno_idProduto = $linhaRetOITT['idFicha'].'-'.$linhaRetOITT['nomeFicha'];
                        }else{
                          $retorno_idProduto = $linhaRetornoCMD1['idProduto'].'-'.$linhaRetornoCMD1['nomeProduto'];  
                        } 
                        $retorno_quantidade = $linhaRetornoCMD1['quantidade'];
                        $retorno_valorUnitario = number_format($linhaRetornoCMD1['valorUnitario'], 2, ',', '.');
                        $retorno_totalLinha = number_format($linhaRetornoCMD1['totalLinha'], 2, ',', '.');

                        echo "<div id='linha$varContador' class='form-row' >"
                                    ."<div class='col-md-5 mb-1'>"
                                        ."<input type='text' class='form-control' id='$retorno_idComanda-produto$varContador' name='$retorno_idComanda-produto$varContador' list='listaItens' value='$retorno_idProduto'>"
                                    ."</div>"
                                    ."<div class='distanciaQuantidade2 col-md-2 mb-1'>"
                                        ."<input type='number' class='form-control' id='$retorno_idComanda-quantidade$varContador' name='$retorno_idComanda-quantidade$varContador' onblur='somaCMD1($retorno_idComanda, $varContador), calcularTotalFinalCMD1($retorno_idComanda)' min='1' max='9999' value='$retorno_quantidade'>"
                                    ."</div>"
                                    ."<div class='distanciaUnitario2 col-md-2 mb-1'>"
                                        ."<input type='text' class='form-control' id='$retorno_idComanda-valorUnitario$varContador' name='$retorno_idComanda-valorUnitario$varContador' onKeyPress='return(MascaraMoeda(this, 0, 0, event))' onblur='somaCMD1($retorno_idComanda, $varContador), calcularTotalFinalCMD1($retorno_idComanda)' value='$retorno_valorUnitario'>"
                                    ."</div>"
                                    ."<div class='IndexDistanciaTotal2 col-md-2 mb-1'>"
                                        ."<input type='text' style='text-align: center; font-size: 13px;' class='form-control' id='$retorno_idComanda-valorTotal$varContador' name='$retorno_idComanda-valorTotal$varContador' value='$retorno_totalLinha' readonly>"
                                    ."</div>"
                                    ."<div>"
                                    ."<button type='button' class='btn btn-danger IndexBotaoExcluirLinha' id='botaoExcluir1' name='botaoExcluir1' onclick='excluirLinha($retorno_idComanda, $varContador)'>X</button>"
                                    ."</div>"
                        ."</div>";
                    }
                    echo "</div>"
                        ."<!-- INICIO LINHA DO VALOR TOTAL AUTOCOMPLETE -->"
                            ."<input type='hidden' id='$retorno_idComanda-quantidadeLinhas' name='$retorno_idComanda-quantidadeLinhas' value='$varContador'>"
                            ."<div class='form-row'>"
                                ."<div class='IndexDistancia3 col-md-2 mb-1'>"
                                    ."<input type='text' style='text-align: center; font-size: 13px; background-color: #8B0000; color: white;' class='form-control' id='$retorno_idComanda-totalNota' name='totalNota$varContador' value='$retorno_valorTotal' readonly>"
                                ."</div>"
                            ."</div>"
                            ."<!-- FIM LINHA DO VALOR TOTAL AUTOCOMPLETE -->"
                            ."</div>"
                            ."<!--INICIO SEPARADOR-->"
                            ."<div class='distanciaTop1'>"
                                ."<ul class='nav nav-tabs justify-content-center'>"
                                ."</ul>"
                            ."</div>"
                            ."<!--FIM SEPARADOR-->"
                            ."<button type='button' class='btn btn-outline-danger IndexDistanciaSMargem' onclick='criarOculto($retorno_idComanda, $varContador)'><font><b>+</b></font></button>"
                            ."<div id='button' class='buttonAdicionar'>"
                                ."<button type='submit' class='btn btn-outline-primary' onclick='return validarCMD1($retorno_idComanda)'>Alterar</button>"
                                ."<button type='button' class='btn btn-outline-success distanciaBotaoFinalizar' onclick='abrirFechamento($retorno_idComanda)'>Finalizar</button>"
                            ."</div>"
                        ."</form>"    
                ."</div>"
                ."<div class='close-btn' onclick='abrirComanda($retorno_idComanda)'>&times;</div>"
            ."</div>"
        ."</div>";
            }
        ?>
        <!--FIM DIV COM A TABELA CMD1 OCULTA (COMANDAS JÁ CADASTRADAS)-->

        <!--RETORNAR QUANTIDADE DE LINHAS NA CONSULTA AFIM DE GERAR O AVISO NO MOMENTO DE FINALIZAR O DIA-->
        <input type="hidden" name="analiseRetornoOCMD" id="analiseRetornoOCMD" value="<?php echo $analiseRetornoOCMD ?>">
        <!--FIM RETORNAR QUANTIDADE DE LINHAS NA CONSULTA AFIM DE GERAR O AVISO NO MOMENTO DE FINALIZAR O DIA-->

        <!-- INICIO ADICIONAR COMANDA -->
        <div class="popup" id="popup-1">
            <div class="overlay"></div>
            <div class="content" id="content">
                <div class="IndexMargem">
                    <img src="../img/covilLogin.png" width="180px;" height="100px;" style="display: block; margin-left: auto; margin-right: auto;">
                    <h2 style="text-align: center; margin-left: 2%;"><font color="yellow">Nova Comanda</font></h2><br><br>
                        <form class="needs-validation" method="POST" action="cadastrarComanda.php" novalidate target="_blank">
                            <div class="form-row" style="text-align: left;">
                                <div class="col-md-2 mb-3">
                                    <span><font size="2" color="yellow"><b>Comanda </b></font></span>
                                    <input type="text" class="form-control" id="comanda" name="comanda">        
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-5 mb-3" style="text-align: left;">
                                    <span><font size="2" color="yellow">Cliente </font></span>
                                    <input type="text" class="form-control" id="nomeCliente" name="nomeCliente">
                                </div>

                                <div class="IndexDistancia1 col-md-2 mb-3" >
                                    <span><font size="2" color="yellow">Hora Entrada </font></span>
                                    <input type="time" class="form-control" id="horaEntrada" name="horaEntrada">
                                </div>
                            </div>

                            <table class="distanciaTop table table-sm table-dark">
                                <thead>
                                    <tr>
                                        <th scope="col" style="text-align: left;">Produto</th>
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
                                        <th></th>
                                        <th scope="col" style="text-align: right;">Qtde</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th scope="col" style="text-align: center;">Unit</th>
                                        <th scope="col" style="text-align: right;">Valor Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                            </table>
                            <!----------------------INICIO DATALIST----------------------->
                            <!--PRODUTO-->
                            <datalist id="listaItens">
                                <?php while ($linhaOITM = mysqli_fetch_assoc($resultadoOITM)) { ?>
                                    <option value="<?php echo $linhaOITM['idProduto'] . '-' . $linhaOITM['nomeProduto']; ?>"></option>
                                <?php } ?>
                            </datalist>
                            <!----------------------FIM DATALIST----------------------->

                            <div class="divProdutosAberto">
                            <div id="formularioCriar" >
                                <div id="linha1" class="form-row" >
                                    <input type="hidden" id="quantidadeLinhas" name="quantidadeLinhas">

                                    <div class="col-md-5 mb-1">
                                        <input type="text" class="form-control" id="produto1" name="produto1" list="listaItens">
                                    </div>

                                    <div class="IndexDistanciaQuantidade col-md-2 mb-1">
                                        <input type="number" class="form-control" id="quantidade1" name="quantidade1" onblur="soma('quantidade1', 'valorUnitario1', 'valorTotal1'), calcularTotalFinal()" min="1" max="9999">
                                    </div>

                                    <div class="IndexDistanciaUnitario col-md-2 mb-1">
                                        <input type="text" class="form-control" id="valorUnitario1" name="valorUnitario1" onKeyPress="return(MascaraMoeda(this, '.', ',', event))" onblur="soma('quantidade1', 'valorUnitario1', 'valorTotal1'), calcularTotalFinal()" value="R$">
                                    </div>

                                    <div class="distanciaTotal col-md-2 mb-1">
                                        <input type="text" style="text-align: center; font-size: 13px;" class="form-control" id="valorTotal1" name="valorTotal1" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- INICIO LINHA DO VALOR TOTAL AUTOCOMPLETE -->
                            <div class="form-row">
                               <input type="hidden">

                                <div class="col-md-5 mb-1">
                                    <input type="hidden" class="form-control">
                                </div>

                                <div class="IndexDistanciaUnitario col-md-2 mb-1">
                                    <input type="hidden" class="form-control">
                                </div>

                                <div class="IndexDistanciaQuantidade col-md-2 mb-1">
                                    <input type="hidden" class="form-control">
                                </div>

                                <div class="distanciaTotal col-md-2 mb-1">
                                    <input type="text" style="text-align: center; font-size: 13px; background-color: #8B0000; color: white;" class="form-control" id="totalNota" name="totalNota" readonly>
                                </div>
                            </div>
                            </div>
                            <!-- FIM LINHA DO VALOR TOTAL AUTOCOMPLETE -->

                            <!--INICIO SEPARADOR-->
                            <div class="distanciaTop1">
                                <ul class="nav nav-tabs justify-content-center">
                                </ul>
                            </div>
                            <!--FIM SEPARADOR-->

                            <button type="button" class="btn btn-outline-danger IndexDistanciaSMargem" onclick="criar()"><font><b>+</b></font></button>
                            <div id="button" class="buttonAdicionar">
                                <button class="btn btn-outline-primary" onclick="return validar()">Adicionar</button>
                            </div>
                        </form>
                        
                </div>
                <div class="close-btn" onclick="novaComanda()">&times;</div>
            </div>
        </div>
        <!-- FIM ADICIONAR COMANDA -->

        <!-- INICIO FINALIZAR COMANDA -->
        <div class="popup2" id="popup-pagamento">
            <div class="overlay2"></div>
            <div class="content2" id="content2">
                <div class="IndexMargem2">
                    <h2 style="text-align: center;"><font color="yellow">Pagamento</font></h2><br><br>
                        <form class="needs-validation" method="POST" action="finalizarComanda.php" novalidate target="_blank">
                            <div class="form-row" style="text-align: center;">
                                <input type="hidden" id="retornoIdComanda" name="retornoIdComanda">
                                <div class="col-md-12 mb-3">
                                    <span><font size="2" color="yellow"><b>Valor Comanda </b></font></span>
                                    <input type="text" class="form-control" id="valorComanda" name="valorComanda" style="text-align: center;" readonly>        
                                </div>
                            </div>

                            <div class="form-row" style="text-align: center;">
                                <div class="col-md-12 mb-3">
                                    <span><font size="2" color="yellow"><b>Desconto </b></font></span>
                                    <input type="text" class="form-control" id="desconto" name="desconto" onKeyPress="return(MascaraMoeda(this, '.', ',', event))" onblur="retornarTroco()" style="text-align: center;">        
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-12 mb-3" style="text-align: center;">
                                    <span><font size="2" color="yellow"><b>Valor Fornecido</b></font></span>
                                    <input type="text" class="form-control" id="valorFornecido" name="valorFornecido" onKeyPress="return(MascaraMoeda(this, '.', ',', event))" onblur="retornarTroco()" style="text-align: center;">
                                </div>
                            </div> 

                            <div class="form-row">
                                <div class="col-md-12 mb-3" style="text-align: center;">
                                    <span><font size="2" color="yellow"><b>Troco</b></font></span>
                                    <input type="text" class="form-control" id="trocoComanda" name="trocoComanda" style="text-align: center; background-color: yellow; font-weight:bolder;" readonly>
                                </div>
                            </div>

                            <label class="switch">
                                <input type="checkbox" id="cortesia" name="cortesia" onclick="analiseCortesia()">
                                <span class="slider round"></span>
                            </label>
                            <span style="color: yellow; font-size: 15px;">Comanda Cortesia</span><br><br>

                            <div class="col-md-12 mb-3" style="text-align: center;">
                                <button type="submit" class="btn btn-outline-success" onclick="return validarFinalizarComanda()" style="text-align: center;">Finalizar</button>
                            </div> 
                        </form>  
                    <div type="button" class="close-btn2" id="fecharFechamento" onclick="abrirFechamento(0)">&times;</div>                     
                </div>
            </div>
        </div>
        <!-- FIM FINALIZAR COMANDA -->

        <!-- INÍCIO FORMULÁRIO PARA FINALIZAR O DIA -->
        <div class="popup2" id="popup-finalizarDia">
            <div class="overlay2"></div>
            <div class="content2" id="content2">
                <div class="IndexMargem2">
                    <img src="../img/covilFaturamento.png" width="180px;" height="100px;" style="display: block; margin-left: auto; margin-right: auto;"><br><br><br>
                        <form class="needs-validation" method="POST" action="finalizarDia.php" novalidate target="_blank">
                            <div class="form-row">
                                <div class="col-md-12 mb-3" style="margin-left: 2%;">
                                    <span><font size="2" color="yellow"><b>Dia de Venda a ser finalizado </b></font></span>
                                    <input type="date" class="form-control" id="finalizarDia" name="finalizarDia" style="text-align: center;"> 
                                    <span><font color="yellow">*</font><font size="1" color="yellow"> Considerar o dia de venda que iniciou </font><font color="yellow">*</font></span>      
                                </div>
                            </div><br>

                            <div class="col-md-12 mb-3" style="text-align: center; margin-left: 2%;">
                                <button type="submit" class="btn btn-outline-warning" onclick="return validarFinalizarDia()" style="text-align: center;">Finalizar</button>
                            </div> 
                        </form>  
                    <div type="button" class="close-btn2" onclick="finalizarDia()">&times;</div>                     
                </div>
            </div>
        </div>
        <!-- FIM FORMULÁRIO PARA FINALIZAR O DIA -->

        <!-- INÍCIO FORMULÁRIO CONTENDO O FATURAMENTO DO DIA -->
        <div class="popup3" id="popup-faturamentoDia">
            <div class="overlay3"></div>
            <div class="content3" id="content3">
                <div class="IndexMargem">
                    <img src="../img/covilLogin.png" width="180px;" height="100px;" style="display: block; margin-left: auto; margin-right: auto;">
                    <h2 style="text-align: center; margin-left: 2%;"><font color="yellow">Faturamento Diário</font></h2><br><br>
                        <form class="needs-validation" method="POST" action="#">
                            <div class="form-row" style="text-align: left;">
                                <div class="col-md-2 mb-3">
                                    <span><font size="2" color="yellow"><b>Num Comandas</b></font></span>
                                    <input type="text" class="form-control" id="comandasAbertas" name="comandasAbertas" value="<?php echo $numComandaAberta ?>" readonly>        
                                </div>
                                <div class="distancia2 col-md-3 mb-3" >
                                    <span class="IndexDistancia1"><font size="2" color="yellow" ><b>Data</b></font></span>
                                    <input type="date" class="form-control" id="dataFaturamento" name="dataFaturamento" value="<?php echo $dataAtual; ?>" readonly>
                                </div>
                            </div>

                            <table class="distanciaTop table table-sm table-dark">
                                <thead>
                                    <tr>
                                        <th scope="col" style="text-align: left;">Produto</th>
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
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th scope="col" style="text-align: right;">Qtde</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th scope="col" style="text-align: right;">Valor Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                            </table>
                            <div class="retornoComandasAbertas">
                            <?php
                            $v_linhaFat = 0;
                            while ($linhaFaturamento = mysqli_fetch_assoc($resultadoFaturamentoAberto)) {

                                $v_linhaFat = $v_linhaFat + 1;
                                $v_fatIdProduto = $linhaFaturamento['idProduto'];
                                if (substr($v_fatIdProduto, 0, 1) == 9) {
                                    $tabelaFatOITT = "SELECT * FROM OITT WHERE idFicha = '$v_fatIdProduto';";
                                    $resultadoFatOITT = mysqli_query($conexao, $tabelaFatOITT);
                                    $linhaFatOITT = mysqli_fetch_assoc($resultadoFatOITT);
                                    $nomeCompletoItem = $linhaFatOITT['idFicha'].'-'.$linhaFatOITT['nomeFicha'];
                                }else{
                                    $nomeCompletoItem = $linhaFaturamento['idProduto'].'-'.$linhaFaturamento['nomeProduto'];
                                }
                                $v_fatNomeProduto = $linhaFaturamento['nomeProduto'];
                                $v_fatQuantidade = $linhaFaturamento['quantidade'];
                                $v_fatVendaTotal = number_format($linhaFaturamento['vendaTotal'], 2, ',', '.');


                                echo "<div id='formularioFaturamentoDia'>"
                                        ."<div id='fat-linha$v_linhaFat' class='form-row' >"
                                            ."<div class='col-md-5 mb-1'>"
                                            ."<input type='text' class='form-control' id='fat-produto$v_linhaFat' name='fat-produto$v_linhaFat' value='$nomeCompletoItem' readonly>"
                                        ."</div>"
                                        ."<div class='IndexDistanciaQuantidade col-md-2 mb-1'>"
                                           ."<input type='number' class='form-control distancia2' id='fat-quantidade$v_linhaFat' name='fat-quantidade$v_linhaFat' value='$v_fatQuantidade' style='text-align:center;' readonly>"
                                        ."</div>"
                                        ."<div class='IndexDistanciaUnitario col-md-2 mb-1'>"
                                            ."<input type='hidden' class='form-control'>"
                                        ."</div>"
                                        ."<div class='distanciaTotal col-md-2 mb-1'>"
                                            ."<input type='text' style='text-align: center; font-size: 13px;' class='form-control' id='fat-valorTotal$v_linhaFat' name='fat-valorTotal$v_linhaFat' value='$v_fatVendaTotal' readonly>"
                                        ."</div>"
                                    ."</div>"
                                ."</div>";
                            }

                            echo "<!-- INICIO LINHA DO VALOR TOTAL AUTOCOMPLETE -->"
                                ."<div class='form-row'>"
                                   ."<input type='hidden'>"
                                    ."<div class='col-md-5 mb-1'>"
                                        ."<input type='hidden' class='form-control'>"
                                    ."</div>"
                                    ."<div class='IndexDistanciaUnitario col-md-2 mb-1'>"
                                        ."<input type='hidden' class='form-control'>"
                                    ."</div>"
                                    ."<div class='IndexDistanciaQuantidade col-md-2 mb-1'>"
                                        ."<input type='hidden' class='form-control'>"
                                    ."</div>"
                                    ."<div class='distanciaTotal col-md-2 mb-1'>"
                                        ."<input type='text' style='text-align: center; font-size: 13px; background-color: #8B0000; color: white;' class='form-control' id='fat-totalNota$v_linhaFat' name='fat-totalNota$v_linhaFat' value='$valorComandaAberta' readonly>"
                                    ."</div>"
                                ."</div>"
                                ."<!-- FIM LINHA DO VALOR TOTAL AUTOCOMPLETE -->";
                            
                            ?>
                            </div>
                            <!--INICIO SEPARADOR-->
                            <div class="distanciaTop1">
                                <ul class="nav nav-tabs justify-content-center">
                                </ul>
                            </div>
                            <!--FIM SEPARADOR-->
                        </form>
                        
                </div>
                <div class="close-btn3" onclick="faturamentoDia()">&times;</div>
            </div>
        </div>
        <!-- FIM FORMULÁRIO CONTENDO O FATURAMENTO DO DIA -->
        </div>
        <!--FIM TELA DE MANUSEIO DE COMANDAS-->

        <!--INÍCIO TELA DE PESQUISA COMANDA-->
            <div class="tab-pane fade alinhamentoTopParceiro" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                <div class="IndexMargem2">
                    <img src="../img/covilLogin.png" width="200px;" height="140px;" style="display: block; margin-left: auto; margin-right: auto;">
                    <form class="needs-validation" method="POST" action="visualizarComanda.php" target="_blank">
                        <div class="form-group" style="margin-left: 2%;">
                            <label for="inputAddress"><font color="yellow">Pesquisar Comanda <font color="red">*</font></b></font></label>
                            <input type="text" class="form-control" id="idVisualizacaoComanda" name="idVisualizacaoComanda" placeholder="Busque a comanda por Data, Número, Nome ou Valor" list="listaComanda" required>
                            <datalist id="listaComanda">
                                <?php while ($linhaVisualizarComanda = mysqli_fetch_assoc($resultadoVisualizarComanda)) { 
                                    if ($linhaVisualizarComanda['valorPago'] == 0 && $linhaVisualizarComanda['cortesia'] == 'N'|| $linhaVisualizarComanda['valorPago'] == null && $linhaVisualizarComanda['cortesia'] == 'N') {
                                        $status = 'Pendente';
                                    }else{
                                        $status = 'Pago';
                                    }
                                    ?>
                                    <option aria-disabled="true" value="<?php echo 'Id: '.$linhaVisualizarComanda['idComanda'].' - Comanda: '.$linhaVisualizarComanda['comanda'].' - '.$linhaVisualizarComanda['nomeCliente'].' - '.$linhaVisualizarComanda['horaEntrada'].' - R$ '.number_format($linhaVisualizarComanda['valorTotal'], 2, ',', '.').'-'.$status; ?>" ></option>
                                <?php } ?>
                            </datalist>
                        </div><br>
                        <button type="submit" class="btn btn-outline-primary" style="margin-left: 46%;" onclick="return validarVisualizacao()">Analisar</button>
                    </form>
                </div><br><br>
            </div>
        <!--FIM TELA DE PESQUISA COMANDA-->

        </div>
        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
    </body>
    
