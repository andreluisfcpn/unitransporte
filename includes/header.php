<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuração da estrutura do menu e hierarquia de páginas
$menuConfig = [
    'admin' => [
        'title' => 'Administração',
        'icon' => 'fas fa-cog',
        'items' => [
            'dashboard' => [
                'title' => 'Dashboard Admin',
                'url' => '/admin/dashboard.php',
                'icon' => 'fas fa-tachometer-alt'
            ],
            'users' => [
                'title' => 'Usuários',
                'url' => '/admin/usuarios.php',
                'icon' => 'fas fa-users',
                'submenu' => [
                    'cadastrar_usuario' => [
                        'title' => 'Cadastrar Usuário',
                        'url' => '/admin/cadastrar_usuario.php',
                        'icon' => 'fas fa-user-plus'
                    ],
                    'relatorio_usuarios' => [
                        'title' => 'Relatório de Usuários',
                        'url' => '/admin/relatorio_usuarios.php',
                        'icon' => 'fas fa-chart-bar'
                    ]
                ]
            ],
            'drivers' => [
                'title' => 'Motoristas',
                'url' => '/admin/motoristas.php',
                'icon' => 'fas fa-id-card',
                'submenu' => [
                    'cadastrar_motorista' => [
                        'title' => 'Cadastrar Motorista',
                        'url' => '/admin/cadastrar_motorista.php',
                        'icon' => 'fas fa-user-plus'
                    ],
                    'relatorio_motoristas' => [
                        'title' => 'Relatório de Motoristas',
                        'url' => '/admin/relatorio_motoristas.php',
                        'icon' => 'fas fa-chart-bar'
                    ]
                ]
            ],
            'buses' => [
                'title' => 'Ônibus',
                'url' => '/admin/onibus.php',
                'icon' => 'fas fa-bus',
                'submenu' => [
                    'cadastrar_onibus' => [
                        'title' => 'Cadastrar Ônibus',
                        'url' => '/admin/cadastrar_onibus.php',
                        'icon' => 'fas fa-plus-circle'
                    ],
                    'relatorio_onibus' => [
                        'title' => 'Relatório de Ônibus',
                        'url' => '/admin/relatorio_onibus.php',
                        'icon' => 'fas fa-chart-line'
                    ]
                ]
            ]
        ]
    ],
    'motorista' => [
        'title' => 'Motorista',
        'icon' => 'fas fa-id-card',
        'items' => [
            'dashboard' => [
                'title' => 'Dashboard Motorista',
                'url' => '/motorista/dashboard.php',
                'icon' => 'fas fa-tachometer-alt'
            ],
            'validar_qr' => [
                'title' => 'Validar QR Code',
                'url' => '/motorista/validar_qr.php',
                'icon' => 'fas fa-qrcode'
            ],
            'historico_validacoes' => [
                'title' => 'Histórico de Validações',
                'url' => '/motorista/historico_validacoes.php',
                'icon' => 'fas fa-history'
            ]
        ]
    ],
    'usuario' => [
        'title' => 'Usuário',
        'icon' => 'fas fa-user',
        'items' => [
            'dashboard' => [
                'title' => 'Dashboard',
                'url' => '/usuario/dashboard.php',
                'icon' => 'fas fa-tachometer-alt'
            ],
            'perfil' => [
                'title' => 'Meu Perfil',
                'url' => '/usuario/perfil.php',
                'icon' => 'fas fa-user-circle'
            ],
            'gerar_qr' => [
                'title' => 'Gerar QR Code',
                'url' => '/usuario/gerar_qr.php',
                'icon' => 'fas fa-qrcode'
            ],
            'historico_viagens' => [
                'title' => 'Histórico de Viagens',
                'url' => '/usuario/historico_viagens.php',
                'icon' => 'fas fa-history'
            ]
        ]
    ]
];

// Função para determinar a página atual e sua hierarquia
function getCurrentPageInfo() {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $scriptParts = explode('/', $scriptName);
    $currentFile = end($scriptParts);
    $currentDirectory = isset($scriptParts[count($scriptParts) - 2]) ? $scriptParts[count($scriptParts) - 2] : '';
    
    // Remover a extensão .php do arquivo atual
    $currentPage = pathinfo($currentFile, PATHINFO_FILENAME);
    
    return [
        'directory' => $currentDirectory,
        'page' => $currentPage,
        'fullPath' => $scriptName
    ];
}

