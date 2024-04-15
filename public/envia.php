<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>recebe dados</title>
</head>
<body>
    <?php

    $conexao = mysqli_connect("localhost","root", "","teste" );
    // checando conexao
    if(!$conexao){
        echo "NÃO CONECTADO";

    }

    echo "CONECTADO AO BANCO <br>";

// verificar email se ja existe
$email = $_POST['email'];

$sql = "SELECT email FROM teste.users WHERE email='$email'";
$retorno = mysqli_query($conexao, $sql);

if (mysqli_num_rows($retorno)>0 ) {
    echo "EMAIL JÁ CADASTRADO! <br>";
}else {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $idade = $_POST['idade'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];

    $sql = "INSERT INTO teste.users(nome,email,idade,cidade,estado) VALUES ('$nome','$email','$idade','$cidade','$estado')";
    $resultado = mysqli_query($conexao, $sql);
    echo ">>>USUÁRIO CADASTRADO COM SUCESSO!<br>";
}






?>
</body>
</html>