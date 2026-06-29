<?php
require_once 'config.php';
require_once 'layout.php';

$stmt = $pdo->query("SELECT * FROM p00_produto ORDER BY p00_descricao");
$produtos = $stmt->fetchAll();

renderHeader("Listagem de Produtos");
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold text-gray-800"><i class="fas fa-box-open text-primary mr-2"></i>Produtos</h2>
    <a href="produto_form.php" class="bg-primary hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow transition"><i class="fas fa-plus mr-2"></i>Novo Produto</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Imposto (%)</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (count($produtos) > 0): ?>
                <?php foreach ($produtos as $p): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($p['p00_codigo']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($p['p00_descricao']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">R$ <?= number_format($p['p00_preco'], 2, ',', '.') ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500"><?= number_format($p['p00_imposto'], 2, ',', '.') ?>%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="produto_view.php?codigo=<?= urlencode($p['p00_codigo']) ?>" class="text-blue-600 hover:text-blue-900" title="Visualizar"><i class="fas fa-eye"></i></a>
                            <a href="produto_form.php?codigo=<?= urlencode($p['p00_codigo']) ?>" class="text-amber-500 hover:text-amber-700" title="Editar"><i class="fas fa-edit"></i></a>
                            <a href="produto_view.php?codigo=<?= urlencode($p['p00_codigo']) ?>&action=delete" class="text-red-600 hover:text-red-900" title="Excluir"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">Nenhum produto cadastrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
renderFooter();
?>