// Gerar breadcrumb dinamicamente
function generateBreadcrumb($menuConfig) {
    $pageInfo = getCurrentPageInfo();
    $breadcrumb = [];
    $roleDirectory = $pageInfo['directory'];
    $currentPage = $pageInfo['page'];
    
    // Adicionar "Início" como primeiro item
    $breadcrumb[] = [
        'title' => 'Início',
        'url' => '/',
        'active' => false
    ];
    
    // Se estamos em um diretório de perfil (admin, motorista, usuario)
    if (isset($menuConfig[$roleDirectory])) {
        // Adicionar o perfil como segundo nível
        $breadcrumb[] = [
            'title' => $menuConfig[$roleDirectory]['title'],
            'url' => "/{$roleDirectory}/dashboard.php",
            'active' => false
        ];
        
        // Procurar a página atual no menu
        foreach ($menuConfig[$roleDirectory]['items'] as $itemKey => $item) {
            // Verificar se estamos na página principal deste item
            if ($currentPage == $itemKey || (isset($item['url']) && basename($item['url'], '.php') == $currentPage)) {
                $breadcrumb[] = [
                    'title' => $item['title'],
                    'url' => $item['url'],
                    'active' => true
                ];
                break;
            }
            
            // Verificar se estamos em um submenu
            if (isset($item['submenu'])) {
                foreach ($item['submenu'] as $subItemKey => $subItem) {
                    if ($currentPage == $subItemKey || (isset($subItem['url']) && basename($subItem['url'], '.php') == $currentPage)) {
                        // Adicionar o item pai
                        $breadcrumb[] = [
                            'title' => $item['title'],
                            'url' => $item['url'],
                            'active' => false
                        ];
                        
                        // Adicionar o item atual
                        $breadcrumb[] = [
                            'title' => $subItem['title'],
                            'url' => $subItem['url'],
                            'active' => true
                        ];
                        break 2;
                    }
                }
            }
        }
    }
    
    // Se não encontramos a página nos menus configurados, usar o título da página
    if (count($breadcrumb) == 2 && isset($GLOBALS['pageTitle'])) {
        $breadcrumb[] = [
            'title' => $GLOBALS['pageTitle'],
            'url' => $pageInfo['fullPath'],
            'active' => true
        ];
    }
    
    return $breadcrumb;
}

// Renderizar o menu lateral com base na configuração e papel do usuário
function renderSidebar($menuConfig) {
    $html = '';
    $userRole = isset($_SESSION['user']) ? $_SESSION['user']['role'] : '';
    
    if ($userRole && isset($menuConfig[$userRole])) {
        $currentPageInfo = getCurrentPageInfo();
        $currentPage = $currentPageInfo['page'];
        
        foreach ($menuConfig[$userRole]['items'] as $itemKey => $item) {
            $isActive = ($currentPage == $itemKey);
            $activeClass = $isActive ? 'active' : '';
            
            // Se tem submenu
            if (isset($item['submenu']) && count($item['submenu']) > 0) {
                $html .= '<div class="menu-item">';
                $html .= "<a href=\"{$item['url']}\" class=\"menu-link {$activeClass}\">";
                $html .= "<i class=\"{$item['icon']} mr-2\"></i> {$item['title']} <i class=\"fas fa-chevron-down ml-auto\"></i>";
                $html .= '</a>';
                
                // Submenu
                $html .= '<div class="submenu">';
                foreach ($item['submenu'] as $subItemKey => $subItem) {
                    $isSubActive = ($currentPage == $subItemKey);
                    $subActiveClass = $isSubActive ? 'active' : '';
                    $html .= "<a href=\"{$subItem['url']}\" class=\"submenu-link {$subActiveClass}\">";
                    $html .= "<i class=\"{$subItem['icon']} mr-2\"></i> {$subItem['title']}";
                    $html .= '</a>';
                }
                $html .= '</div>';
                $html .= '</div>';
            } else {
                // Item sem submenu
                $html .= "<a href=\"{$item['url']}\" class=\"menu-link {$activeClass}\">";
                $html .= "<i class=\"{$item['icon']} mr-2\"></i> {$item['title']}";
                $html .= '</a>';
            }
        }
    } else {
        // Menu para usuários não logados
        $html .= '<a href="/login.php" class="menu-link"><i class="fas fa-sign-in-alt mr-2"></i> Login</a>';
    }
    
    // Sempre mostrar o link de logout para usuários logados
    if ($userRole) {
        $html .= '<div class="menu-divider"></div>';
        $html .= '<a href="/logout.php" class="menu-link"><i class="fas fa-sign-out-alt mr-2"></i> Sair</a>';
    }
    
    return $html;
}

