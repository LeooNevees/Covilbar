<?php
include('../../v_login.php');

//CONEXAO BANCO DE DADOS
include '../../conexao.php';

$tabelaOCRD = "SELECT * FROM OCRD ";
$resultadoIdOcrd = mysqli_query($conexao, $tabelaOCRD);
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
            //MASCARA CNPJ E CPF
            function formatarCampo(campoTexto) {
                if (campoTexto.value.length <= 11) {
                    campoTexto.value = mascaraCpf(campoTexto.value);
                } else {
                    campoTexto.value = mascaraCnpj(campoTexto.value);
                }
            }
            function retirarFormatacao(campoTexto) {
                campoTexto.value = campoTexto.value.replace(/(\.|\/|\-)/g, "");
            }
            function mascaraCpf(valor) {
                return valor.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/g, "\$1.\$2.\$3\-\$4");
            }
            function mascaraCnpj(valor) {
                return valor.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/g, "\$1.\$2.\$3\/\$4\-\$5");
            }

            //MASCARA NUMERO TELEFONE
            function mask(o, f) {
                setTimeout(function () {
                    var v = mphone(o.value);
                    if (v != o.value) {
                        o.value = v;
                    }
                }, 1);
            }

            function mphone(v) {
                var r = v.replace(/\D/g, "");
                r = r.replace(/^0/, "");
                if (r.length > 10) {
                    r = r.replace(/^(\d\d)(\d{5})(\d{4}).*/, "($1) $2-$3");
                } else if (r.length > 5) {
                    r = r.replace(/^(\d\d)(\d{4})(\d{0,4}).*/, "($1) $2-$3");
                } else if (r.length > 2) {
                    r = r.replace(/^(\d\d)(\d{0,5})/, "($1) $2");
                } else {
                    r = r.replace(/^(\d*)/, "($1");
                }
                return r;
            }

            //PESQUISAR CEP AUTOMÁTICO 
            function limpa_formulário_cep() {
                //Limpa valores do formulário de cep.
                document.getElementById('rua').value = ("");
                document.getElementById('bairro').value = ("");
                document.getElementById('cidade').value = ("");
                document.getElementById('uf').value = ("");
                document.getElementById('ibge').value = ("");
            }

            function meu_callback(conteudo) {
                if (!("erro" in conteudo)) {
                    //Atualiza os campos com os valores.
                    document.getElementById('rua').value = (conteudo.logradouro);
                    document.getElementById('bairro').value = (conteudo.bairro);
                    document.getElementById('cidade').value = (conteudo.localidade);
                    document.getElementById('uf').value = (conteudo.uf);
                    document.getElementById('ibge').value = (conteudo.ibge);
                } //end if.
                else {
                    //CEP não Encontrado.
                    limpa_formulário_cep();
                    alert("CEP não encontrado.");
                }
            }

            function pesquisacep(valor) {

                //Nova variável "cep" somente com dígitos.
                var cep = valor.replace(/\D/g, '');

                //Verifica se campo cep possui valor informado.
                if (cep != "") {

                    //Expressão regular para validar o CEP.
                    var validacep = /^[0-9]{8}$/;

                    //Valida o formato do CEP.
                    if (validacep.test(cep)) {

                        //Preenche os campos com "..." enquanto consulta webservice.
                        document.getElementById('rua').value = "...";
                        document.getElementById('bairro').value = "...";
                        document.getElementById('cidade').value = "...";
                        document.getElementById('uf').value = "...";
                        document.getElementById('ibge').value = "...";

                        //Cria um elemento javascript.
                        var script = document.createElement('script');

                        //Sincroniza com o callback.
                        script.src = 'https://viacep.com.br/ws/' + cep + '/json/?callback=meu_callback';

                        //Insere script no documento e carrega o conteúdo.
                        document.body.appendChild(script);

                    } //end if.
                    else {
                        //cep é inválido.
                        limpa_formulário_cep();
                        alert("Formato de CEP inválido.");
                    }
                } //end if.
                else {
                    //cep sem valor, limpa formulário.
                    limpa_formulário_cep();
                }
            }
            ;

            function validar() {
                var finalizar = 0;

                var valorTipo = $("#tipo").val();

                if (document.getElementById('nome').value == '') {
                    alert('Por favor insira o nome completo do parceiro');
                    return false;
                    finalizar = 1;
                }

                if (valorTipo == 0) {
                    alert('Por favor insira o tipo do Fornecedor');
                    return false;
                    finalizar = 1;
                }

                if (finalizar == 0) {
                    alert("Cadastrando Parceiro de Negócio");
                    setTimeout(function () {
                        window.location.href = "http://localhost/CovilBar/sistema/Parceiros";
                    }, 500);
                }
            }

            function validarAlteracao(){
                if (document.getElementById('nomePN').value == null || document.getElementById('nomePN').value == '') {
                    alert('Necessário digitar o nome do PN antes de continuar');
                    return false;
                }

                setTimeout(function () {
                        window.location.href = "http://localhost/CovilBar/sistema/Parceiros";
                }, 500);
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
                        <li class="nav-item active">
                            <a class="nav-link" href="index.php">Parceiros</a>
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

        <!--INICIO ADICIONAR PN-->
        <div class="tab-content" id="v-pills-tabContent">
            <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                <div class="margem">
                    <img src="../../img/covilLogin.png" width="200px;" height="140px;" style="display: block; margin-left: auto; margin-right: auto;">
                    <h1 style="text-align: center; margin-left: 2%;"><font color="yellow">Cadastro do Parceiro de Negócio</font></h1><br><br>
                    <form class="needs-validation" method="POST" action="cadastrarParceiro.php" target="_blank">
                        <div class="form-group">
                            <label for="inputAddress"><font color="yellow">Nome Completo <font color="red">*</font></b></font></label>
                            <input type="text" class="form-control" id="nome" name="nome" placeholder="Insira o nome do PN">
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="inputEmail4"><font color="yellow">Nome Fantasia</font></label>
                                <input type="text" class="form-control" id="nomeFantasia" name="nomeFantasia" placeholder="Insira o nome fantasia do PN">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="inputPassword4"><font color="yellow">Tipo <font color="red">*</font></b></font></label>
                                <select id="inputState" class="form-control" id="tipo" name="tipo">
                                    <option value="FORNECEDOR" selected>Fornecedor</option>
                                    <option value="CLIENTE">Cliente</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="inputEmail4"><font color="yellow">E-mail</font></label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Insira o e-mail">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="inputPassword4"><font color="yellow">Sexo</font></label>
                                <select id="inputState" class="form-control" id="sexo" name="sexo">
                                    <option value="MASCULINO" selected>Masculino</option>
                                    <option value="FEMININO">Feminino</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="inputEmail4"><font color="yellow">CPF/CNPJ</font></label>
                                <input type="text" class="form-control" id="numDocumento" name="numDocumento" onfocus="javascript: retirarFormatacao(this);" onblur="javascript: formatarCampo(this);" maxlength="14" placeholder="Insira o CPF ou CNPJ">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="inputEmail4"><font color="yellow">Telefone</font></label>
                                <input type="text" class="form-control" id="telefone" name="telefone" onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" placeholder="ex: (14)99111-2211">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="inputZip"><font color="yellow">CEP</font></label>
                                <input type="text" class="form-control" id="cep" name="cep" size="10" maxlength="9" onblur="pesquisacep(this.value);" placeholder="Ex: 17020-024">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="inputCity"><font color="yellow">Rua</font></label>
                                <input type="text" class="form-control" id="rua" name="rua">
                            </div>

                            <div class="form-group col-md-2">
                                <label for="inputState"><font color="yellow">N°</font></label>
                                <input type="text" class="form-control" id="numEndereco" name="numEndereco" maxlength="9">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-5">
                                <label for="inputState"><font color="yellow">Bairro</font></label>
                                <input type="text" class="form-control" id="bairro" name="bairro">
                            </div>

                            <div class="form-group col-md-5">
                                <label for="inputState"><font color="yellow">Cidade</font></label>
                                <input type="text" class="form-control" id="cidade" name="cidade">
                            </div>

                            <div class="form-group col-md-2">
                                <label for="inputState"><font color="yellow"><b>UF</b></font></label>
                                <input type="text" class="form-control" id="uf" name="uf">
                            </div>
                        </div>

                        <div class="form-group col-md-2" style="display: none">
                            <label for="inputState"><font color="grey"><b>ibge</b></font></label>
                            <input type="text" class="form-control" id="ibge" name="ibge">
                        </div>

                        <span><font color="red">(*) Campos Obrigatórios</font></span><br><br>

                        <button type="submit" class="btn btn-outline-primary" style="margin-left: 43%;" onclick="return validar()">Cadastrar</button>

                    </form>
                </div>
            </div><br><br><br>


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
                            </datalist>
                        </div><br>

                        <button type="submit" class="btn btn-outline-primary" style="margin-left: 43%;" onclick="return validarAlteracao()">Analisar</button>

                    </form>
                </div><br><br>
            </div>
                <!--FIM EDITAR PN-->
        </div>



            <!-- Optional JavaScript -->
            <!-- jQuery first, then Popper.js, then Bootstrap JS -->
            <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
            <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>

            <!-- SCRIPT PARA VALIDAR O FORMULÁRIO, RETORNANDO VERMELHO NOS CAMPOS INCOMPLETOS-->
            <script>
                                    // Example starter JavaScript for disabling form submissions if there are invalid fields
                                    (function () {
                                        'use strict';
                                        window.addEventListener('load', function () {
                                            // Fetch all the forms we want to apply custom Bootstrap validation styles to
                                            var forms = document.getElementsByClassName('needs-validation');
                                            // Loop over them and prevent submission
                                            var validation = Array.prototype.filter.call(forms, function (form) {
                                                form.addEventListener('submit', function (event) {
                                                    if (form.checkValidity() === false) {
                                                        event.preventDefault();
                                                        event.stopPropagation();
                                                    }
                                                    form.classList.add('was-validated');
                                                }, false);
                                            });
                                        }, false);
                                    })();
            </script>
    </body>
</html>
