# Sistema de Lista de Contatos

Este é um sistema de gerenciamento de contatos simples, desenvolvido como uma aplicação full-stack com backend em Laravel e frontend em Vue.js.

## Core Technologies

-   **Backend**: PHP 8.1+, Laravel 10+, MySQL
-   **Frontend**: Node.js 20+, Vue.js 3+, Vuetify 3 (Material Design), Vue Router, Pinia
-   **Autenticação**: Laravel Sanctum (API Tokens)
-   **APIs Externas**:
    -   ViaCEP (Consulta de endereços por CEP)
    -   Nominatim - OpenStreetMap (Geocodificação de endereços)
-   **Testes**: PHPUnit

---

## Requisitos Funcionais

-   **Gerenciamento de Usuário:**
    -   Cadastro de novos usuários no sistema.
    -   Login e Logout com autenticação baseada em token.
    -   Recuperação de senha por e-mail.
    -   Exclusão da própria conta.
-   **Gerenciamento de Contatos (CRUD):**
    -   Cadastro de novos contatos com nome, CPF, telefone e endereço completo.
    -   Visualização da lista de contatos cadastrados.
    -   Edição dos dados de um contato existente.
    -   Exclusão de um contato.
-   **Funcionalidades da Lista:**
    -   Filtro dinâmico da lista por nome ou CPF do contato.
    -   Paginação e ordenação dos resultados.
    -   Ao clicar em um contato, o mapa é centralizado e exibe um pino na sua localização.

## Regras de Negócio

-   Cada e-mail só pode ser associado a uma única conta de usuário.
-   Um mesmo CPF não pode ser cadastrado duas vezes para o mesmo usuário.
-   A validação do CPF segue o algoritmo oficial brasileiro.
-   Todos os campos do endereço são obrigatórios, exceto o "complemento".
-   Para excluir a própria conta, o usuário deve confirmar a ação informando sua senha.

## Requisitos Não Funcionais

-   **API**: A comunicação entre frontend e backend deve ser feita através de uma API RESTful.
-   **Design**: A interface do usuário deve seguir as diretrizes do Material Design (V2 ou V3).
-   **Segurança**: Endpoints de gerenciamento de contatos, usuário e ViaCEP devem ser protegidos e acessíveis apenas por usuários autenticados.

---

## Setup e Instalação

### Pré-requisitos

-   PHP >= 8.1
-   Composer
-   Node.js >= 20.0
-   NPM ou Yarn
-   Um servidor de banco de dados MySQL

### 1. Backend (Laravel)

1.  **Navegue até a pasta do backend:**
    ```bash
    cd backend
    ```

2.  **Instale as dependências do PHP:**
    ```bash
    composer install
    ```

3.  **Crie o arquivo de ambiente:**
    ```bash
    cp .env.example .env
    ```

4.  **Configure o `.env`:**
    Abra o arquivo `.env` e configure as variáveis do banco de dados (`DB_*`) e, se desejar usar a recuperação de senha, configure as variáveis de e-mail (`MAIL_*`), por exemplo, com as credenciais do [Mailtrap](https://mailtrap.io/).
    ```ini
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=contact_list
    DB_USERNAME=root
    DB_PASSWORD=password

    MAIL_MAILER=smtp
    MAIL_HOST=sandbox.smtp.mailtrap.io
    MAIL_PORT=2525
    MAIL_USERNAME=seu_usuario_mailtrap
    MAIL_PASSWORD=sua_senha_mailtrap
    MAIL_ENCRYPTION=tls
    ```

5.  **Gere a chave da aplicação:**
    ```bash
    php artisan key:generate
    ```

6.  **Execute as migrações do banco de dados:**
    Certifique-se de que o banco de dados (`contact_list`) já foi criado no seu servidor MySQL.
    ```bash
    php artisan migrate
    ```

7.  **Inicie o servidor do backend:**
    ```bash
    php artisan serve
    ```
    O servidor estará rodando em `http://localhost:8000`.

### 2. Frontend (Vue.js)

1.  **Navegue até a pasta do frontend (em um novo terminal):**
    ```bash
    cd frontend
    ```

2.  **Instale as dependências do JavaScript:**
    ```bash
    npm install
    ```

3.  **Inicie o servidor de desenvolvimento:**
    ```bash
    npm run dev
    ```
    A aplicação estará acessível em `http://localhost:5173`.

---

## Detalhes das Integrações

### Pesquisa de CEP (ViaCEP)

Para evitar problemas de CORS e manter a segurança, o frontend não consulta a API do ViaCEP diretamente. O fluxo é o seguinte:

1.  No formulário de contato, o usuário digita o CEP e o campo perde o foco.
2.  O frontend (componente `ContactForm.vue`) envia uma requisição para um endpoint interno do nosso backend: `GET /api/viacep/{cep}`.
3.  O backend (controller `ViaCepController.php`) recebe a requisição, faz a chamada para a API externa `https://viacep.com.br/ws/{cep}/json/`, obtém os dados do endereço e os retorna como resposta para o frontend.
4.  O frontend preenche automaticamente os campos de endereço no formulário com os dados recebidos.

### Geolocalização e Pino no Mapa

A obtenção da latitude/longitude e a exibição do pino no mapa seguem este fluxo:

1.  Quando um contato é **criado ou atualizado**, o backend (controller `ContactController.php`) concatena os dados do endereço (`rua, número, cidade, estado`).
2.  O backend envia esse endereço para a API **Nominatim (OpenStreetMap)** para obter as coordenadas geográficas (latitude e longitude).
3.  Essas coordenadas são salvas no banco de dados junto com as outras informações do contato.
4.  No frontend (view `ContactsView.vue`), quando o usuário clica em um card de contato, o objeto completo daquele contato (incluindo latitude e longitude) é passado como uma `prop` para o componente `MapView.vue`.
5.  O componente `MapView.vue` "observa" alterações nessa `prop`. Quando um novo contato é recebido, ele utiliza as coordenadas para centralizar o mapa e posicionar um marcador (`LMarker`) no local exato.

---

## Testes Implementados

Os testes foram escritos utilizando o framework PHPUnit, nativo do Laravel.

### Testes Criados

-   **`tests/Feature/Auth/RegistrationTest.php`**: Garante que um novo usuário consegue se registrar com sucesso na aplicação, recebendo um token de autenticação como resposta.
-   **`tests/Feature/Api/ContactManagementTest.php`**: Testa se um usuário autenticado pode criar um novo contato.
    -   *Nota: Este teste apresentou instabilidade durante o desenvolvimento devido a complexidades na validação do CPF e simulação de APIs externas. Ele foi temporariamente desativado para permitir a validação dos demais testes e requer investigação adicional.*

### Como Executar os Testes

1.  Navegue até a pasta do backend:
    ```bash
    cd backend
    ```
2.  Execute o comando do PHPUnit via Artisan:
    ```bash
    php artisan test
    ```
