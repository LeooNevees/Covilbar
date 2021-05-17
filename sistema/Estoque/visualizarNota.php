<?php
include('../../v_login.php');
//SE NOTA = 1 (NOTA DE ENTRADA) || NOTA = 2 (NOTA DE SAIDA)
$tipoNota = trim(filter_input(INPUT_GET, 'tipoNota', FILTER_SANITIZE_STRING));

//CONEXAO BANCO DE DADOS
include '../../conexao.php';

?>

<?php if ($tipoNota == 1) {  //INICIO DE VISUALIZAR NOTA DE ENTRADA
    //REALIZADO CAPTURA DESTA FORMA DEVIDO A PODER LINKAR JUNTO AO CONTAS A PAGAR OU RECEBER
    $idNota = trim(filter_input(INPUT_POST, 'idNota', FILTER_SANITIZE_STRING));
    if (!empty($idNota)) {
        $v_idNota = (int)$idNota;
    }else{
        $idNota = filter_input(INPUT_GET, 'idEntrada', FILTER_SANITIZE_NUMBER_INT);
        if (empty($idNota)) {
            echo "Entrada de Mercadoria não encontrada. Por favor refaça o procedimento";
        }else{
            $v_idNota = trim($idNota);
        }
    }

    date_default_timezone_set('America/Sao_Paulo');
    $data = date('d/m');

    $tabelaOPCH = "
    SELECT T0.idEntrada AS 'idEntrada', T0.idParceiroNegocio AS 'idParceiroNegocio', T1.nomeParceiro AS 'nomeParceiro', T0.dataLancamento AS 'dataLancamento', T0.dataVencimento as 'dataVencimento', T0.parcelas as 'parcelas', T0.valorTotal as 'valorTotal', T2.nomeDeposito AS 'nomeDeposito'
    FROM OPCH T0

    INNER JOIN OCRD T1
    ON T0.idParceiroNegocio = T1.idParceiro

    INNER JOIN OWHS T2
    ON T0.idDeposito = T2.idDeposito

    WHERE idEntrada = '$v_idNota';";
    $resultadoOPCH = mysqli_query($conexao, $tabelaOPCH);
    if (mysqli_affected_rows($conexao) <= 0) {
        echo "Entrada de Mercadoria não encontrada. Por favor refaça o procedimento";
        return 0;
    }

    $tabelaPCH1 = "SELECT * FROM PCH1 WHERE idEntrada = '$v_idNota';";
    $resultadoPCH1 = mysqli_query($conexao, $tabelaPCH1);
    $totalLinhas = mysqli_affected_rows($conexao);
    if ($totalLinhas <= 0) {
        echo "PCH1 não encontrada. Relate a situação ao administrador do sistema";
        return 0;
    }?>

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
                            window.location.href = "http://localhost/CovilBar/sistema/Estoque/entrada.php";
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

            <!--INICIO VISUALIZAR ENTRADA-->
            <div class="tab-content" id="v-pills-tabContent">
                <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                    <div class="margem">
                        <img src="../../img/covilLogin.png" width="200px;" height="140px;" style="display: block; margin-left: auto; margin-right: auto;">
                        <h1 style="text-align: center; margin-left: 1%;"><font color="yellow">Entrada de Mercadoria</font></h1><br><br>
                        <form class="needs-validation" method="POST" action="cadastrarEntrada.php" novalidate target="_blank">
                            <?php while($linhaOPCH = mysqli_fetch_assoc($resultadoOPCH)){ ?>
                            <div class="form-row">
                                <div class="col-md-1 mb-3">
                                    <span><font size="2" color="yellow"><b>Número Nota</b></font></span>
                                    <input type="text" class="form-control" id="numeroNota" name="numeroNota" value="<?php echo $linhaOPCH['idEntrada'] ?>" readonly>       
                                </div>

                                <div class="distancia1 col-md-2 mb-3">
                                    <span><font size="2" color="yellow">Data de Lançamento</font></span>
                                    <input type="date" class="form-control" id="dataLancamento" name="dataLancamento" value="<?php echo $linhaOPCH['dataLancamento'] ?>" readonly>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-3 mb-3">
                                    <span><font size="2" color="yellow">Código Fornecedor</font></span>
                                    <input type="text" class="form-control" id="codigoFornecedor" name="codigoFornecedor" value="<?php echo $linhaOPCH['idParceiroNegocio'].' - '.$linhaOPCH['nomeParceiro'] ?>" readonly>
                                </div>

                                <div class="distancia2 col-md-2 mb-3">
                                    <span><font size="2" color="yellow">Data de Vencimento</font></span>
                                    <input type="date" class="form-control" id="dataVencimento" name="dataVencimento" value="<?php echo $linhaOPCH['dataVencimento'] ?>" readonly>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-1 mb-3">
                                    <span><font size="2" color="yellow">Depósito</font></span>
                                    <select type="text" class="form-control" id="deposito" name="deposito" readonly>
                                        <option><?php echo $linhaOPCH['nomeDeposito'] ?></option>
                                    </select>
                                </div>

                                <div class="distancia3 col-md-1 mb-3">
                                    <span><font size="2" color="yellow">Parcelas</font></span>
                                    <select type="text" class="form-control" id="parcelas" name="parcelas" readonly>
                                        <option><?php echo $linhaOPCH['parcelas']; ?>x</option>
                                    </select>
                                </div>
                            </div>
        
                            <!--ENCERRAMENTO DA OPCH-->

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

                            <div class="form-row" id="formulario">
                                <?php 
                                for ($i=1; $i <= $totalLinhas ; $i++) { 
                                    $v_tabelaPCH1 = "
                                    SELECT T0.idEntrada AS 'idEntrada', T0.numLinha AS 'numLinha', T0.idProduto AS 'idProduto', T2.nomeProduto AS 'nomeProduto', T0.quantidadeEntrada AS 'quantidadeEntrada', T1.nomeUnidade AS 'nomeUnidade', T0.valorUnitario AS 'valorUnitario', T0.totalLinha AS 'totalLinha'
                                    FROM PCH1 T0

                                    INNER JOIN grupounidademedida T1
                                    ON T0.idGrupoUnidadeMedida = T1.idGrupoUnidadeMedida

                                    INNER JOIN oitm T2
                                    ON T0.idProduto = T2.idProduto

                                    WHERE idEntrada = '$v_idNota' 
                                    AND numLinha = '$i';";

                                    $v_resultadoPCH1 = mysqli_query($conexao, $v_tabelaPCH1);
                                    while ($linhaPCH1 = mysqli_fetch_assoc($v_resultadoPCH1)) {
                                        $v_idProduto = $linhaPCH1['idProduto'];
                                        $v_nomeProduto = $linhaPCH1['nomeProduto'];
                                        $v_quantidade = $linhaPCH1['quantidadeEntrada'];
                                        $v_nomeUnidade = $linhaPCH1['nomeUnidade'];
                                        $v_valorUnitario = number_format($linhaPCH1['valorUnitario'], 2, ',', '.');
                                        $v_totalLinha = number_format($linhaPCH1['totalLinha'], 2, ',', '.');
                                    echo "<div class='col-md-3 mb-1'>"
                                        ."<input type='text' class='form-control' id='produto1' name='produto1' value='$v_idProduto - $v_nomeProduto' readonly>"
                                        ."</div>"
                                        ."<div class='distanciaQuantidade col-md-1 mb-1'>"
                                        ."<input type='number' class='form-control' id='quantidade1' name='quantidade1' value='$v_quantidade' readonly>"
                                        ."</div>"
                                        ."<div class='distanciaUM col-md-1 mb-1'>"
                                        ."<input type='text' class='form-control' id='grupoUnidadeMedida1' name='grupoUnidadeMedida1' value='$v_nomeUnidade' readonly>"
                                        ."</div>"
                                        ."<div class='distanciaUnitario col-md-1 mb-1'>"
                                        ."<input type='text' class='form-control' id='valorUnitario1' name='valorUnitario1' value='R$ $v_valorUnitario' readonly>"
                                        ."</div>"
                                        ."<div class='distanciaTotal col-md-1 mb-1'>"
                                        ."<input type='text' style='text-align: center; font-size: 13px;' class='form-control' id='valorTotal1' name='valorTotal1' value='R$ $v_totalLinha' readonly>"
                                        ."</div>";
                                    }
                                }
                                ?>
                            </div>
                            <!-- INICIO LINHA DO VALOR TOTAL AUTOCOMPLETE -->
                             <div class="form-row" id="formulario">
                                <div class="col-md-3 mb-1">
                                    <input type="hidden" class="form-control">
                                </div>
                                <div class="distanciaQuantidade col-md-1 mb-1">
                                    <input type="hidden" class="form-control">
                                </div>


                                <div class="distanciaUM col-md-1 mb-1">
                                    <input type="hidden" class="form-control">
                                </div>


                                <div class="distanciaUnitario col-md-1 mb-1">
                                    <input type="hidden" class="form-control">
                                </div>
                                <?php
                                $conversao_valorTotal = number_format($linhaOPCH['valorTotal'], 2, ',', '.');
                                ?>
                                <div class="distanciaTotal col-md-1 mb-1">
                                    <input type="text" style="text-align: center; font-size: 13px; background-color: #8B0000; color: white;" class="form-control" id="totalNota" name="totalNota" value="<?php echo 'R$ '.$conversao_valorTotal ?>" readonly>
                                </div>
                            </div>
                            <?php } ?>
                            <!-- FIM LINHA DO VALOR TOTAL AUTOCOMPLETE -->

                            <!--INICIO SEPARADOR-->
                            <div class="distanciaTop1">
                                <ul class="nav nav-tabs justify-content-center">
                                </ul>
                            </div>
                            <!--FIM SEPARADOR-->
                        </form><br><br>
                    </div>
                </div>
                <!--FIM VISUALIZAR ENTRADA-->
            </div>

            <!-- Optional JavaScript -->
            <!-- jQuery first, then Popper.js, then Bootstrap JS -->
            <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
            <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
        </body>
</html>


<?php }else{//INÍCIO DE VISUALIZAR NOTA DE SAIDA

    //REALIZADO CAPTURA DESTA FORMA DEVIDO A PODER LINKAR JUNTO AO CONTAS A PAGAR OU RECEBER
    $idNota = trim(filter_input(INPUT_POST, 'idNota', FILTER_SANITIZE_STRING));
    if (!empty($idNota)) {
        $v_idNota = (int)$idNota;
    }else{
        $idNota = filter_input(INPUT_GET, 'idEntrada', FILTER_SANITIZE_NUMBER_INT);
        if (empty($idNota)) {
            echo "Saida de Mercadoria não encontrada. Por favor refaça o procedimento";
        }else{
            $v_idNota = trim($idNota);
        }
    }

    date_default_timezone_set('America/Sao_Paulo');
    $data = date('d/m');

    $tabelaOINV = "
    SELECT T0.idSaida AS 'idSaida', T0.idParceiroNegocio AS 'idParceiroNegocio', T1.nomeParceiro AS 'nomeParceiro', T0.dataLancamento AS 'dataLancamento', T0.dataVencimento as 'dataVencimento', T0.parcelas as 'parcelas', T0.valorTotal as 'valorTotal', T2.nomeDeposito AS 'nomeDeposito'
    FROM OINV T0

    INNER JOIN OCRD T1
    ON T0.idParceiroNegocio = T1.idParceiro

    INNER JOIN OWHS T2
    ON T0.idDeposito = T2.idDeposito

    WHERE idSaida = '$v_idNota';";
    $resultadoOINV = mysqli_query($conexao, $tabelaOINV);
    if (mysqli_affected_rows($conexao) <= 0) {
        echo "Saída de Mercadoria não encontrada. Por favor refaça o procedimento<br>";
        return 0;
    }

    $tabelaINV1 = "SELECT * FROM INV1 WHERE idSaida = '$v_idNota';";
    $resultadoINV1 = mysqli_query($conexao, $tabelaINV1);
    $totalLinhas = mysqli_affected_rows($conexao);
    if ($totalLinhas <= 0) {
        echo "INV1 não encontrada. Relate a situação ao administrador do sistema<br>";
        return 0;
    }?>

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
            <link rel="stylesheet" href="/css/style.css">

            <!-----ICONES------------>
            <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
            <script src="https://kit.fontawesome.com/70c48f08c7.js"></script>
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

            <script type="text/javascript">            

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
                            window.location.href = "http://localhost/CovilBar/sistema/Estoque/entrada.php";
                        }, 500);
                    }
                }
            </script>

            <style type="text/css">
                .margem{
                    margin-top: 1%;
                    margin-left: 10%;
                    margin-right: 10%;
                }
                .margem2{
                    margin-top: 3%;
                    margin-left: 25%;
                    margin-right: 25%;
                }
                .distancia1{
                    margin-left: 75%;
                }
                .distancia2{
                    margin-left: 58.3%;
                }
                .distancia3{
                    margin-left: 75%;
                }
                .distancia4{
                    margin-left: 91.6%;
                }
                .distanciaTop{
                    margin-top: 5%;
                }
                .distanciaSMargem{
                    margin-left: 0%; 
                }
                .distanciaNome{
                    margin-left: 0%;
                }
                .distanciaQuantidade{
                    margin-left: 7.4%;
                }
                .distanciaUM{
                    margin-left: 9.1%;
                }
                .distanciaUnitario{
                    margin-left: 9.1%;
                }
                .distanciaTotal{
                    margin-left: 7.3%;
                }
                .buttonAdicionar{
                    text-align: center;
                }
                .fundo1{
                    background-size: 100%;
                    background-attachment: fixed;
                    background-repeat: no-repeat;
                    background-color: black;
                }
            </style>

        </head>
        <body class="fundo1">
            <!-- NAVBAR -->
            <div> 
                <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #8B0000">
                    <a href="../index.php"><img src="../../img/zillaSistema.png" height="50px" width="110px"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

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
                    <li class="nav-item">
                        <a class="nav-link active" id="v-pills-profile-tab" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false" title="Pesquisar Parceiro"><i class="fa fa-search" style="color: red;"></i></a>
                    </li>
                </ul>
            </div>
            <!--FIM SEPARADOR-->

            <!--INICIO VISUALIZAR ENTRADA-->
            <div class="tab-content" id="v-pills-tabContent">
                <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                    <div class="margem">
                        <img src="../../img/zillaLogin.png" width="200px;" height="140px;" style="display: block; margin-left: auto; margin-right: auto;">
                        <h1 style="text-align: center;"><font color="yellow">Saída de Mercadoria</font></h1><br><br>
                        <form class="needs-validation" method="POST" action="#" novalidate target="_blank">
                            <?php while($linhaOINV = mysqli_fetch_assoc($resultadoOINV)){ ?>
                            <div class="form-row">
                                <div class="col-md-1 mb-3">
                                    <span><font size="2" color="yellow"><b>Número Nota</b></font></span>
                                    <input type="text" class="form-control" id="numeroNota" name="numeroNota" value="<?php echo $linhaOINV['idSaida'] ?>" readonly>       
                                </div>

                                <div class="distancia1 col-md-2 mb-3">
                                    <span><font size="2" color="yellow">Data de Lançamento</font></span>
                                    <input type="date" class="form-control" id="dataLancamento" name="dataLancamento" value="<?php echo $linhaOINV['dataLancamento'] ?>" readonly>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-3 mb-3">
                                    <span><font size="2" color="yellow">Código Fornecedor</font></span>
                                    <input type="text" class="form-control" id="codigoFornecedor" name="codigoFornecedor" value="<?php echo $linhaOINV['idParceiroNegocio'].' - '.$linhaOINV['nomeParceiro'] ?>" readonly>
                                </div>

                                <div class="distancia2 col-md-2 mb-3">
                                    <span><font size="2" color="yellow">Data de Vencimento</font></span>
                                    <input type="date" class="form-control" id="dataVencimento" name="dataVencimento" value="<?php echo $linhaOINV['dataVencimento'] ?>" readonly>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-2 mb-3">
                                    <span><font size="2" color="yellow">Depósito</font></span>
                                    <select type="text" class="form-control" id="deposito" name="deposito" readonly>
                                        <option><?php echo $linhaOINV['nomeDeposito'] ?></option>
                                    </select>
                                </div>

                                <div class="distancia3 col-md-1 mb-3">
                                    <span><font size="2" color="yellow">Parcelas</font></span>
                                    <select type="text" class="form-control" id="parcelas" name="parcelas" readonly>
                                        <option><?php echo $linhaOINV['parcelas']; ?>x</option>
                                    </select>
                                </div>
                            </div>
        
                            <!--ENCERRAMENTO DA OPCH-->

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

                            <div class="form-row" id="formulario">
                                <?php 
                                for ($i=1; $i <= $totalLinhas ; $i++) { 
                                    $v_tabelaINV1 = "
                                    SELECT T0.idSaida AS 'idSaida', T0.numLinha AS 'numLinha', T0.idProduto AS 'idProduto', T2.nomeProduto AS 'nomeProduto', T0.quantidadeSaida AS 'quantidadeSaida', T1.nomeUnidade AS 'nomeUnidade', T0.valorUnitario AS 'valorUnitario', T0.totalLinha AS 'totalLinha'
                                    FROM INV1 T0

                                    LEFT JOIN grupounidademedida T1
                                    ON T0.idGrupoUnidadeMedida = T1.idGrupoUnidadeMedida

                                    LEFT JOIN oitm T2
                                    ON T0.idProduto = T2.idProduto

                                    WHERE idSaida = '$v_idNota' 
                                    AND numLinha = '$i';";

                                    $v_resultadoINV1 = mysqli_query($conexao, $v_tabelaINV1);
                                    while ($linhaINV1 = mysqli_fetch_assoc($v_resultadoINV1)) {
                                        $v_idProduto = $linhaINV1['idProduto'];
                                        if (substr($v_idProduto, 0, 1) == 9) {
                                            $tabelaOITT = "SELECT * FROM OITT WHERE idFicha = '$v_idProduto';";
                                            $resultadoTabelaOITT = mysqli_query($conexao, $tabelaOITT);
                                            $linhaOITT = mysqli_fetch_assoc($resultadoTabelaOITT);
                                            $v_nomeProduto = $linhaOITT['nomeFicha'];
                                        }else{
                                            $v_nomeProduto = $linhaINV1['nomeProduto'];
                                        }
                                        
                                        $v_quantidade = $linhaINV1['quantidadeSaida'];
                                        $v_nomeUnidade = $linhaINV1['nomeUnidade'];
                                        $v_valorUnitario = number_format($linhaINV1['valorUnitario'], 2, ',', '.');
                                        $v_totalLinha = number_format($linhaINV1['totalLinha'], 2, ',', '.');
                                    echo "<div class='col-md-3 mb-1'>"
                                        ."<input type='text' class='form-control' id='produto1' name='produto1' value='$v_idProduto - $v_nomeProduto' readonly>"
                                        ."</div>"
                                        ."<div class='distanciaQuantidade col-md-1 mb-1'>"
                                        ."<input type='number' class='form-control' id='quantidade1' name='quantidade1' value='$v_quantidade' readonly>"
                                        ."</div>"
                                        ."<div class='distanciaUM col-md-1 mb-1'>"
                                        ."<input type='text' class='form-control' id='grupoUnidadeMedida1' name='grupoUnidadeMedida1' value='$v_nomeUnidade' readonly>"
                                        ."</div>"
                                        ."<div class='distanciaUnitario col-md-1 mb-1'>"
                                        ."<input type='text' class='form-control' id='valorUnitario1' name='valorUnitario1' value='R$ $v_valorUnitario' readonly>"
                                        ."</div>"
                                        ."<div class='distanciaTotal col-md-1 mb-1'>"
                                        ."<input type='text' style='text-align: center; font-size: 13px;' class='form-control' id='valorTotal1' name='valorTotal1' value='R$ $v_totalLinha' readonly>"
                                        ."</div>";
                                    }
                                }
                                ?>
                            </div>
                            <!-- INICIO LINHA DO VALOR TOTAL AUTOCOMPLETE -->
                             <div class="form-row" id="formulario">
                                <div class="col-md-3 mb-1">
                                    <input type="hidden" class="form-control">
                                </div>
                                <div class="distanciaQuantidade col-md-1 mb-1">
                                    <input type="hidden" class="form-control">
                                </div>


                                <div class="distanciaUM col-md-1 mb-1">
                                    <input type="hidden" class="form-control">
                                </div>


                                <div class="distanciaUnitario col-md-1 mb-1">
                                    <input type="hidden" class="form-control">
                                </div>
                                <?php
                                $conversao_valorTotal = number_format($linhaOINV['valorTotal'], 2, ',', '.');
                                ?>
                                <div class="distanciaTotal col-md-1 mb-1">
                                    <input type="text" style="text-align: center; font-size: 13px; background-color: #8B0000; color: white;" class="form-control" id="totalNota" name="totalNota" value="<?php echo 'R$ '.$conversao_valorTotal ?>" readonly>
                                </div>
                            </div>
                            <?php } ?>
                            <!-- FIM LINHA DO VALOR TOTAL AUTOCOMPLETE -->

                            <!--INICIO SEPARADOR-->
                            <div class="distanciaTop1">
                                <ul class="nav nav-tabs justify-content-center">
                                </ul>
                            </div>
                            <!--FIM SEPARADOR-->
                        </form><br><br>
                    </div>
                </div>
                <!--FIM VISUALIZAR ENTRADA-->
            </div>

            <!-- Optional JavaScript -->
            <!-- jQuery first, then Popper.js, then Bootstrap JS -->
            <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
            <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
        </body>
    </html>

<?php } ?>

