<?php
// layout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function formatCodigoCliente($codigo) {
    return str_pad($codigo, 6, '0', STR_PAD_LEFT);
}

function validateCpf($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11) {
        return false;
    }
    // Verifica se foi informada uma sequência de dígitos repetidos
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    // Calcula os dígitos verificadores para saber se o CPF é válido
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
}

function validateCnpj($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    if (strlen($cnpj) != 14) {
        return false;
    }
    // Verifica se todos os dígitos são iguais
    if (preg_match('/(\d)\1{13}/', $cnpj)) {
        return false;
    }
    // Valida primeiro dígito verificador
    $j = 5;
    $soma1 = 0;
    for ($i = 0; $i < 12; $i++) {
        $soma1 += $cnpj[$i] * $j;
        $j = ($j == 2) ? 9 : $j - 1;
    }
    $resto1 = $soma1 % 11;
    $dg1 = ($resto1 < 2) ? 0 : 11 - $resto1;
    
    // Valida segundo dígito verificador
    $j = 6;
    $soma2 = 0;
    for ($i = 0; $i < 13; $i++) {
        $soma2 += $cnpj[$i] * $j;
        $j = ($j == 2) ? 9 : $j - 1;
    }
    $resto2 = $soma2 % 11;
    $dg2 = ($resto2 < 2) ? 0 : 11 - $resto2;
    
    return ($cnpj[12] == $dg1 && $cnpj[13] == $dg2);
}

function parseBrlNumeric($val) {
    $val = trim($val);
    if (strpos($val, ',') !== false) {
        // Formato brasileiro com vírgula decimal (ex: 1.250,50 ou 1250,50)
        $val = str_replace('.', '', $val); // Remove pontos de milhar
        $val = str_replace(',', '.', $val); // Substitui vírgula por ponto decimal
    }
    return (float)$val;
}

function setFlashMessage($msg, $type = 'success') {
    $_SESSION['flash_msg'] = $msg;
    $_SESSION['flash_type'] = $type;
}

function renderFlashMessage() {
    if (isset($_SESSION['flash_msg'])) {
        $type = $_SESSION['flash_type'] ?? 'success';
        $msg = $_SESSION['flash_msg'];
        unset($_SESSION['flash_msg']);
        unset($_SESSION['flash_type']);
        
        $bgColor = $type === 'success' ? 'bg-emerald-100 border-emerald-400 text-emerald-800' : 'bg-red-100 border-red-400 text-red-800';
        $icon = $type === 'success' ? 'fa-check-circle text-emerald-500' : 'fa-exclamation-circle text-red-500';
        
        echo '<div class="border px-4 py-3 rounded-lg mb-6 flex items-center gap-3 ' . $bgColor . ' shadow-sm transition duration-300">
                <i class="fas ' . $icon . ' text-lg"></i>
                <span class="font-medium">' . htmlspecialchars($msg) . '</span>
              </div>';
    }
}

function renderHeader($title = "Sistema de Gerenciamento") {
    echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . '</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#4f46e5", // Indigo 600
                        secondary: "#10b981", // Emerald 500
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">
    <nav class="bg-primary text-white shadow-lg">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold tracking-wider flex items-center gap-2"><i class="fas fa-boxes"></i> Gerenciador PRO</a>
            <div class="space-x-4 font-semibold">
                <a href="clientes.php" class="hover:text-gray-200 transition">Clientes</a>
                <a href="produtos.php" class="hover:text-gray-200 transition">Produtos</a>
                <a href="associacao.php" class="hover:text-gray-200 transition">Associações</a>
            </div>
        </div>
    </nav>
    <main class="container mx-auto px-6 py-8 flex-grow">';
    renderFlashMessage();
}

function renderFooter() {
    echo '    </main>
    <footer class="bg-gray-800 text-gray-300 text-center py-4 mt-auto shadow-inner">
        <p>&copy; ' . date("Y") . ' Sistema de Gerenciamento. Todos os direitos reservados.</p>
    </footer>
</body>
</html>';
}

function formatCnpjCpf($val, $tipo) {
    $val = preg_replace('/[^0-9]/', '', $val);
    if ($tipo === 'F') {
        if (strlen($val) === 11) {
            return substr($val, 0, 3) . '.' . substr($val, 3, 3) . '.' . substr($val, 6, 3) . '-' . substr($val, 9, 2);
        }
    } elseif ($tipo === 'J') {
        if (strlen($val) === 14) {
            return substr($val, 0, 2) . '.' . substr($val, 2, 3) . '.' . substr($val, 5, 3) . '/' . substr($val, 8, 4) . '-' . substr($val, 12, 2);
        }
    }
    return $val;
}
?>

