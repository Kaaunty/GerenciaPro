<?php
// layout.php
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
