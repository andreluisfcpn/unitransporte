# UniTransporte

> Sistema web para gestão e validação de transporte universitário — administração, motoristas e usuários (carteiras com QR, validações em viagem, relatórios e impressão de carteiras).

Resumo
- Multi-usuário: `admin`, `motorista`, `usuario`.
- Gera e valida QR Codes para embarque; registra viagens (`trips`) e status (`autorizado` / `recusado`).
- Painéis separados para administração, motoristas e usuários.
- APIs internas em `admin/api/` para CRUD e relatórios (JSON).
- Front-end simples com Bootstrap e Font Awesome; backend em PHP com PDO/MySQL.

Destaques
- Fluxo completo: cadastro de usuários/motoristas/ônibus, geração de carteiras e QR, validação pelo motorista, relatórios por ônibus e motorista.
- Uploads de fotos em `assets/uploads/`.
- Uso de prepared statements (PDO) para consultas; algumas áreas ainda podem melhorar em segurança (veja seção Segurança).

Estrutura do projeto (visão geral)
- `config.php` — configurações da aplicação (conexão PDO, timezone).
- `index.php`, `login.php`, `logout.php` — entradas e autenticação.
- `includes/` — `auth.php`, `header.php`, `footer.php` (menu dinâmico, breadcrumbs, helpers).
- `admin/` — painel do administrador e subpáginas (cadastro, relatórios, impressão de carteiras).
- `admin/api/` — endpoints JSON (ex.: `get_users.php`, `save_user.php`, `delete_user.php`, `get_buses.php`, `get_bus_passenger_counts.php`, etc.).
- `motorista/` — funcionalidades de motorista (`validar_qr.php`, `confirmar_viagem.php`, histórico).
- `usuario/` — carteira, geração de QR, histórico e perfil.
- `assets/` — imagens e `uploads/`.

Requisitos
- PHP (recomendo >= 7.4 ou 8.x)
- MySQL / MariaDB
- Extensões PHP: PDO, pdo_mysql, mbstring, fileinfo
- Servidor local: XAMPP, WAMP, Laragon ou similar

Instalação rápida (ambiente local)
1. Copie o projeto para a pasta do servidor web (ex: `htdocs/unitransporte`).
2. Crie um banco de dados MySQL e configure as tabelas (importar SQL se houver schema fornecido).
3. Atualize as constantes em `config.php`: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
4. Garanta permissão de escrita em `assets/uploads/` para uploads de fotos.
5. Acesse `http://localhost/<seu_path>/` e faça login (ou `http://localhost/<seu_path>/login.php`).

Observações de configuração
- Em `config.php` você pode ajustar `date_default_timezone_set` se necessário.
- Sessões são usadas para autenticação (`includes/auth.php`).
- Uploads de imagem aceitos em `save_user.php`: campos `fotoFile` (jpg, jpeg, png).

API (endpoints úteis em `admin/api/`)
- `get_users.php` — GET: retorna JSON com usuários (`schedule` agregado e caminho da foto se existir).
- `save_user.php` — POST (form-data): criar usuário; espera `action=create`, campos (`nome`, `email`, `senha`, etc.) e `fotoFile` opcional.
- `delete_user.php` — POST/JSON: exclui usuário e dados relacionados (trips, logs, schedule) via `id`.
- `get_buses.php` — GET: lista ônibus.
- `get_bus_passenger_counts.php` — GET: contagem de passageiros por ônibus/data; aceita `date` comme parâmetro. Requer role `admin`.

Autenticação e permissões
- A API e páginas sensíveis usam `includes/auth.php` e checam `$_SESSION['user']['role']`.
- Endpoints críticos verificam `isLoggedIn()` e `getUserRole()` (ex.: `get_bus_passenger_counts.php` restringe a `admin`).
- Autenticação é baseada em sessão PHP — requests via ferramentas (curl, Postman) precisam reutilizar cookie de sessão.

Como contribuir
- Abra uma issue descrevendo a mudança proposta.
- Faça uma branch clara (`feature/descricao` ou `bugfix/descricao`).
- Crie PR com descrição, mudanças e testes (se aplicável).

Contato
[Búzios Digital](https://buzios.digital)

- _Project Owner_ - [André Luis Castro](https://github.com/andreluisfcpn)
- _Mid-Level FullStack Developer_ - [Mike Santos](https://github.com/mfonsanBD)

Licença
- Não há arquivo `LICENSE` no projeto.