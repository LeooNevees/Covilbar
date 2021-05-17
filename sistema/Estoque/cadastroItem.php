<?php
include('../../v_login.php');

//CONEXAO BANCO DE DADOS
include '../../conexao.php';

$tabelaGrupo = "SELECT * FROM GrupoItens WHERE cancelado = 'N';";
$resultadoGrupo = mysqli_query($conexao, $tabelaGrupo);

$tabelaUM = "SELECT * FROM grupounidademedida ORDER BY idGrupoUnidadeMedida ASC LIMIT 3";
$resultadoUM = mysqli_query($conexao, $tabelaUM);

$tabelaItens = "
SELECT T0.idProduto AS 'idProduto', T0.nomeProduto AS 'nomeProduto', T2.nomeGrupo AS 'grupoItens', SUM(T1.quantidadeEstoque) AS 'quantidadeEstoque', T0.cancelado AS 'cancelado'
FROM OITM T0 

LEFT JOIN OITW T1 
ON T0.idProduto = T1.idProduto

LEFT JOIN grupoitens T2
ON T0.idGrupoItens = T2.idGrupoItens

GROUP BY T0.idProduto

ORDER BY T0.nomeProduto";
$resultadoItem = mysqli_query($conexao, $tabelaItens);
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

            function validarItem(numero) {
                var finalizarA = 0;

                if (numero > 0) {
                    alert('Por favor faça toda a saída do item para inativá-lo.');
                    return false;
                    finalizar = 1;
                }

                if (finalizarA == 0) {
                    alert("Alterando Item");
                    setTimeout(function () {
                        window.location.href = "http://localhost/CovilBar/sistema/Estoque/cadastroItem.php";
                    }, 500);
                }
            }

            function entradaVal() {
                var finalizar = 0;

                if (document.getElementById('nomeItem').value == '' || document.getElementById('nomeItem') == null) {
                    alert('Por favor insira o nome do Item');
                    finalizar = 1;
                    return false;
                }

                if (document.getElementById('grupoItens').value == 0) {
                    alert('Por favor selecione um grupo de itens');
                    finalizar = 1;
                    return false;
                }


                if (document.getElementById('unidadeMedida').value == 0) {
                    alert('Por favor insira uma Unidade de Medida');
                    finalizar = 1;
                    return false;
                }

                if (finalizar == 0) {
                    alert("Cadastrando Item");
                    setTimeout(function () {
                        window.location.href = "http://localhost/CovilBar/sistema/Estoque/cadastroItem.php";
                    }, 500);
                }
            }

            function validarAlteracao() {
                if (document.getElementById('nomeItemAlt').value == null || document.getElementById('nomeItemAlt').value == '') {
                    alert('Necessário digitar o nome do Item antes de continuar');
                    return false;
                }

                setTimeout(function () {
                    window.location.href = "http://localhost/CovilBar/sistema/Estoque/cadastroItem.php";
                }, 500);
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
                                <a class="dropdown-item" href="../Estoque/entrada.php"><font color="blue">Entrada</font></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../Estoque/saida.php"><font color="blue">Saida</font></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../Estoque/cadastroItem.php"><font color="blue">Cadastro do Item</font></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../Estoque/fichaTecnica.php"><font color="blue">Ficha Técnica</font></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../Estoque/deposito.php"><font color="blue">Depósitos</font></a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Financeiro
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="../Financeiro/contasPagar.php"><font color="black">Contas a Pagar</font></a>
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

        <!--INICIO ADICIONAR ITEM-->
        <div class="tab-content" id="v-pills-tabContent">
            <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                <div class="CadastroItemMargem">
                    <img src="../../img/covilLogin.png" width="200px;" height="140px;" style="display: block; margin-left: auto; margin-right: auto;">
                    <h1 style="text-align: center; margin-left: 1%;"><font color="yellow">Cadastro do Item</font></h1><br><br>
                    <form class="needs-validation" method="POST" action="cadastrarItem.php" target="_blank">
                        <div class="form-group">
                            <label for="inputAddress"><font color="yellow">Nome do Item <font color="red">*</font></b></font></label>
                            <input type="text" class="form-control" id="nomeItem" name="nomeItem" placeholder="Insira o nome do item">
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="inputEmail4"><font color="yellow">Grupo de Itens <font color="red">*</font></b></font></font></label>
                                <select class="form-control" id="grupoItens" name="grupoItens">
                                    <option value="0">Selecione</option>
                                    <?php while ($linhaGrupo = mysqli_fetch_assoc($resultadoGrupo)) { ?>
                                        <option value="<?php echo $linhaGrupo['idGrupoItens'] ?>"><?php echo $linhaGrupo['nomeGrupo']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label><font color="yellow">Unidade de Medida Padrão <font color="red">*</font></b></font></label>
                                <select class="form-control" id="unidadeMedida" name="unidadeMedida">
                                    <option value="0">Selecione</option>
                                    <?php while ($linhaUnidade = mysqli_fetch_assoc($resultadoUM)) { ?>
                                        <option value="<?php echo $linhaUnidade['idGrupoUnidadeMedida'] ?>"><?php echo $linhaUnidade['nomeUnidade']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <label class="switch">
                            <input type="checkbox" id="itemCompra" name="itemCompra">
                            <span class="slider round"></span>
                        </label>
                        <span style="color: yellow; font-size: 15px;">Item de Compra</span><br>

                        <label class="switch">
                            <input type="checkbox" id="itemVenda" name="itemVenda">
                            <span class="slider round"></span>
                        </label>
                        <span style="color: yellow; font-size: 15px;">Item de Venda</span><br><br><br>

                        <span><font color="red">(*) Campos Obrigatórios</font></span><br><br>

                        <button type="submit" class="btn btn-outline-primary" style="margin-left: 45%;" onclick="return entradaVal()">Cadastrar</button>

                    </form>
                </div>
            </div><br><br><br>
            <!--FIM ADICIONAR ITEM-->

            <!--INICIO EDITAR ITEM-->
            <div class="tab-pane fade " id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                <div class="CadastroItemMargem">
                    <img src="../../img/covilLogin.png" width="200px;" height="140px;" style="display: block; margin-left: auto; margin-right: auto;">
                    <form class="needs-validation" method="POST" action="editarItem.php" target="_blank">
                        <div class="form-group">
                            <label for="inputAddress"><font color="yellow">Digite o nome do Item <font color="red">*</font></b></font></label>
                            <input type="text" class="form-control" id="nomeItemAlt" name="nomeItemAlt" placeholder="Insira o nome do Item que deseja alterar" list="listaItem" required>
                            <datalist id="listaItem" >
                                <?php
                                while ($linha = mysqli_fetch_assoc($resultadoItem)) {
                                    if ($linha['cancelado'] == 'N') {
                                        $status = 'ATIVO';
                                    } else {
                                        $status = 'INATIVO';
                                    }
                                    ?>
                                    <option value="<?php echo $linha['idProduto'] . ' - ' . $linha['nomeProduto'] . ' - ' . $status; ?>"></option>
                                <?php } ?>
                            </datalist>
                        </div><br>

                        <button type="submit" class="btn btn-outline-primary" style="margin-left: 43%;" onclick="return validarAlteracao()">Analisar</button>

                    </form>
                </div><br><br>
            </div>
            <!--FIM EDITAR ITEM-->
        </div>



        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
    </body>
</html>