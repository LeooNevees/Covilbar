<?php
include('../v_login.php');

$idComanda = trim(filter_input(INPUT_POST, 'idVisualizacaoComanda', FILTER_SANITIZE_STRING));


if (empty($idComanda)) {
	echo "Id Comanda inválido. Por favor refaça o procedimento anterior.";
	return 0;
}else{
	$vIdComanda = substr($idComanda, 4, -1);
	$validado_idComanda = (int)$vIdComanda;
	$v_idComanda = trim($validado_idComanda);
}

//CONEXAO BANCO DE DADOS
include '../conexao.php';

date_default_timezone_set('America/Sao_Paulo');
$data = date('d/m');

//TABELA DA PESQUISA DE COMANDAS
$tabelaOCMD = "
SELECT idComanda, comanda, nomeCliente, DATE_FORMAT(`horaEntrada`, '%H:%i') AS 'horaEntrada', valorTotal, valorPago, troco, cortesia, statusPagamento, cancelado
FROM OCMD
WHERE idComanda = '$v_idComanda'
AND cancelado = 'N';";

$resultadoOCMD = mysqli_query($conexao, $tabelaOCMD);

if (mysqli_affected_rows($conexao) <= 0) {
    echo "Comanda não encontrada. Por favor refaça o procedimento";
    return 0;
}   

$linhaRetornoOCMD = mysqli_fetch_assoc($resultadoOCMD);

//TABELA DA PESQUISA DE COMANDAS
$tabelaCMD1 = "
SELECT T0.idComanda AS 'idComanda', T0.idProduto AS 'idProduto', T1.nomeProduto AS 'nomeProduto', T0.quantidade AS 'quantidade', T0.valorUnitario AS 'valorUnitario', T0.totalLinha AS 'totalLinha', T0.cancelado AS 'cancelado'
FROM CMD1 T0 

LEFT JOIN OITM T1
ON T0.idProduto = T1.idProduto

