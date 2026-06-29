<?php
require_once 'config.php';
require_once 'layout.php';

$codigo = $_GET['codigo'] ?? '';
$cliente = null;

if ($codigo) {
    $stmt = $pdo->prepare("SELECT * FROM c00_cliente WHERE c00_codigo = ?");
    $stmt->execute([$codigo]);
    $cliente = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_post = $_POST['c00_codigo'];
    $nome = $_POST['c00_nome'];
    $pessoa = $_POST['c00_pessoa'];
    $estado = $_POST['c00_estado'];
    // Tratamento de data: DatePicker retorna AAAA-MM-DD, precisamos gravar AAAAMMDD
    $data_nascimento = str_replace('-', '', $_POST['c00_data_nascimento']);
    
    // Tratamento do CPF/CNPJ Virtual -> c00_cnpj
    $cnpj = '';
    if ($pessoa === 'F') {
        $cnpj = $_POST['cpf'];
    } elseif ($pessoa === 'J') {
        $cnpj = $_POST['cnpj'];
    } else {
        $cnpj = $_POST['cnpj_outros']; // Pode ser vazio
    }
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

    try {
        if ($cliente) {
            // Update
            $stmt = $pdo->prepare("UPDATE c00_cliente SET c00_nome=?, c00_pessoa=?, c00_cnpj=?, c00_estado=?, c00_data_nascimento=? WHERE c00_codigo=?");
            $stmt->execute([$nome, $pessoa, $cnpj, $estado, $data_nascimento, $codigo_post]);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO c00_cliente (c00_codigo, c00_nome, c00_pessoa, c00_cnpj, c00_estado, c00_data_nascimento) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$codigo_post, $nome, $pessoa, $cnpj, $estado, $data_nascimento]);
        }
        header("Location: clientes.php");
        exit;
    } catch (\PDOException $e) {
        $erro = "Erro ao salvar: " . $e->getMessage();
    }
}

// Convert AAAAMMDD back to YYYY-MM-DD for the HTML5 date picker
$data_formatada = '';
if ($cliente && strlen($cliente['c00_data_nascimento']) == 8) {
    $d = $cliente['c00_data_nascimento'];
    $data_formatada = substr($d, 0, 4) . '-' . substr($d, 4, 2) . '-' . substr($d, 6, 2);
}

renderHeader($cliente ? "Alterar Cliente" : "Novo Cliente");
?>

<div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6"><?= $cliente ? 'Alterar Cliente' : 'Novo Cliente' ?></h2>
    
    <?php if (isset($erro)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="POST" action="" class="space-y-4" id="clienteForm">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Código (Máx 6)</label>
                <input type="text" name="c00_codigo" value="<?= $cliente ? htmlspecialchars($cliente['c00_codigo']) : '' ?>" <?= $cliente ? 'readonly' : 'required' ?> maxlength="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border <?= $cliente ? 'bg-gray-100' : '' ?>">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Estado (Sigla 2)</label>
                <input type="text" name="c00_estado" value="<?= $cliente ? htmlspecialchars($cliente['c00_estado']) : '' ?>" required maxlength="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border uppercase">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Nome</label>
            <input type="text" name="c00_nome" value="<?= $cliente ? htmlspecialchars($cliente['c00_nome']) : '' ?>" required maxlength="60" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Data de Nascimento</label>
            <input type="date" name="c00_data_nascimento" value="<?= $data_formatada ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Tipo de Pessoa</label>
            <select name="c00_pessoa" id="c00_pessoa" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border">
                <option value="">Selecione...</option>
                <option value="F" <?= ($cliente && $cliente['c00_pessoa'] == 'F') ? 'selected' : '' ?>>Física</option>
                <option value="J" <?= ($cliente && $cliente['c00_pessoa'] == 'J') ? 'selected' : '' ?>>Jurídica</option>
                <option value="O" <?= ($cliente && $cliente['c00_pessoa'] == 'O') ? 'selected' : '' ?>>Outros</option>
            </select>
        </div>

        <div id="div_cpf" class="hidden">
            <label class="block text-sm font-medium text-gray-700">CPF (Campo Virtual)</label>
            <input type="text" name="cpf" id="cpf" value="<?= ($cliente && $cliente['c00_pessoa'] == 'F') ? htmlspecialchars(formatCnpjCpf($cliente['c00_cnpj'], 'F')) : '' ?>" maxlength="14" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border" placeholder="Apenas números">
        </div>

        <div id="div_cnpj" class="hidden">
            <label class="block text-sm font-medium text-gray-700">CNPJ</label>
            <input type="text" name="cnpj" id="cnpj" value="<?= ($cliente && $cliente['c00_pessoa'] == 'J') ? htmlspecialchars(formatCnpjCpf($cliente['c00_cnpj'], 'J')) : '' ?>" maxlength="18" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border" placeholder="Apenas números">
        </div>

        <div id="div_outros" class="hidden">
            <label class="block text-sm font-medium text-gray-700">Documento (Opcional)</label>
            <input type="text" name="cnpj_outros" id="cnpj_outros" value="<?= ($cliente && $cliente['c00_pessoa'] == 'O') ? htmlspecialchars($cliente['c00_cnpj']) : '' ?>" maxlength="14" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border">
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <a href="clientes.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Cancelar</a>
            <button type="submit" class="bg-primary hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Salvar</button>
        </div>
    </form>
</div>

<script>
    const pessoaSelect = document.getElementById('c00_pessoa');
    const divCpf = document.getElementById('div_cpf');
    const divCnpj = document.getElementById('div_cnpj');
    const divOutros = document.getElementById('div_outros');
    const cpfInput = document.getElementById('cpf');
    const cnpjInput = document.getElementById('cnpj');

    function maskCPF(value) {
        return value
            .replace(/\D/g, '')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d{1,2})/, '$1-$2')
            .replace(/(-\d{2})\d+?$/, '$1');
    }

    function maskCNPJ(value) {
        return value
            .replace(/\D/g, '')
            .replace(/(\d{2})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1/$2')
            .replace(/(\d{4})(\d{1,2})/, '$1-$2')
            .replace(/(-\d{2})\d+?$/, '$1');
    }

    cpfInput.addEventListener('input', (e) => {
        e.target.value = maskCPF(e.target.value);
    });

    cnpjInput.addEventListener('input', (e) => {
        e.target.value = maskCNPJ(e.target.value);
    });

    function updateFields() {
        divCpf.classList.add('hidden');
        divCnpj.classList.add('hidden');
        divOutros.classList.add('hidden');
        cpfInput.required = false;
        cnpjInput.required = false;

        if (pessoaSelect.value === 'F') {
            divCpf.classList.remove('hidden');
            cpfInput.required = true;
        } else if (pessoaSelect.value === 'J') {
            divCnpj.classList.remove('hidden');
            cnpjInput.required = true;
        } else if (pessoaSelect.value === 'O') {
            divOutros.classList.remove('hidden');
        }
    }

    pessoaSelect.addEventListener('change', updateFields);
    
    // Initial call
    updateFields();
</script>

<?php
renderFooter();
?>
