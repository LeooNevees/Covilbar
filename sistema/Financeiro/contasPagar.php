<?php
include('../../v_login.php');

//CONEXAO BANCO DE DADOS
include '../../conexao.php';

//TABELA PARA PREENCHER A PAGINA DE CONTAS A PAGAR
$tabelaParcelas = "
SELECT T1.idParceiroNegocio AS 'idParceiro', T2.nomeParceiro AS 'nomeParceiro', COUNT(T0.parcela) AS 'parcelas', SUM(valorParcela) AS 'totalAcumulado'
FROM PCH2 T0

LEFT JOIN OPCH T1
ON T0.idEntrada = T1.idEntrada

LEFT JOIN OCRD T2
ON T1.idParceiroNegocio = T2.idParceiro

WHERE T0.status = 'ABERTO'

GROUP BY T1.idParceiroNegocio, T2.nomeParceiro

ORDER BY T2.nomeParceiro ASC;";
$resultadoParcelas = mysqli_query($conexao, $tabelaParcelas);
$retornoNumLinhas = mysqli_affected_rows($conexao);

//TABELA PARA TRAZER SOMENTE O ID DOS PN (COM PARCELAS EM ABERTO)
$tabelaIdPn = "
SELECT T1.idParceiroNegocio AS 'idParceiro'
FROM PCH2 T0

LEFT JOIN OPCH T1
ON T0.idEntrada = T1.idEntrada

LEFT JOIN OCRD T2
ON T1.idParceiroNegocio = T2.idParceiro

WHERE T0.status = 'ABERTO'

GROUP BY T1.idParceiroNegocio

ORDER BY T2.nomeParceiro ASC;";

$resultadoIdPn = mysqli_query($conexao, $tabelaIdPn);
while ($linhaIdPn = mysqli_fetch_assoc($resultadoIdPn)) {
    $idPn[] = $linhaIdPn['idParceiro'];
}

//TABELA TRAZENDO TUDO DA PCH2 EMBASADO NO $IdPn
$linha = 0;