// Define o link para o dashboard com base na role do usuário
$userDashboardLink = '/login.php'; // padrão se não estiver logado
if (isset($_SESSION['user'])) {
    switch ($_SESSION['user']['role']) {
        case 'admin':
            $userDashboardLink = '/admin/dashboard.php';
            break;
        case 'motorista':
            $userDashboardLink = '/motorista/dashboard.php';
            break;
        case 'usuario':
            $userDashboardLink = '/usuario/dashboard.php';
            break;
        default:
            $userDashboardLink = '/login.php';
    }
}

// Define um título padrão caso não seja definido na página
if (!isset($pageTitle)) {
    $pageTitle = "Bem-vindo";
}

// Gerar breadcrumb para a página atual
$breadcrumbs = generateBreadcrumb($menuConfig);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <link rel="icon" href="/assets/img/favicon.ico" type="image/x-icon">
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($pageTitle); ?> - Sec. Administração</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- Font Awesome para ícones -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <style>
  .modal-dialog-scrollable .modal-body {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
  }

  :root {
    --buzios-azul: #003049; /* Cor inspirada no brasão de Búzios */
    --buzios-azul-claro: #004e7c;
    --buzios-highlight: #005f99;
  }
  body {
    background-color: #f8f9fa;
    color: #343a40;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  .navbar-buzios {
    background-color: var(--buzios-azul);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
  }
  .navbar-buzios .navbar-brand,
  .navbar-buzios .breadcrumb-item a {
    color: #fff !important;
  }
  .navbar-brand img {
    height: 40px;
    margin-right: 10px;
  }
  .breadcrumb {
    background: transparent;
    margin-bottom: 0;
    padding: 0.5rem 0;
  }
  .breadcrumb-item.active {
    color: rgba(255, 255, 255, 0.8);
  }
  .breadcrumb-item + .breadcrumb-item::before {
    color: rgba(255, 255, 255, 0.6);
  }
  /* Sidebar estilos */
  .sidebar {
    position: fixed;
    top: 0;
    left: -280px;
    width: 280px;
    height: 100%;
    background-color: var(--buzios-azul);
    padding: 0;
    box-shadow: 2px 0 5px rgba(0,0,0,0.5);
    transition: left 0.3s ease;
    z-index: 1050;
    display: flex;
    flex-direction: column;
  }
  .sidebar.open {
    left: 0;
  }
  .sidebar-header {
    background-color: var(--buzios-azul-claro);
    padding: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
  }
  .sidebar .profile {
    color: #fff;
    margin-bottom: 0;
  }
  .sidebar-menu {
    padding: 15px 0;
    flex-grow: 1;
    overflow-y: auto;
  }
  .menu-link, .submenu-link {
    color: rgba(255,255,255,0.8);
    display: flex;
    align-items: center;
    padding: 12px 20px;
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
  }
  .menu-link:hover, .submenu-link:hover, 
  .menu-link.active, .submenu-link.active {
    color: #fff;
    background-color: var(--buzios-highlight);
    border-left-color: #fff;
  }
  .menu-link i, .submenu-link i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
  }
  .submenu {
    background-color: rgba(0,0,0,0.1);
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
  }
  .submenu.open {
    max-height: 500px;
  }
  .submenu-link {
    padding-left: 50px;
  }
  .menu-divider {
    height: 1px;
    background: rgba(255,255,255,0.1);
    margin: 10px 20px;
  }
  .hamburger {
    cursor: pointer;
    font-size: 24px;
    color: #fff;
    border: none;
    background: none;
    outline: none;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 4px;
    transition: background-color 0.3s;
  }
  .hamburger:hover {
    background-color: rgba(255,255,255,0.1);
  }
  .sidebar-footer {
    padding: 15px 20px;
    background-color: var(--buzios-azul-claro);
    border-top: 1px solid rgba(255,255,255,0.1);
  }
  /* Overlay para fechar o menu em dispositivos móveis */
  .sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    z-index: 1045;
    display: none;
  }
  .sidebar-overlay.active {
    display: block;
  }
  </style>
