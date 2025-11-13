<?php
include 'config.php';
include 'security.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Buscar informações do usuário
$query = "SELECT nome, email, data_criacao FROM usuarios WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(":id", $_SESSION['usuario_id']);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar últimos logins
$query = "SELECT data_login, ip_address FROM logs_login WHERE usuario_id = :id ORDER BY data_login DESC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->bindParam(":id", $_SESSION['usuario_id']);
$stmt->execute();
$logs_login = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Bem-vindo, <?php echo htmlspecialchars($usuario['nome']); ?>!</h1>
            <div class="user-menu">
                <span><?php echo htmlspecialchars($usuario['email']); ?></span>
                <!-- <span class="current-time" id="currentTime"></span> -->
                <a href="logout.php" class="btn btn-logout">Sair</a>
            </div>
        </header>
        
        <!-- Relógio Central -->
        <div class="center-clock">
            <div class="main-date" id="mainDate"></div>
            <div class="main-time" id="mainTime"></div>
            <div class="timezone-info" id="mainTimezone"></div>
        </div>
        
        <!-- Menu Flutuante -->
        <div class="floating-menu">
            <button class="menu-btn" onclick="openModal('infoModal')">Informações da Conta</button>
            <button class="menu-btn" onclick="openModal('passwordModal')">Alterar Senha</button>
            <button class="menu-btn" onclick="openModal('historyModal')">Últimos Acessos</button>
        </div>
        
        <!-- Modal Informações da Conta -->
        <div id="infoModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Informações da Conta</h3>
                    <button class="close-btn" onclick="closeModal('infoModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="info-item">
                        <div class="info-label">Nome Completo</div>
                        <div><?php echo htmlspecialchars($usuario['nome']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">E-mail</div>
                        <div><?php echo htmlspecialchars($usuario['email']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Membro Desde</div>
                        <div><?php echo formatarDataHora($usuario['data_criacao'], 'd/m/Y \à\s H:i'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ID do Usuário</div>
                        <div><?php echo $_SESSION['usuario_id']; ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Alterar Senha -->
        <div id="passwordModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Alterar Senha</h3>
                    <button class="close-btn" onclick="closeModal('passwordModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="changePasswordForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="input-group">
                            <label for="senha_atual">Senha Atual</label>
                            <input type="password" id="senha_atual" name="senha_atual" required>
                        </div>
                        
                        <div class="input-group">
                            <label for="nova_senha">Nova Senha</label>
                            <input type="password" id="nova_senha" name="nova_senha" required minlength="6">
                        </div>
                        
                        <div class="input-group">
                            <label for="confirmar_nova_senha">Confirmar Nova Senha</label>
                            <input type="password" id="confirmar_nova_senha" name="confirmar_nova_senha" required>
                        </div>
                        
                        <button type="submit" class="btn">Alterar Senha</button>
                    </form>
                    <div id="passwordMessage" class="message" style="margin-top: 15px;"></div>
                </div>
            </div>
        </div>
        
        <!-- Modal Histórico de Acessos -->
        <div id="historyModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Últimos Acessos</h3>
                    <button class="close-btn" onclick="closeModal('historyModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <?php if (count($logs_login) > 0): ?>
                        <ul class="login-history">
                            <?php foreach ($logs_login as $log): ?>
                                <li>
                                    <span class="login-time">
                                        <?php echo formatarDataHora($log['data_login'], 'd/m/Y H:i'); ?>
                                    </span>
                                    <span class="login-ip">
                                        IP: <?php echo htmlspecialchars($log['ip_address']); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p style="text-align: center; color: var(--text-secondary);">
                            Nenhum registro de login encontrado.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Funções do Modal
    function openModal(modalId) {
        document.getElementById(modalId).style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // Fechar modal ao clicar fora
    window.onclick = function(event) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    }
    
    // Atualizar relógio central
    function updateMainClock() {
        const now = new Date();
        const optionsDate = {
            timeZone: 'America/Sao_Paulo',
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        
        const optionsTime = {
            timeZone: 'America/Sao_Paulo',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        };
        
        const dateFormatter = new Intl.DateTimeFormat('pt-BR', optionsDate);
        const timeFormatter = new Intl.DateTimeFormat('pt-BR', optionsTime);
        
        document.getElementById('mainDate').textContent = 
            dateFormatter.format(now).replace(/^\w/, c => c.toUpperCase());
        document.getElementById('mainTime').textContent = timeFormatter.format(now);
        
        // Atualizar também o tempo no header
        updateCurrentTime();
    }
    
    // Atualizar hora do header
    function updateCurrentTime() {
        const now = new Date();
        const options = {
            timeZone: 'America/Sao_Paulo',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        };
        
        const formatter = new Intl.DateTimeFormat('pt-BR', options);
        const currentTimeElement = document.getElementById('currentTime');
        if (currentTimeElement) {
            currentTimeElement.textContent = formatter.format(now);
        }
    }
    
    // Mostrar informações do fuso horário
    function showTimezoneInfo() {
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        const offset = new Date().getTimezoneOffset();
        const offsetHours = Math.abs(Math.floor(offset / 60));
        const offsetMinutes = Math.abs(offset % 60);
        const offsetString = `${offset < 0 ? '+' : '-'}${offsetHours.toString().padStart(2, '0')}:${offsetMinutes.toString().padStart(2, '0')}`;
        
        document.getElementById('mainTimezone').textContent = 
            `${timezone} (UTC${offsetString})`;
    }
    
    // Inicializar
    updateMainClock();
    showTimezoneInfo();
    setInterval(updateMainClock, 1000);
    </script>
    
    <script src="script.js"></script>
</body>
</html>