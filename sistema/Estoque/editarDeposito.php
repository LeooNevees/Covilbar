<?php
include('../../v_login.php');

$deposito = filter_input(INPUT_POST, 'nomeDepositoAlt', FILTER_SANITIZE_NUMBER_INT);
$idDeposito = (int)trim($deposito);

//CONEXAO BANCO DE DADOS
include '../../conexao.php';
$tabela = "SELECT * FROM OWHS WHERE idDeposito = '$idDeposito';";
$resultado = mysqli_query($conexao, $tabela);
if (mysqli_affected_rows($conexao) <= 0) {
    echo "Não foi possível encontrar depósito. Por favor refaça o procedimento";
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
            function validar() {
		    	var finalizar = 0;

		    	if (document.getElementById('nomeDeposito').value == '' || document.getElementById('nomeDeposito').value == null) {
		    		alert('Por favor insira o nome do deposito');
		    		return false;
		    		finalizar = 1;
		    	}

		    	if (document.getElementById('localizacao').value == '' || document.getElementById('localizacao').value == null) {
		    		alert('Por favor insira a localização do deposito');
		    		return false;
		    		finalizar = 1;
		    	}

		    	if(finalizar == 0){
					alert("Atualizando Depósito");
					setTimeout(function() {
			    		window.close();
					}, 500);
				}
   			}

   			function validarAlteracao() {
   				var finalizar = 0;

   				if (document.getElementById('nomeDepositoAlt').value == '' || document.getElementById('nomeDepositoAlt').value == null) {
		    		alert('Por favor insira o ID ou o nome do deposito');
		    		return false;
		    		finalizar = 1;
		    	}else{
		    		var nomeDepositoAlt = document.getElementById('nomeDepositoAlt').value;
		    		var id = nomeDepositoAlt.substr(0, 2);
		    		
		    		if (isNaN(id)) {
                    	alert('Faltando ID do depósito');
                    	return false;
                	}

		    	}

		    	if(finalizar == 0){
					setTimeout(function() {
			    		window.location.href = "http://localhost/CovilBar/sistema/Estoque/deposito.php";
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

        <!--INICIO ADICIONAR DEPÓSITO-->
        <div class="tab-content" id="v-pills-tabContent">
            <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                <div class="CadastroItemMargem">
                    <img src="../../img/covilLogin.png" width="200px;" height="140px;" style="display: block; margin-left: auto; margin-right: auto;">
                    <h1 style="text-align: center; margin-left: 1%;"><font color="yellow">Editar dados do Depósito</font></h1><br><br>
                    <form class="needs-validation" method="POST" action="atualizarDeposito.php" target="_blank">
                        <div class="form-row EditarDepositoMargem2">
                        	<div class="form-group col-md-2">
                                <label for="inputEmail4"><font color="yellow">Depósito <font color="red"><b>*</b></font></font></label>
                                <input type="text" class="form-control" id="idDeposito" name="idDeposito" value="<?php echo $idDeposito ?>" readonly>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="inputEmail4"><font color="yellow">Nome Depósito <font color="red"><b>*</b></font></font></label>
                                <input type="text" class="form-control" id="nomeDeposito" name="nomeDeposito" value="<?php echo $linha['nomeDeposito'] ?>" readonly>
                            </div>

                            <?php if($linha['cancelado'] == 'S'){ ?>
                            	<div class="form-group col-md-3">
	                                <label for="inputPassword4"><font color="yellow">Status <font color="red">*</font></b></font></label>
	                                <select id="inputState" class="form-control btn-danger" id="status" name="status">
                                		<option value="S">Inativo</option>
                                    <option value="N">Ativo</option>
                                    </select>
	                            </div>
                            <?php }else{ ?>
                            	<div class="form-group col-md-3">
	                                <label for="inputPassword4"><font color="yellow">Status <font color="red">*</font></b></font></label>
	                                <select id="inputState" class="form-control btn-success" id="status" name="status">
                                    <option value="N">Ativo</option>
                                    <option value="S">Inativo</option>
                                    </select>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="form-row EditarDepositoMargem2">
                            <div class="form-group col-md-9">
                                <label for="inputEmail4"><font color="yellow">Localização <font color="red"><b>*</b></font></font></label>
                                <input type="text" class="form-control" id="localizacao" name="localizacao" value="<?php echo $linha['localizacao'] ?>" readonly>
                            </div>
                        </div>
                        <span class="EditarDepositoMargem2"><font color="red">(*) Campos Obrigatórios</font></span><br>
                        <span class="EditarDepositoMargem2"><font color="grey">Campos bloqueados por segurança</font></span><br><br>

                        <div class="margemBotao">
                        	<button type="submit" class="btn btn-outline-primary" style="margin-left: 43%;" onclick="return validar()">Atualizar</button>
                        </div>
                    </form>
                </div>
            </div><br><br><br>
            <!--FIM ADICIONAR DEPÓSITO-->
        </div>



            <!-- Optional JavaScript -->
            <!-- jQuery first, then Popper.js, then Bootstrap JS -->
            <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
            <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
    </body>
</html>
