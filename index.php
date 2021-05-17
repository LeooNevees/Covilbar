<?php
session_start();
?>

<!doctype html>
<html lang="pt-br">
    <head>
        <!-- ICONE NA BARRA DO NAVEGADOR-->
        <link rel="shortcut icon" href="img/zillaMonstro.png">

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

        <script type="text/javascript">
            //FUNÇÃO PARA VALIDAR O CAMPO LOGIN E SENHA
            function verificar() {
                if (document.getElementById('usuario').value == '') {
                    alert('Por favor insira o usuário');
                    return false;
                }

                if (document.getElementById('senha').value == '') {
                    alert('Por favor insira a senha');
                    return false;
                }
            }
        </script>
    </head>

    <body class="corFundo">      
        <div class="alinhamentoLogin">
            <img src="img/covilLogin.png" class="imgAlinhamento">
            <form action="analiseLogin.php" method="POST" class="alinhamentoLeft">
                <div class="form-group alinhamentoTop">
                    <label><font color="red">Usuário</font></label>
                    <input type="text" name="usuario"  class="form-control" id="usuario" placeholder="Insira seu usuário">
                </div>
                <div class="form-group">
                    <label><font color="red">Password</font></label>
                    <input type="password" name="senha" id="senha"  class="form-control"  placeholder="Insira a senha">
                </div>

                <div class="col-md-12 text-center ">
                    <button type="submit" class="btn btn-outline-danger btn-block mybtn tx-tfm alinhamentoTop" onclick="return verificar()">Acessar</button>
                </div>
                <div class="col-md-12 ">
                    <div class="login-or">
                        <hr class="hr-or">
                    </div>
                </div>
                <!---------------------------ALERTA DE USUÁRIO ERRADO -------------------------------->
                <?php if (isset($_SESSION['nao_autenticado'])) { ?>
                    <div class="alert alert-danger" role="alert"><font style="font-size: 15px"><b>Usuário Inválido</b></font></div>
                    <?php }unset($_SESSION['nao_autenticado']); ?>
            </form>  
        </div>

        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
    </body>
</html>