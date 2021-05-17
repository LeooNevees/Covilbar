<?php
include('../v_login.php');
$data = date('d-m-Y');
$tabelaCMD1 = "SELECT * FROM CMD1";
$resultadoCMD1 = mysqli_query($conexao, $tabelaCMD1);
echo "Gerando planilha CMD1<br>";
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
        <!--INÍCIO DOWNLOAD TABELA CMD1-->
        <?php
            //Nome do arquivo que será exportado
            $cmd1 = 'CMD1_'.$data.'.xlsx';
            
            //Criamos uma tabela HTML com o formato da Planilha
            $htmlCMD1 = '';
            $htmlCMD1 .= '<table border="1">';                        
            $htmlCMD1 .= '<tr>';
            $htmlCMD1 .= '<td align="center" class="borda"><b>idLinha</b></td>';
            $htmlCMD1 .= '<td align="center" class="borda"><b>idComanda</b></td>';
            $htmlCMD1 .= '<td align="center" class="borda"><b>numLinha</b></td>';
            $htmlCMD1 .= '<td align="center" class="borda"><b>idProduto</b></td>';
            $htmlCMD1 .= '<td align="center" class="borda"><b>quantidade</b></td>';
            $htmlCMD1 .= '<td align="center" class="borda"><b>valorUnitario</b></td>';
            $htmlCMD1 .= '<td align="center" class="borda"><b>totalLinha</b></td>';
            $htmlCMD1 .= '<td align="center" class="borda"><b>dataCadastro</b></td>';
            $htmlCMD1 .= '<td align="center" class="borda"><b>usuarioCadastro</b></td>';
            $htmlCMD1 .= '<td align="center" class="borda"><b>cancelado</b></td>';
            $htmlCMD1 .= '</tr>';
            
            //Selecionar todos os itens da tabela
            while ($linhaCMD1 = mysqli_fetch_assoc($resultadoCMD1)){
                $htmlCMD1 .= '<tr>';
                $htmlCMD1 .= '<td align="center" class="borda">' .$linhaCMD1['idLinha'].'</td>';
                $htmlCMD1 .= '<td align="center" class="borda">' .$linhaCMD1['idComanda'].'</td>';
                $htmlCMD1 .= '<td align="center" class="borda">' .$linhaCMD1['numLinha'].'</td>';
                $htmlCMD1 .= '<td align="center" class="borda">' .$linhaCMD1['idProduto'].'</td>';
                $htmlCMD1 .= '<td align="center" class="borda">' .$linhaCMD1['quantidade'].'</td>';
                $htmlCMD1 .= '<td align="center" class="borda">' .$linhaCMD1['valorUnitario'].'</td>';
                $htmlCMD1 .= '<td align="center" class="borda">' .$linhaCMD1['totalLinha'].'</td>';
                $htmlCMD1 .= '<td align="center" class="borda">' .$linhaCMD1['dataCadastro'].'</td>';
                $htmlCMD1 .= '<td align="center" class="borda">' .$linhaCMD1['usuarioCadastro'].'</td>';
                $htmlCMD1 .= '<td align="center" class="borda">' .$linhaCMD1['cancelado'].'</td>';
                $htmlCMD1 .= '</tr>';
            }
            
            //Configurações header para forçar o Download
            header("Expires: Mon, 26 Jul 1997 05:00>00 GMT");
            header("Last-Modified: " . gmdate("D, d M Yh:i:s"). " GMT");
            header("Cache-control: no-cache, must-revalidate");
            header("Pragma: no-cache");
            header("Content-type: application/x-msexcel");
            header("Content-Disposition: attachment; filename=\"{$cmd1}\"");
            header("Content-Description: PHP Generated Data" );
            
          //Envia o conteúdo do arquivo
          echo $htmlCMD1;
          exit();
        ?>      
        <!--FIM DOWNLOAD TABELA CMD1-->  

    </body> 
</html>

