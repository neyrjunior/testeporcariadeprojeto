# UmHelp — Achados e Perdidos

Aplicação PHP para publicar e localizar itens perdidos ou encontrados na comunidade acadêmica. A aplicação usa MySQL e PDO para persistir usuários, publicações e mensagens privadas.

## Requisitos

- PHP 7.4+ com PDO MySQL habilitado.
- MySQL 5.7+ ou MariaDB compatível.
- Apache (por exemplo, XAMPP).

## Instalação local

1. Inicie Apache e MySQL no XAMPP. Importe `database/schema.sql` pelo phpMyAdmin ou, no Prompt de Comando, execute:

   ```sh
   C:\xampp\mysql\bin\mysql.exe -u root < database\schema.sql
   ```

2. Copie `.env.example` para `.env` na raiz (PowerShell: `Copy-Item .env.example .env`) e ajuste `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASSWORD`. O schema cria o banco `umhelp`; se mudar `DB_NAME`, ajuste também o banco referenciado em `database/schema.sql`. O arquivo `.env` não deve ser publicado.
3. Crie os dados de demonstração:

   ```sh
   C:\xampp\php\php.exe database\seed.php
   ```

   O seed cria usuários fictícios, relatos de itens e uma mensagem de exemplo. A senha inicial comum às contas de demonstração é configurada em `UMHELP_DEMO_PASSWORD` no `.env`.
4. Coloque o projeto no diretório público do Apache e abra `http://localhost/<pasta-do-projeto>/index.php`.

O seed pode ser executado novamente sem duplicar os usuários, relatos e mensagem de exemplo. Para ambientes que não sejam locais, altere a senha de demonstração e nunca utilize essas contas.

## Contas de demonstração

- Root: `root@umhelp.local`
- Usuários de exemplo: `marina.costa@example.test`, `rafael.almeida@example.test` e `camila.santos@example.test`
- A senha de todas as contas é a definida por `UMHELP_DEMO_PASSWORD`.

As contas de exemplo usam e-mails `.test` e dados fictícios. Cadastros feitos pela aplicação guardam a senha usando `password_hash`.

## Funcionalidades

- Cadastro e login com senha armazenada em hash.
- Feed e pesquisa de relatos; contagem de itens perdidos e encontrados.
- Perfil público com as publicações da pessoa. Dados cadastrais e documentos são exibidos somente ao próprio titular.
- Mensagens privadas entre usuários, com conversa associada à publicação.
- A conta `root` pode consultar, editar e excluir qualquer publicação. Ela cria publicações pelo formulário normal do feed.
- Proteção CSRF nos formulários que alteram dados.

## Banco de dados

`database/schema.sql` cria as tabelas `usuarios`, `relatos` e `mensagens`, correspondentes às entidades dos models. As chaves estrangeiras removem relatos e mensagens quando a conta relacionada é excluída e preservam mensagens sem referência caso um relato seja removido.

As credenciais e configurações do banco são lidas de variáveis de ambiente ou do `.env`. Se a conexão falhar, confira se o MySQL está ativo, se o schema foi aplicado e se `pdo_mysql` está habilitado no PHP.

## Convenções de arquivos

- Views e assets usam nomes em minúsculas; ex.: `views/cadastro.php` e `assets/img/logo.png`.
- Classes PHP usam `PascalCase`, com o nome do arquivo igual ao da classe; ex.: `models/Usuario.php` e `controllers/HomeController.php`.
- `index.php` é o ponto de entrada e roteia as páginas pela query `action`.
