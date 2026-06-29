<?php
require_once 'config.php';
require_once 'layout.php';

$codigo = $_GET['codigo'] ?? '';
$produto = null;

if ($codigo) {
    $stmt = $pdo->prepare("SELECT * FROM p00_produto WHERE p00_codigo = ?");
    $stmt->execute([$codigo]);
    $produto = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_post = $_POST['p00_codigo'];
    $descricao = $_POST['p00_descricao'];
    $preco = str_replace(',', '.', str_replace('.', '', $_POST['p00_preco'])); // Converte 1.234,56 para 1234.56
    $imposto = str_replace(',', '.', $_POST['p00_imposto']);

    try {
        if ($produto) {
            // Update
            $stmt = $pdo->prepare("UPDATE p00_produto SET p00_descricao=?, p00_preco=?, p00_imposto=? WHERE p00_codigo=?");
            $stmt->execute([$descricao, $preco, $imposto, $codigo_post]);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO p00_produto (p00_codigo, p00_descricao, p00_preco, p00_imposto) VALUES (?, ?, ?, ?)");
            $stmt->execute([$codigo_post, $descricao, $preco, $imposto]);
        }
        header("Location: produtos.php");
        exit;
    } catch (\PDOException $e) {
        $erro = "Erro ao salvar: " . $e->getMessage();
    }
}

renderHeader($produto ? "Alterar Produto" : "Novo Produto");
?>

<div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6"><?= $produto ? 'Alterar Produto' : 'Novo Produto' ?></h2>
    
    <?php if (isset($erro)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="POST" action="" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Código (Máx 15)</label>
            <input type="text" name="p00_codigo" value="<?= $produto ? htmlspecialchars($produto['p00_codigo']) : '' ?>" <?= $produto ? 'readonly' : 'required' ?> maxlength="15" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border <?= $produto ? 'bg-gray-100' : '' ?>">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Descrição</label>
            <input type="text" name="p00_descricao" value="<?= $produto ? htmlspecialchars($produto['p00_descricao']) : '' ?>" required maxlength="45" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Preço (Ex: 1500,00)</label>
                <input type="text" name="p00_preco" value="<?= $produto ? number_format($produto['p00_preco'], 2, ',', '') : '' ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Imposto % (Ex: 10,5)</label>
                <input type="text" name="p00_imposto" value="<?= $produto ? number_format($produto['p00_imposto'], 2, ',', '') : '' ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border">
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <a href="produtos.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Cancelar</a>
            <button type="submit" class="bg-primary hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Salvar</button>
        </div>
    </form>
</div>

<?php
renderFooter();
?>
