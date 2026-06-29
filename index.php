<?php
require_once 'config.php';
require_once 'layout.php';

renderHeader("Dashboard - Gerenciamento");
?>

<div class="text-center py-10 bg-white rounded-xl shadow-sm border border-gray-100">
    <h1 class="text-4xl font-extrabold text-gray-800 mb-4">Bem-vindo ao Gerenciador PRO</h1>
    <p class="text-gray-500 mb-8 max-w-2xl mx-auto text-lg">Um sistema simples e moderno para gerenciar o cadastro de clientes, produtos e suas associações.</p>
    
    <div class="flex justify-center gap-6 flex-wrap">
        <a href="clientes.php" class="bg-primary hover:bg-indigo-700 text-white font-bold py-4 px-8 rounded-lg shadow-md transition transform hover:-translate-y-1 flex flex-col items-center gap-2">
            <i class="fas fa-users text-3xl"></i>
            <span>Gerenciar Clientes</span>
        </a>
        <a href="produtos.php" class="bg-secondary hover:bg-emerald-600 text-white font-bold py-4 px-8 rounded-lg shadow-md transition transform hover:-translate-y-1 flex flex-col items-center gap-2">
            <i class="fas fa-box-open text-3xl"></i>
            <span>Gerenciar Produtos</span>
        </a>
        <a href="associacao.php" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-4 px-8 rounded-lg shadow-md transition transform hover:-translate-y-1 flex flex-col items-center gap-2">
            <i class="fas fa-link text-3xl"></i>
            <span>Associar Produtos a Clientes</span>
        </a>
    </div>
</div>

<?php
renderFooter();
?>
