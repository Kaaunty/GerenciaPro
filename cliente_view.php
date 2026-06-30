<?php
require_once 'config.php';
require_once 'layout.php';

$codigo = $_GET['codigo'] ?? '';
$action = $_GET['action'] ?? '';

if (!$codigo) {
    header("Location: clientes.php");
    exit;
}

// Verifica Exclusão
if ($action === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("DELETE FROM c00_cliente WHERE c00_codigo = ?");
        $stmt->execute([$codigo]);
        setFlashMessage("Cliente excluído com sucesso!", "success");
        header("Location: clientes.php");
        exit;
    }
}

// Busca Dados do Cliente
$stmt = $pdo->prepare("SELECT * FROM c00_cliente WHERE c00_codigo = ?");
$stmt->execute([$codigo]);
$cliente = $stmt->fetch();

if (!$cliente) {
    echo "<div class='text-red-500'>Cliente não encontrado.</div>";
    renderFooter();
    exit;
}

// Busca Produtos Associados
$stmtProd = $pdo->prepare("
    SELECT p.* FROM p00_produto p
    INNER JOIN c00_p00_cliente_produto cp ON p.p00_codigo = cp.p00_codigo
    WHERE cp.c00_codigo = ?
");
$stmtProd->execute([$codigo]);
$produtos = $stmtProd->fetchAll();

renderHeader($action === 'delete' ? "Excluir Cliente" : "Detalhes do Cliente");
?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">
                <?= $action === 'delete' ? '<span class="text-red-600">Confirmação de Exclusão</span>' : 'Detalhes do Cliente' ?>
            </h2>
            <a href="clientes.php" class="text-gray-500 hover:text-gray-800"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <p class="text-sm text-gray-500">Código</p>
                <p class="font-medium text-lg"><?= htmlspecialchars(formatCodigoCliente($cliente['c00_codigo'])) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Nome</p>
                <p class="font-medium text-lg"><?= htmlspecialchars($cliente['c00_nome']) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Tipo de Pessoa</p>
                <p class="font-medium text-lg">
                    <?php 
                        if ($cliente['c00_pessoa'] == 'F') echo 'Física';
                        elseif ($cliente['c00_pessoa'] == 'J') echo 'Jurídica';
                        else echo 'Outros';
                    ?>
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-500"><?= $cliente['c00_pessoa'] == 'F' ? 'CPF' : 'CNPJ / Documento' ?></p>
                <p class="font-medium text-lg"><?= htmlspecialchars(formatCnpjCpf($cliente['c00_cnpj'], $cliente['c00_pessoa'])) ?: '-' ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Data de Nascimento</p>
                <p class="font-medium text-lg">
                    <?php 
                        $d = $cliente['c00_data_nascimento'];
                        if(strlen($d) == 8) echo substr($d, 6, 2).'/'.substr($d, 4, 2).'/'.substr($d, 0, 4);
                        else echo $d;
                    ?>
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Estado</p>
                <p class="font-medium text-lg"><?= htmlspecialchars($cliente['c00_estado']) ?></p>
            </div>
        </div>

        <?php if ($action === 'delete'): ?>
            <div class="bg-red-50 p-4 rounded border border-red-200">
                <p class="text-red-700 font-bold mb-4">Tem certeza que deseja excluir este cliente? Esta ação não pode ser desfeita e removerá todas as associações com produtos.</p>
                <form method="POST">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Sim, Excluir</button>
                    <a href="clientes.php" class="ml-2 text-gray-600 hover:text-gray-900">Cancelar</a>
                </form>
            </div>
        <?php else: ?>
            <div class="flex gap-2 border-t pt-4">
                <a href="cliente_form.php?codigo=<?= urlencode($codigo) ?>" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-4 rounded"><i class="fas fa-edit mr-2"></i>Editar</a>
                <a href="cliente_view.php?codigo=<?= urlencode($codigo) ?>&action=delete" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"><i class="fas fa-trash mr-2"></i>Excluir</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($action !== 'delete'): ?>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2"><i class="fas fa-box-open text-primary mr-2"></i>Produtos Associados</h3>
        <?php if (count($produtos) > 0): ?>
            <ul class="divide-y divide-gray-200 border rounded">
                <?php foreach ($produtos as $p): ?>
                    <li class="p-4 flex justify-between items-center hover:bg-gray-50">
                        <div>
                            <span class="font-bold text-gray-700"><?= htmlspecialchars($p['p00_codigo']) ?></span> - <?= htmlspecialchars($p['p00_descricao']) ?>
                        </div>
                        <div class="text-gray-500">
                            R$ <?= number_format($p['p00_preco'], 2, ',', '.') ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500 italic">Nenhum produto associado a este cliente.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php
renderFooter();
?>
