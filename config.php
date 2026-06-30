<?php
// config.php
$host = '127.0.0.1';
$db   = 'gerenciamento_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Primeiro conecta sem banco de dados para cria-lo se não existir
try {
    $pdo_init = new PDO("mysql:host=$host;charset=$charset", $user, $pass);
    $pdo_init->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_init->exec("CREATE DATABASE IF NOT EXISTS `$db`");
} catch (\PDOException $e) {
    die("Erro na conexão inicial: " . $e->getMessage());
}

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Criação das Tabelas
// Verifica se a tabela c00_cliente existe e se a coluna c00_codigo é do tipo esperado (int)
$recreate = false;
try {
    $q = $pdo->query("DESCRIBE c00_cliente");
    $cols = $q->fetchAll();
    foreach ($cols as $col) {
        if ($col['Field'] === 'c00_codigo' && strpos(strtolower($col['Type']), 'int') === false) {
            $recreate = true;
            break;
        }
    }
} catch (\PDOException $e) {
    // Se a tabela não existe, será criada abaixo
}

if ($recreate) {
    $pdo->exec("DROP TABLE IF EXISTS c00_p00_cliente_produto");
    $pdo->exec("DROP TABLE IF EXISTS c00_cliente");
}

$sql = "
CREATE TABLE IF NOT EXISTS c00_cliente (
    c00_codigo INT AUTO_INCREMENT NOT NULL,
    c00_nome character varying(60) NOT NULL,
    c00_pessoa character varying(1) NOT NULL,  
    c00_cnpj character varying(14),  
    c00_estado character(2) NOT NULL,
    c00_data_nascimento character(8) NOT NULL,
    PRIMARY KEY (c00_codigo)
);

CREATE TABLE IF NOT EXISTS p00_produto (
    p00_codigo character(15) NOT NULL,
    p00_descricao character varying(45) NOT NULL,
    p00_preco numeric(10,2) NOT NULL,
    p00_imposto numeric(10,2) NOT NULL,
    PRIMARY KEY (p00_codigo)
);

CREATE TABLE IF NOT EXISTS c00_p00_cliente_produto (
    c00_codigo INT NOT NULL,
    p00_codigo character(15) NOT NULL,
    PRIMARY KEY (c00_codigo, p00_codigo),
    FOREIGN KEY (c00_codigo) REFERENCES c00_cliente(c00_codigo) ON DELETE CASCADE,
    FOREIGN KEY (p00_codigo) REFERENCES p00_produto(p00_codigo) ON DELETE CASCADE
);
";

$pdo->exec($sql);
?>

