<?php
include('../v_login.php');
$data = date('d-m-Y');
$tabelaOCMD = "SELECT * FROM OCMD;"; 
$resultadoOCMD = mysqli_query($conexao, $tabelaOCMD);
?>

<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>Planilha</title>
        <style type="text/css">
            .borda{
                border: 0.5px solid #191970;
                font-family: arial;
            }           
        </style>
    </head>
    
    <body>
        <!--INÍCIO DOWNLOAD TABELA OCMD-->
        <?php
            //Nome do arquivo que será exportado
            $ocmd = 'OCMD_'.$data.'.xlsx';
            
            //Criamos uma tabela HTML com o formato da Planilha
            $htmlOCMD = '';
            $htmlOCMD .= '<table border="1">';                        
            $htmlOCMD .= '<tr>';
            $htmlOCMD .= '<td align="center" class="borda"><b>idComanda</b></td>';
            $htmlOCMD .= '<td align="center" class="borda"><b>Comanda</b></td>';
            $htmlOCMD .= '<td align="center" class="borda"><b>nomeCliente</b></td>';
            $htmlOCMD .= '<td align="center" class="borda"><b>horaEntrada</b></td>';
            $htmlOCMD .= '<td align="center" class="borda"><b>horaSaida</b></td>';
            $htmlOCMD .= '<td align="center" class="borda"><b>valorTotal</b></td>';
            $htmlOCMD .= '<td align="center" class="borda"><b>valorPago</b></td>';
            $htmlOCMD .= '<td align="center" class="borda"><b>troco</b></td>';
            $htmlOCMD .= '<td align="center" class="borda"><b>dataCadastro</b></td>';
            $htmlOCMD .= '<td align="center" class="borda"><b>status</b></td>';
            $htmlOCMD .= '<td align="center" class="borda"><b>usuarioCadastro</b></td>';
            $htmlOCMD .= '<td align="center" class="borda"><b>cancelado</b></td>';
            $htmlOCMD .= '</tr>';
            
            //Selecionar todos os itens da tabela
            while ($linhaOCMD = mysqli_fetch_assoc($resultadoOCMD)){
                $htmlOCMD .= '<tr>';
                $htmlOCMD .= '<td align="center" class="borda">' .$linhaOCMD['idComanda'].'</td>';
                $htmlOCMD .= '<td align="center" class="borda">' .$linhaOCMD['comanda'].'</td>';
                $htmlOCMD .= '<td align="center" class="borda">' .$linhaOCMD['nomeCliente'].'</td>';
                $htmlOCMD .= '<td align="center" class="borda">' .$linhaOCMD['horaEntrada'].'</td>';
                $htmlOCMD .= '<td align="center" class="borda">' .$linhaOCMD['horaSaida'].'</td>';
                $htmlOCMD .= '<td align="center" class="borda">' .$linhaOCMD['valorTotal'].'</td>';
                $htmlOCMD .= '<td align="center" class="borda">' .$linhaOCMD['valorPago'].'</td>';
                $htmlOCMD .= '<td align="center" class="borda">' .$linhaOCMD['troco'].'</td>';
                $htmlOCMD .= '<td align="center" class="borda">' .$linhaOCMD['dataCadastro'].'</td>';
                $htmlOCMD .= '<td align="center" class="borda">' .$linhaOCMD['status'].'</td>';
                $htmlOCMD .= '<td align="center" class="borda">' .$linhaOCMD['usuarioCadastro'].'</td>';
                $htmlOCMD .= '<td align="center" class="borda">' .$linhaOCMD['cancelado'].'</td>';
                $htmlOCMD .= '</tr>';
            }
            
            //Configurações header para forçar o Download
            header("Expires: Mon, 26 Jul 1997 05:00>00 GMT");
            header("Last-Modified: " . gmdate("D, d M Yh:i:s"). " GMT");
            header("Cache-control: no-cache, must-revalidate");
            header("Pragma: no-cache");
            header("Content-type: application/x-msexcel");
            header("Content-Disposition: attachment; filename=\"{$ocmd}\"");
            header("Content-Description: PHP Generated Data" );
            
          //Envia o conteúdo do arquivo
          echo $htmlOCMD;
        ?>      
        <!--FIM DOWNLOAD TABELA OCMD--> 
        <h1>Deu certo</h1> 
    </body> 
</html>
