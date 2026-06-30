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
    $nome = trim($_POST['c00_nome']);
    $pessoa = $_POST['c00_pessoa'];
    $estado = strtoupper(trim($_POST['c00_estado']));
    // Tratamento de data: DatePicker retorna AAAA-MM-DD, precisamos gravar AAAAMMDD
    $data_nascimento = str_replace('-', '', $_POST['c00_data_nascimento']);
    
    // Tratamento do CPF/CNPJ Virtual -> c00_cnpj
    $cnpj = '';
    if ($pessoa === 'F') {
        $cnpj = $_POST['cpf'] ?? '';
    } elseif ($pessoa === 'J') {
        $cnpj = $_POST['cnpj'] ?? '';
    } else {
        $cnpj = $_POST['cnpj_outros'] ?? ''; // Pode ser vazio
    }
    $cnpj_limpo = preg_replace('/[^0-9]/', '', $cnpj);

    // Validações no Back-end
    if (empty($nome) || strlen($nome) > 60) {
        $erro = "O nome é obrigatório e deve ter no máximo 60 caracteres.";
    } elseif (empty($estado) || strlen($estado) != 2) {
        $erro = "O estado (UF) é obrigatório e deve ter 2 caracteres.";
    } elseif (empty($data_nascimento) || strlen($data_nascimento) != 8) {
        $erro = "A data de nascimento é obrigatória.";
    } elseif ($pessoa === 'F') {
        if (empty($cnpj_limpo)) {
            $erro = "O CPF é obrigatório para Pessoa Física.";
        } elseif (!validateCpf($cnpj_limpo)) {
            $erro = "O CPF informado é inválido.";
        }
    } elseif ($pessoa === 'J') {
        if (empty($cnpj_limpo)) {
            $erro = "O CNPJ é obrigatório para Pessoa Jurídica.";
        } elseif (!validateCnpj($cnpj_limpo)) {
            $erro = "O CNPJ informado é inválido.";
        }
    }

    if (!isset($erro)) {
        try {
            if ($cliente) {
                // Update
                $stmt = $pdo->prepare("UPDATE c00_cliente SET c00_nome=?, c00_pessoa=?, c00_cnpj=?, c00_estado=?, c00_data_nascimento=? WHERE c00_codigo=?");
                $stmt->execute([$nome, $pessoa, $cnpj_limpo, $estado, $data_nascimento, $codigo]);
                setFlashMessage("Cliente atualizado com sucesso!", "success");
            } else {
                // Insert (deixa o banco gerar o c00_codigo com auto_increment)
                $stmt = $pdo->prepare("INSERT INTO c00_cliente (c00_nome, c00_pessoa, c00_cnpj, c00_estado, c00_data_nascimento) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nome, $pessoa, $cnpj_limpo, $estado, $data_nascimento]);
                setFlashMessage("Cliente cadastrado com sucesso!", "success");
            }
            header("Location: clientes.php");
            exit;
        } catch (\PDOException $e) {
            $erro = "Erro ao salvar: " . $e->getMessage();
        }
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
        <div>
            <label class="block text-sm font-medium text-gray-700">Nome</label>
            <input type="text" name="c00_nome" value="<?= $cliente ? htmlspecialchars($cliente['c00_nome']) : '' ?>" required maxlength="60" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <?php if ($cliente): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código</label>
                    <input type="text" readonly value="<?= htmlspecialchars(formatCodigoCliente($cliente['c00_codigo'])) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border bg-gray-100 font-medium">
                </div>
            <?php endif; ?>
            <div class="<?= $cliente ? '' : 'col-span-2' ?>">
                <label class="block text-sm font-medium text-gray-700">Estado</label>
                <select name="c00_estado" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 p-2 border">
                    <option value="">Selecione...</option>
                    <?php
                    $estados = [
                        'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas',
                        'BA' => 'Bahia', 'CE' => 'Ceará', 'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo',
                        'GO' => 'Goiás', 'MA' => 'Maranhão', 'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul',
                        'MG' => 'Minas Gerais', 'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná',
                        'PE' => 'Pernambuco', 'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte',
                        'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina',
                        'SP' => 'São Paulo', 'SE' => 'Sergipe', 'TO' => 'Tocantins'
                    ];
                    foreach ($estados as $sigla => $nome_estado) {
                        $selected = ($cliente && $cliente['c00_estado'] == $sigla) ? 'selected' : '';
                        echo "<option value=\"$sigla\" $selected>$sigla - $nome_estado</option>";
                    }
                    ?>
                </select>
            </div>
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

    // Frontend Validations on Submit
    document.getElementById('clienteForm').addEventListener('submit', (e) => {
        const pVal = pessoaSelect.value;
        if (pVal === 'F') {
            const cpfVal = cpfInput.value.replace(/\D/g, '');
            if (!validarCPF_JS(cpfVal)) {
                alert('O CPF informado é inválido!');
                e.preventDefault();
                cpfInput.focus();
            }
        } else if (pVal === 'J') {
            const cnpjVal = cnpjInput.value.replace(/\D/g, '');
            if (!validarCNPJ_JS(cnpjVal)) {
                alert('O CNPJ informado é inválido!');
                e.preventDefault();
                cnpjInput.focus();
            }
        }
    });

    function validarCPF_JS(cpf) {
        if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
        let soma = 0, resto;
        for (let i = 1; i <= 9; i++) soma += parseInt(cpf.substring(i-1, i)) * (11 - i);
        resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.substring(9, 10))) return false;
        soma = 0;
        for (let i = 1; i <= 10; i++) soma += parseInt(cpf.substring(i-1, i)) * (12 - i);
        resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.substring(10, 11))) return false;
        return true;
    }

    function validarCNPJ_JS(cnpj) {
        if (cnpj.length !== 14 || /^(\d)\1{13}$/.test(cnpj)) return false;
        let tamanho = cnpj.length - 2;
        let numeros = cnpj.substring(0, tamanho);
        let digitos = cnpj.substring(tamanho);
        let soma = 0;
        let pos = tamanho - 7;
        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }
        let resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
        if (resultado !== parseInt(digitos.charAt(0))) return false;
        tamanho = tamanho + 1;
        numeros = cnpj.substring(0, tamanho);
        soma = 0;
        pos = tamanho - 7;
        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }
        resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
        if (resultado !== parseInt(digitos.charAt(1))) return false;
        return true;
    }
</script>

<?php
renderFooter();
?>