date_default_timezone_set('America/Sao_Paulo');
$data = date('d/m');
$v_numLinha = 0;
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
        <link rel="stylesheet" href="style.css">

        <!-----ICONES------------>
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <script src="https://kit.fontawesome.com/70c48f08c7.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

        <script type="text/javascript">

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


            function analisarPN(idParc) {
                var popup = 'popup-' + idParc;
                document.getElementById(popup).classList.toggle("active");
            }

            function MostrarAltValor(num1, num2) {
                document.getElementById('tituloAlterar-' + num1).style.display = 'block';
                document.getElementById('alterarValor-' + num2).style.display = 'block';
            }

            function validar() {
                var finalizar = 0;
                if (finalizar == 0) {
                    alert("Vinculando pagamento a nota");
                    setTimeout(function () {
                        window.location.href = "http://localhost/CovilBar/sistema/Financeiro/contasPagar.php";
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
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Estoque
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="../Estoque/entrada.php"><font color="blue">Entrada</font></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../Estoque/saida.php"><font color="blue">Saida</font></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../Estoque/cadastroItem.php"><font color="blue">Cadastro do Item</font></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../Estoque/deposito.php"><font color="blue">Depósitos</font></a>
                            </div>
                        </li>
                        <li class="nav-item dropdown active">
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

        <!--INICIO ADICIONAR PN-->
        <div class="tab-content" id="v-pills-tabContent">
            <div class="margem">
            <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                    <img src="../../img/covilLogin.png" width="200px;" height="140px;" style="display: block; margin-left: auto; margin-right: auto;">
                    <h1 style="text-align: center; margin-left: 1%;"><font color="yellow">Pagamentos em aberto</font></h1><br><br>
                    <div class="margem2">
                    <div><br>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Id PN</th>
                                    <th scope="col">Nome do PN</th>
                                    <th scope="col">Aberto</th>
                                    <th scope="col">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($linhaParcelas = mysqli_fetch_assoc($resultadoParcelas)) {
                                    $v_idParceiro = $linhaParcelas['idParceiro'];
                                    $v_nomeParceiro = $linhaParcelas['nomeParceiro'];
                                    $v_parcelas = $linhaParcelas['parcelas'];
                                    $v_totalAcumulado = number_format($linhaParcelas['totalAcumulado'], 2, ',', '.');
                                    echo "<tr onclick='analisarPN($v_numLinha)' style='cursor:pointer'>"
                                    . "<td>$v_numLinha</td>"
                                    . "<td>$v_idParceiro</td>"
                                    . "<td>$v_nomeParceiro</td>"
                                    . "<td>$v_parcelas</td>"
                                    . "<td>R$ $v_totalAcumulado</td>"
                                    . "</tr>";
                                    $v_numLinha += 1;
                                }
                                ?>
                            </tbody>
                        </table>
                        <br>
                    </div>
                    </div>



                    <?php
                    for ($i = 0; $i < $retornoNumLinhas; $i++) {
                        $tabelaPCH2 = "
            SELECT T0.idParcela AS 'idParcela', T1.idParceiroNegocio AS 'idParceiroNegocio', T0.idEntrada AS 'idEntrada', T0.parcela AS 'parcela', T0.totalParcelas AS 'totalParcelas', T0.valorParcela AS 'valorParcela', date_format(T0.dataVencParcela, '%d-%m-%Y') AS 'dataVencParcela'
            FROM PCH2 T0

            LEFT JOIN OPCH T1
            ON T0.idEntrada = T1.idEntrada

            LEFT JOIN OCRD T2
            ON T1.idParceiroNegocio = T2.idParceiro

            WHERE T0.status = 'ABERTO'
            AND T1.idParceiroNegocio = '$idPn[$i]'

            ORDER BY T2.nomeParceiro, T0.idParcela;";

                        $resultadoPCH2 = mysqli_query($conexao, $tabelaPCH2);
                        echo "<div class='popup' id='popup-$i'>"
                        . "<div class='overlay'>"
                        . "<div class='content' id='content'>"
                        . "<div class='close-btn' onclick='analisarPN($i)'>&times;</div>"
                        . "<table class='table  table-striped'>"
                        . "<thead>"
                        . "<tr>"
                        . "<th scope='col'>ID</th>"
                        . "<th scope='col'>PN</th>"
                        . "<th scope='col'>Nota</th>"
                        . "<th scope='col'>Parcela</th>"
                        . "<th scope='col'>Valor</th>"
                        . "<th scope='col'>Modificar</th>"
                        . "<th scope='col' style='display:none;' id='tituloAlterar-$i'>Alt</th>"
                        . "<th scope='col'>Vencimento</th>"
                        . "<th scope='col'></th>"
                        . "</tr>"
                        . "</thead>"
                        . "<tbody>";

                        while ($linhaPCH2 = mysqli_fetch_assoc($resultadoPCH2)) {
                            $v_idParcela = $linhaPCH2['idParcela'];
                            $v_idParceiroNegocio = $linhaPCH2['idParceiroNegocio'];
                            $v_idEntrada = $linhaPCH2['idEntrada'];
                            $v_parcela = $linhaPCH2['parcela'] . '/' . $linhaPCH2['totalParcelas'];
                            $v_valorParcela = number_format($linhaPCH2['valorParcela'], 2, ',', '.');
                            $v_dataVencParcela = $linhaPCH2['dataVencParcela'];

                            echo "<tr id='$linha'>"
                            . "<td scope='row'>$v_idParcela</td>"
                            . "<td scope='row'>$v_idParceiroNegocio</td>"
                            . "<td scope='row'><a href='../Estoque/visualizarNota.php?tipoNota=1&idEntrada=$v_idEntrada' target='_blank'><font color='red'><b>$v_idEntrada</b></font></a></td>"
                            . "<td scope='row'>$v_parcela</td>"
                            . "<td scope='row'>R$ $v_valorParcela</td>"
                            . "<td scope='row'><input class='form-check-input' type='checkbox' id='defaultCheck1' onclick='MostrarAltValor($i, $linha)'></td>"
                            . "<td scope='row' style='display:none;' id='alterarValor-$linha'><input type='number' class='tamCampo'></td>"
                            . "<td scope='row'>$v_dataVencParcela</td>"
                            . "<td scope='row'><a href='cadastrarPagamento.php?idParcela=$v_idParcela' target='_blank' class='btn btn-outline-primary' onclick='validar()'>Pagar</a></td>"
                            . "</tr>";
                            $linha += 1;
                        }
                        echo "</tbody>"
                        . "</table>"
                        . "</div>"
                        . "</div>"
                        . "</div>"
                        . "</div>";
                    }
                    ?>

                </div>
            </div>


            <!--FIM ADICIONAR PN-->

             <!--INICIO EDITAR PN-->
            <div class="tab-pane fade alinhamentoTopParceiro" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                <div class="margem">
                    <img src="../../img/covilLogin.png" width="200px;" height="140px;" style="display: block; margin-left: auto; margin-right: auto;">
                    <form class="needs-validation" method="POST" action="editarParceiro.php" target="_blank">
                        <div class="form-group">
                            <label for="inputAddress"><font color="yellow">Digite o nome do PN <font color="red">*</font></b></font></label>
                            <input type="text" class="form-control" id="nomePN" name="nomePN" placeholder="Insira o nome do PN que deseja alterar" list="listaParceiro" required>
                            <datalist id="listaParceiro">
                            <?php while($linha = mysqli_fetch_assoc($resultadoIdOcrd)){ if($linha['cancelado'] == 'N'){$status = 'ATIVO';}else{$status = 'INATIVO';}?>
                                <option value="<?php echo $linha['idParceiro'].' - '.$linha['nomeParceiro'].' - '.$status; ?>"></option>
                            <?php } ?>
                            <option>Todos</option>
                            </datalist>
                        </div><br>

                        <button type="submit" class="btn btn-outline-primary" style="margin-left: 43%;" onclick="return validarAlteracao()">Analisar</button>

                    </form>
                </div>
            </div>
                <!--FIM EDITAR PN-->
        </div>

        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>


    </body>
</html>
<?php 
