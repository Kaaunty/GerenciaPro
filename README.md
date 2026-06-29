# ProLink - Sistema de Gerenciamento de Clientes e Produtos

O **ProLink** é um sistema web desenvolvido em PHP para gerenciar o cadastro de clientes, catálogo de produtos e estabelecer a associação entre ambos (relação de muitos-para-muitos). 

Este projeto foi construído focando na simplicidade de execução, robustez no tratamento dos dados e um visual prático e moderno utilizando **Tailwind CSS**.

---

## 🚀 Tecnologias Utilizadas

- **PHP 7.4+** (Sem dependências externas de pacotes)
- **MySQL / MariaDB** (Via PDO do PHP)
- **Tailwind CSS** (Via CDN para estilização ágil e responsiva)
- **FontAwesome** (Ícones visuais)
- **JavaScript Nativo** (Para aplicação de máscaras dinâmicas e regras de validação nos formulários)

---

## 📋 Requisitos e Funcionalidades Atendidas

### 📦 Produtos
- **Listagem de Produtos**: Exibe todos os produtos cadastrados.
- **CRUD Completo**: Possibilidade de Incluir, Visualizar, Alterar e Excluir.
- **Campo Virtual "Valor do Imposto"**: Calculado dinamicamente com base no preço e no percentual de imposto informado.
- **Associações**: Na tela de visualização do produto, é exibida a lista de todos os clientes associados a ele.

### 👤 Clientes
- **Listagem de Clientes**: Exibe todos os clientes cadastrados.
- **CRUD Completo**: Possibilidade de Incluir, Visualizar, Alterar e Excluir.
- **DatePicker**: Campo "Data de Nascimento" integrado ao calendário nativo do navegador.
- **Validação Dinâmica por Tipo de Pessoa**:
  - **Física (F)**: Obriga o preenchimento do campo virtual **CPF** (que é formatado e gravado no campo `c00_cnpj` do banco).
  - **Jurídica (J)**: Obriga o preenchimento do campo **CNPJ**.
  - **Outros (O)**: Torna os campos de CPF e CNPJ opcionais.
- **Associações**: Na tela de visualização do cliente, é exibida a lista de todos os produtos associados a ele.

### 🔗 Associações
- **Tela de Associação**: Permite associar e remover o vínculo entre clientes e produtos em lote de forma simplificada.

---

## 🛠️ Configuração e Execução

### 1. Pré-requisitos
Certifique-se de ter um ambiente PHP com MySQL instalado na sua máquina (por exemplo, utilizando o **XAMPP**, **WampServer** ou **Laragon**).

### 2. Configuração do Banco de Dados
A aplicação possui um **auto-instalador**. Você só precisa garantir que o serviço de banco de dados (MySQL) esteja ativo.
- O arquivo de configuração de banco de dados é o `config.php`.
- Por padrão, ele está configurado para acessar o banco `gerenciamento_db` em `localhost` (IP `127.0.0.1`), usando o usuário `root` e sem senha (padrão do XAMPP).
- Ao acessar o sistema pela primeira vez, as tabelas e o banco de dados serão criados automaticamente.

### 3. Como Rodar a Aplicação

#### Opção A (Via Servidor Embutido do PHP - Recomendado para Testes)
Se você possui o PHP configurado no seu terminal, navegue até a pasta do projeto e execute:
```bash
php -S localhost:8000
```
Depois, abra o navegador e acesse: `http://localhost:8000`

#### Opção B (Colando na pasta do Servidor Local)
1. Copie a pasta deste projeto.
2. Cole-a dentro do diretório público do seu servidor web (ex: `C:\xampp\htdocs\prolink`).
3. Com o Apache e o MySQL ativos no XAMPP, acesse no navegador: `http://localhost/prolink`

---

## 🗄️ Estrutura do Banco de Dados

O sistema cria as tabelas com a seguinte estrutura física:

```sql
CREATE TABLE c00_cliente (
    c00_codigo character(6) NOT NULL,
    c00_nome character varying(60) NOT NULL,
    c00_pessoa character varying(1) NOT NULL,  
    c00_cnpj character varying(14),  
    c00_estado character(2) NOT NULL,
    c00_data_nascimento character(8) NOT NULL,
    PRIMARY KEY (c00_codigo)
);

CREATE TABLE p00_produto (
    p00_codigo character(15) NOT NULL,
    p00_descricao character varying(45) NOT NULL,
    p00_preco numeric(10,2) NOT NULL,
    p00_imposto numeric(10,2) NOT NULL,
    PRIMARY KEY (p00_codigo)
);

CREATE TABLE c00_p00_cliente_produto (
    c00_codigo character(6) NOT NULL,
    p00_codigo character(15) NOT NULL,
    PRIMARY KEY (c00_codigo, p00_codigo),
    FOREIGN KEY (c00_codigo) REFERENCES c00_cliente(c00_codigo) ON DELETE CASCADE,
    FOREIGN KEY (p00_codigo) REFERENCES p00_produto(p00_codigo) ON DELETE CASCADE
);
```