</head>
<body>
<nav class="navbar navbar-buzios navbar-expand-lg">
  <div class="container d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
      <!-- Botão hamburger para abrir o menu lateral -->
      <button class="hamburger mr-2" id="toggleSidebar"><i class="fas fa-bars"></i></button>
      <a class="navbar-brand d-flex align-items-center" href="<?php echo $userDashboardLink; ?>">
        <img src="/assets/img/buzios_logo.png" alt="Búzios Logo">
        <span>Sec. Administração</span>
      </a>
    </div>
    <!-- Breadcrumb -->
    <ol class="breadcrumb mb-0">
      <?php foreach ($breadcrumbs as $index => $item): ?>
        <?php if ($item['active']): ?>
          <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($item['title']); ?></li>
        <?php else: ?>
          <li class="breadcrumb-item"><a href="<?php echo $item['url']; ?>"><?php echo htmlspecialchars($item['title']); ?></a></li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ol>
  </div>
</nav>

<!-- Overlay para fechar o sidebar -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar lateral -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="profile">
      <?php if(isset($_SESSION['user'])): ?>
        <h5 class="mb-1"><?php echo htmlspecialchars($_SESSION['user']['nome']); ?></h5>
        <p class="mb-0 small text-light-50"><?php echo htmlspecialchars($_SESSION['user']['email']); ?></p>
      <?php else: ?>
        <h5 class="mb-0">Bem-vindo</h5>
      <?php endif; ?>
    </div>
  </div>
  
  <div class="sidebar-menu">
    <!-- Menu dinâmico baseado no papel do usuário -->
    <?php echo renderSidebar($menuConfig); ?>
  </div>
  
  <?php if(isset($_SESSION['user'])): ?>
  <div class="sidebar-footer">
    <div class="d-flex align-items-center text-white-50 small">
      <i class="fas fa-circle text-success mr-2"></i> 
      <span>Online como <?php echo htmlspecialchars(ucfirst($_SESSION['user']['role'])); ?></span>
    </div>
  </div>
  <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Toggle do sidebar
    document.getElementById('toggleSidebar').addEventListener('click', function(e) {
      e.stopPropagation();
      var sidebar = document.getElementById('sidebar');
      var overlay = document.getElementById('sidebarOverlay');
      sidebar.classList.toggle('open');
      overlay.classList.toggle('active');
    });

    // Fechar sidebar ao clicar no overlay
    document.getElementById('sidebarOverlay').addEventListener('click', function() {
      document.getElementById('sidebar').classList.remove('open');
      this.classList.remove('active');
    });

    // Toggle de submenus
    var menuItems = document.querySelectorAll('.menu-link');
    menuItems.forEach(function(item) {
      var hasSubmenu = item.nextElementSibling && item.nextElementSibling.classList.contains('submenu');
      
      if (hasSubmenu) {
        item.addEventListener('click', function(e) {
          e.preventDefault();
          var submenu = this.nextElementSibling;
          submenu.classList.toggle('open');
          
          // Toggle ícone de seta
          var arrow = this.querySelector('.fa-chevron-down, .fa-chevron-up');
          if (arrow) {
            arrow.classList.toggle('fa-chevron-down');
            arrow.classList.toggle('fa-chevron-up');
          }
        });
      }
    });

    // Abrir submenu ativo automaticamente
    var activeSubmenuLink = document.querySelector('.submenu .submenu-link.active');
    if (activeSubmenuLink) {
      var submenu = activeSubmenuLink.closest('.submenu');
      if (submenu) {
        submenu.classList.add('open');
        var menuLink = submenu.previousElementSibling;
        if (menuLink) {
          menuLink.classList.add('active');
          var arrow = menuLink.querySelector('.fa-chevron-down');
          if (arrow) {
            arrow.classList.remove('fa-chevron-down');
            arrow.classList.add('fa-chevron-up');
          }
        }
      }
    }
  });
</script>

<!-- Início do conteúdo principal -->
<div class="content" id="content">

