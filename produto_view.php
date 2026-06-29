<?php
require_once 'config.php';
require_once 'layout.php';

$codigo = $_GET['codigo'] ?? '';
$action = $_GET['action'] ?? '';

if (!$codigo) {
    header("Location: produtos.php");
    exit;
}

// Verifica Exclusão
if ($action === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("DELETE FROM p00_produto WHERE p00_codigo = ?");
        $stmt->execute([$codigo]);
        header("Location: produtos.php");
        exit;
    }
}

// Busca Dados do Produto
$stmt = $pdo->prepare("SELECT * FROM p00_produto WHERE p00_codigo = ?");
$stmt->execute([$codigo]);
$produto = $stmt->fetch();

if (!$produto) {
    echo "<div class='text-red-500'>Produto não encontrado.</div>";
    renderFooter();
    exit;
}

// Cálculo do Imposto Virtual
$valorImposto = ($produto['p00_preco'] * $produto['p00_imposto']) / 100;
$precoFinal = $produto['p00_preco'] + $valorImposto;

// Busca Clientes Associados
$stmtCli = $pdo->prepare("
    SELECT c.* FROM c00_cliente c
    INNER JOIN c00_p00_cliente_produto cp ON c.c00_codigo = cp.c00_codigo
    WHERE cp.p00_codigo = ?
");
$stmtCli->execute([$codigo]);
$clientes = $stmtCli->fetchAll();

renderHeader($action === 'delete' ? "Excluir Produto" : "Detalhes do Produto");
?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">
                <?= $action === 'delete' ? '<span class="text-red-600">Confirmação de Exclusão</span>' : 'Detalhes do Produto' ?>
            </h2>
            <a href="produtos.php" class="text-gray-500 hover:text-gray-800"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <p class="text-sm text-gray-500">Código</p>
                <p class="font-medium text-lg"><?= htmlspecialchars($produto['p00_codigo']) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Descrição</p>
                <p class="font-medium text-lg"><?= htmlspecialchars($produto['p00_descricao']) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Preço Base</p>
                <p class="font-medium text-lg text-gray-700">R$ <?= number_format($produto['p00_preco'], 2, ',', '.') ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Imposto (%)</p>
                <p class="font-medium text-lg text-gray-700"><?= number_format($produto['p00_imposto'], 2, ',', '.') ?>%</p>
            </div>
            <div class="col-span-2 bg-gray-50 p-4 rounded-lg border flex justify-between items-center mt-4">
                <div>
                    <p class="text-sm text-gray-500 font-bold">Valor do Imposto (Calculado)</p>
                    <p class="font-bold text-2xl text-red-500">+ R$ <?= number_format($valorImposto, 2, ',', '.') ?></p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500 font-bold">Preço Final</p>
                    <p class="font-bold text-3xl text-emerald-600">R$ <?= number_format($precoFinal, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <?php if ($action === 'delete'): ?>
            <div class="bg-red-50 p-4 rounded border border-red-200">
                <p class="text-red-700 font-bold mb-4">Tem certeza que deseja excluir este produto? Esta ação não pode ser desfeita e removerá todas as associações com clientes.</p>
                <form method="POST">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Sim, Excluir</button>
                    <a href="produtos.php" class="ml-2 text-gray-600 hover:text-gray-900">Cancelar</a>
                </form>
            </div>
        <?php else: ?>
            <div class="flex gap-2 border-t pt-4">
                <a href="produto_form.php?codigo=<?= urlencode($codigo) ?>" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-4 rounded"><i class="fas fa-edit mr-2"></i>Editar</a>
                <a href="produto_view.php?codigo=<?= urlencode($codigo) ?>&action=delete" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"><i class="fas fa-trash mr-2"></i>Excluir</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($action !== 'delete'): ?>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2"><i class="fas fa-users text-primary mr-2"></i>Clientes Associados</h3>
        <?php if (count($clientes) > 0): ?>
            <ul class="divide-y divide-gray-200 border rounded">
                <?php foreach ($clientes as $c): ?>
                    <li class="p-4 flex justify-between items-center hover:bg-gray-50">
                        <div>
                            <span class="font-bold text-gray-700"><?= htmlspecialchars($c['c00_codigo']) ?></span> - <?= htmlspecialchars($c['c00_nome']) ?>
                        </div>
                        <div class="text-gray-500 text-sm">
                            <?= htmlspecialchars($c['c00_estado']) ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500 italic">Nenhum cliente associado a este produto.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php
renderFooter();
?>
