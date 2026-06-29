<?php
require_once 'config.php';
require_once 'layout.php';

// Handle Add Association
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $c00_codigo = $_POST['cliente'];
    $p00_codigo = $_POST['produto'];

    if ($c00_codigo && $p00_codigo) {
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO c00_p00_cliente_produto (c00_codigo, p00_codigo) VALUES (?, ?)");
            $stmt->execute([$c00_codigo, $p00_codigo]);
            $msg = "Associação criada com sucesso!";
        } catch (\PDOException $e) {
            $erro = "Erro ao associar: " . $e->getMessage();
        }
    }
}

// Handle Remove Association
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    $c00_codigo = $_POST['c00_codigo'];
    $p00_codigo = $_POST['p00_codigo'];

    try {
        $stmt = $pdo->prepare("DELETE FROM c00_p00_cliente_produto WHERE c00_codigo = ? AND p00_codigo = ?");
        $stmt->execute([$c00_codigo, $p00_codigo]);
        $msg = "Associação removida com sucesso!";
    } catch (\PDOException $e) {
        $erro = "Erro ao remover associação: " . $e->getMessage();
    }
}

// Fetch Clients
$stmtCli = $pdo->query("SELECT c00_codigo, c00_nome FROM c00_cliente ORDER BY c00_nome");
$clientes = $stmtCli->fetchAll();

// Fetch Products
$stmtProd = $pdo->query("SELECT p00_codigo, p00_descricao FROM p00_produto ORDER BY p00_descricao");
$produtos = $stmtProd->fetchAll();

// Fetch All Associations
$stmtAssoc = $pdo->query("
    SELECT c.c00_codigo, c.c00_nome, p.p00_codigo, p.p00_descricao
    FROM c00_p00_cliente_produto cp
    INNER JOIN c00_cliente c ON cp.c00_codigo = c.c00_codigo
    INNER JOIN p00_produto p ON cp.p00_codigo = p.p00_codigo
    ORDER BY c.c00_nome, p.p00_descricao
");
$associacoes = $stmtAssoc->fetchAll();

renderHeader("Associação Cliente x Produto");
?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6"><i class="fas fa-link text-orange-500 mr-2"></i>Nova Associação</h2>
        
        <?php if (isset($msg)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if (isset($erro)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="flex items-end gap-4">
            <input type="hidden" name="action" value="add">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700">Cliente</label>
                <select name="cliente" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border">
                    <option value="">Selecione um cliente...</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?= htmlspecialchars($c['c00_codigo']) ?>"><?= htmlspecialchars($c['c00_nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700">Produto</label>
                <select name="produto" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border">
                    <option value="">Selecione um produto...</option>
                    <?php foreach ($produtos as $p): ?>
                        <option value="<?= htmlspecialchars($p['p00_codigo']) ?>"><?= htmlspecialchars($p['p00_descricao']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded shadow transition">Associar</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (count($associacoes) > 0): ?>
                    <?php foreach ($associacoes as $a): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                <?= htmlspecialchars($a['c00_codigo']) ?> - <?= htmlspecialchars($a['c00_nome']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= htmlspecialchars($a['p00_codigo']) ?> - <?= htmlspecialchars($a['p00_descricao']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <form method="POST" action="" onsubmit="return confirm('Deseja realmente remover esta associação?');">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="c00_codigo" value="<?= htmlspecialchars($a['c00_codigo']) ?>">
                                    <input type="hidden" name="p00_codigo" value="<?= htmlspecialchars($a['p00_codigo']) ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Remover Associação"><i class="fas fa-unlink"></i> Remover</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">Nenhuma associação encontrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
renderFooter();
?>
