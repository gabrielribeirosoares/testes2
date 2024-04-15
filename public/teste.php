<?php

require_once '../config.php';

    // definição da ligação
    $gestor = new PDO("mysql:host=". MYSQL_HOST . ";" .
    "dbname=" . MYSQL_DATABASE . "; charset=utf8",
    MYSQL_USER,
    MYSQL_PASS
);
$dados = $gestor->query("SELECT * FROM users");
$users = $dados->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
        <h3>Users</h3>
        <hr>
        <table border="1">
            <thead>
                <tr>
                    <th>ID User</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Data de nascimento</th>
                    <th>Cidade</th>
                    <th>Estado</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach($users as $user): ?>
                    <tr>
                        <td><?= $user["id_user"] ?></td>
                        <td><?= $user["nome"] ?></td>
                        <td><?= $user["email"] ?></td>
                        <td><?= $user["idade"] ?></td>
                        <td><?= $user["cidade"] ?></td>
                        <td><?= $user["estado"] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p>Total users: <strong><?= count($users) ?></strong></p>

</body>
</html>