WHERE idComanda = '$v_idComanda';";
$resultadoCMD1 = mysqli_query($conexao, $tabelaCMD1);
$totalLinhas = mysqli_affected_rows($conexao);
if (mysqli_affected_rows($conexao) <= 0) {
    echo "Erro ao encontrar a comanda na tabel CMD1";
    return 0;
}
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

            <style type="text/css">

            </style>
            <script type="text/javascript">
                //FUNÇÃO PARA FINALIZAR O PAGAMENTO DA COMANDA
            function abrirFechamento() {                
                document.getElementById('popup-pagamento').classList.toggle("active2");
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
                            window.close();
                        }, 600);
                    }
                }
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
                                <a class="nav-link" href="index.php"> Início <span class="sr-only">(current)</span></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="Parceiros/index.php">Parceiros</a>
                            </li>
                            <li class="nav-item dropdown">
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
                                <button type="button" class="btn btn-outline-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><font size="2"><?php echo strtolower($_SESSION['usuario']) ?></font></button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="../logout.php">Logout</a>
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

            <!--INICIO VISUALIZAR COMANDA-->
                <div class="VisualizarComandaMargem">
                    <img src="../img/covilLogin.png" width="180px;" height="100px;" style="display: block; margin-left: auto; margin-right: auto;">
                    <h2 style="text-align: center; margin-left: 2%;"><font color="yellow">Comanda <?php echo $linhaRetornoOCMD['nomeCliente']; ?></font></h2><br><br>
                        <form class="needs-validation" method="POST" action="finalizarComanda.php" novalidate target="_blank">
                            <div class="form-row" style="text-align: left;">
                                <div class="col-md-1 mb-3">
                                    <span><font size="2" color="yellow"><b>Comanda </b></font></span>
                                    <input type="text" class="form-control" id="comanda" name="comanda" value="<?php echo $linhaRetornoOCMD['comanda']; ?>" readonly>        
                                </div>
                                <div class="distancia5 col-md-2 mb-3" style="text-align: center;">
                                    <span><font size="2" color="yellow">Status </font></span>
                                    <?php
                                    if ($linhaRetornoOCMD['valorPago'] == 0 && $linhaRetornoOCMD['cortesia'] == 'N' || $linhaRetornoOCMD['valorPago'] == null && $linhaRetornoOCMD['cortesia'] == 'N') {
                                        $statusComanda = 'Aberto';
                                       echo "<input type='text' class='form-control' id='status' name='status' value='$statusComanda' style='text-align: center; background-color:red; color:white;' readonly>";
                                    }else{
                                        if ($linhaRetornoOCMD['cortesia'] == 'Y') {
                                            $statusComanda = 'Cortesia';
                                        }else{
                                           $statusComanda = 'Fechado';
                                        }
                                        
                                        echo "<input type='text' class='form-control' id='status' name='status' value='$statusComanda' style='text-align: center; background-color:#006400; color:white;' readonly>"; 
                                   }

                                    ?>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-3 mb-3" style="text-align: left;">
                                    <span><font size="2" color="yellow">Cliente </font></span>
                                    <input type="text" class="form-control" id="nomeCliente" name="nomeCliente" value="<?php echo $linhaRetornoOCMD['nomeCliente']; ?>" readonly>
                                </div>

                                <div class="VisualizarComandaDistancia1 col-md-2 mb-3" style="text-align: center;">
                                    <span><font size="2" color="yellow">Hora Entrada </font></span>
                                    <input type="time" class="form-control" id="horaEntrada" name="horaEntrada" value="<?php echo $linhaRetornoOCMD['horaEntrada']; ?>" style="text-align: right;" readonly>
                                </div>
                            </div>

                            <table class="VisualizarComandaDistanciaTop table table-sm table-dark">
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
					<?php
					$varContador = 0;
					$retorno_idComanda = $linhaRetornoOCMD['idComanda'];
                    while($linhaRetornoCMD1 = mysqli_fetch_assoc($resultadoCMD1)){
                        $varContador += 1;
                        $itemOITT = $linhaRetornoCMD1['idProduto'];
                        $analise = substr($linhaRetornoCMD1['idProduto'], 0, 1);
                        if ($analise == 9) {
                            $tabelaOITT = "SELECT * FROM OITT WHERE idFicha = '$itemOITT';";
                            $resultadoOITT = mysqli_query($conexao, $tabelaOITT);
                            $linhaOITT = mysqli_fetch_assoc($resultadoOITT);
                            $retorno_idProduto = $linhaOITT['idFicha'].'-'.$linhaOITT['nomeFicha'];
                        }else{
                            $retorno_idProduto = $linhaRetornoCMD1['idProduto'].'-'.$linhaRetornoCMD1['nomeProduto'];   
                        }
                        $retorno_quantidade = $linhaRetornoCMD1['quantidade'];
                        $retorno_valorUnitario = number_format($linhaRetornoCMD1['valorUnitario'], 2, ',', '.');
                        $retorno_totalLinha = number_format($linhaRetornoCMD1['totalLinha'], 2, ',', '.');

                        echo "<div id='linha$varContador' class='form-row' >"
                                    ."<div class='col-md-5 mb-1'>"
                                        ."<input type='text' class='form-control' id='$retorno_idComanda-produto$varContador' name='$retorno_idComanda-produto$varContador' value='$retorno_idProduto'>"
                                    ."</div>"
                                    ."<div class='VisualizarComandaDistanciaQuantidade col-md-2 mb-1'>"
                                        ."<input type='number' class='form-control' id='$retorno_idComanda-quantidade$varContador' name='$retorno_idComanda-quantidade$varContador 'value='$retorno_quantidade' style='text-align:center;'>"
                                    ."</div>"
                                    ."<div class='VisualizarComandaDistanciaUnitario col-md-2 mb-1'>"
                                        ."<input type='text' class='form-control' id='$retorno_idComanda-valorUnitario$varContador' name='$retorno_idComanda-valorUnitario$varContador' value='$retorno_valorUnitario' style='text-align:center;'>"
                                    ."</div>"
                                    ."<div class='distanciaTotal col-md-2 mb-1'>"
                                        ."<input type='text' style='text-align: center; font-size: 13px;' class='form-control' id='$retorno_idComanda-valorTotal$varContador' name='$retorno_idComanda-valorTotal$varContador' value='$retorno_totalLinha' readonly>"
                                    ."</div>"
                                    ."<div>"
                        ."</div>"
                        ."</div>";
                    }
                   	?>
                            <!-- INICIO LINHA DO VALOR TOTAL AUTOCOMPLETE -->
                            <div class="form-row">
                                <div class="distanciaTotal2 col-md-2 mb-1">
                                    <input type="text" style="text-align: center; font-size: 13px; background-color: #8B0000; color: white;" class="form-control" id="totalNota" name="totalNota" value="<?php echo number_format($linhaRetornoOCMD['valorTotal'], 2, ',', '.'); ?>" readonly>
                                </div>
                            </div>
                            <!-- FIM LINHA DO VALOR TOTAL AUTOCOMPLETE -->

                            <!--INICIO SEPARADOR-->
                            <div class="distanciaTop1">
                                <ul class="nav nav-tabs justify-content-center">
                                </ul>
                            </div>
                            <!--FIM SEPARADOR-->
                            <?php if($linhaRetornoOCMD['valorPago'] == 0 || $linhaRetornoOCMD['valorPago'] == null){ ?>
                            <button type="button" class="btn btn-outline-success" style="margin-left: 46%; margin-top: 2%;" onclick="abrirFechamento()">Finalizar</button>
                            <?php } ?>
                        </form>
                        
            </div>
            <!--FIM VISUALIZAR COMANDA-->

            <!-- INICIO FINALIZAR COMANDA -->
        <div class="popup2" id="popup-pagamento">
            <div class="overlay2"></div>
            <div class="content2" id="content2">
                <div class="VisualizarComandaMargem2">
                    <h2 style="text-align: center;"><font color="yellow">Pagamento</font></h2><br><br>
                        <form class="needs-validation" method="POST" action="finalizarComanda.php" novalidate target="_blank">
                            <div class="form-row" style="text-align: center;">
                                <input type="hidden" id="retornoIdComanda" name="retornoIdComanda" value="<?php echo $retorno_idComanda; ?>">
                                <div class="col-md-12 mb-3">
                                    <span><font size="2" color="yellow"><b>Valor Comanda </b></font></span>
                                    <input type="text" class="form-control" id="valorComanda" name="valorComanda" style="text-align: center;" value="<?php echo number_format($linhaRetornoOCMD['valorTotal'], 2, ',', '.'); ?>" readonly>        
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

            <!-- Optional JavaScript -->
            <!-- jQuery first, then Popper.js, then Bootstrap JS -->
            <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
            <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
        </body>
</html>


