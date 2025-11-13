// script.js - Sistema de Login Completo
document.addEventListener('DOMContentLoaded', function() {
    initializeAllForms();
    mostrarInfoFusoHorario();
    initDashboard();
    
    // Atualizar informações de tempo a cada minuto
    setInterval(mostrarInfoFusoHorario, 60000);
});

// Função para inicializar todos os formulários
function initializeAllForms() {
    // Login Form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleLogin();
        });
    }
    
    // Register Form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleRegister();
        });
    }
    
    // Change Password Form
    const changePasswordForm = document.getElementById('changePasswordForm');
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleChangePassword();
        });
    }
    
    // Recovery Form
    const recoveryForm = document.getElementById('recoveryForm');
    if (recoveryForm) {
        recoveryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleRecovery();
        });
    }
    
    // Reset Password Form
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    if (resetPasswordForm) {
        resetPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleResetPassword();
        });
    }
}

// Inicialização específica do dashboard
function initDashboard() {
    if (document.body.classList.contains('dashboard-body')) {
        initModalEvents();
        updateMainClock();
        setInterval(updateMainClock, 1000);
        showTimezoneInfo();
        
        // Atualizar também o tempo no header
        setInterval(updateCurrentTime, 1000);
        updateCurrentTime();
    }
}

// Funções do Modal (para dashboard)
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Fechar modal ao clicar fora (para dashboard)
function initModalEvents() {
    window.onclick = function(event) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    }
    
    // Adicionar evento de tecla ESC para fechar modais
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (modal.style.display === 'block') {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        }
    });
}

// Atualizar relógio central (para dashboard)
function updateMainClock() {
    const mainDate = document.getElementById('mainDate');
    const mainTime = document.getElementById('mainTime');
    
    if (mainDate && mainTime) {
        const now = new Date();
        
        // Formatar data
        const dateOptions = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        const formattedDate = now.toLocaleDateString('pt-BR', dateOptions);
        mainDate.textContent = formattedDate.charAt(0).toUpperCase() + formattedDate.slice(1);
        
        // Formatar hora
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        mainTime.textContent = `${hours}:${minutes}:${seconds}`;
    }
}

// Atualizar hora do header
function updateCurrentTime() {
    const currentTimeElement = document.getElementById('currentTime');
    if (currentTimeElement) {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        currentTimeElement.textContent = `${hours}:${minutes}:${seconds}`;
    }
}

// Mostrar informações de fuso horário
function showTimezoneInfo() {
    const timezoneInfo = document.getElementById('mainTimezone');
    if (timezoneInfo) {
        try {
            const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            const offset = new Date().getTimezoneOffset();
            const offsetHours = Math.abs(Math.floor(offset / 60));
            const offsetMinutes = Math.abs(offset % 60);
            const offsetString = `${offset < 0 ? '+' : '-'}${offsetHours.toString().padStart(2, '0')}:${offsetMinutes.toString().padStart(2, '0')}`;
            
            timezoneInfo.textContent = `${timezone} (UTC${offsetString})`;
        } catch (error) {
            console.error('Erro ao obter informações do fuso horário:', error);
            timezoneInfo.textContent = 'America/Sao_Paulo (UTC-03:00)';
        }
    }
}

// Handler para Login
function handleLogin() {
    const form = document.getElementById('loginForm');
    const formData = new FormData(form);
    const messageDiv = document.getElementById('message');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Desabilitar botão durante a requisição
    setLoadingState(submitBtn, true);
    
    fetch('login_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na rede');
        }
        return response.text();
    })
    .then(data => {
        if (data.startsWith('success')) {
            showMessage(messageDiv, 'Login realizado com sucesso!', 'success');
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 1000);
        } else {
            const errorMsg = data.replace('error: ', '');
            showMessage(messageDiv, errorMsg, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage(messageDiv, 'Erro ao realizar login. Tente novamente.', 'error');
    })
    .finally(() => {
        setLoadingState(submitBtn, false);
    });
}

// Handler para Registro
function handleRegister() {
    const form = document.getElementById('registerForm');
    const formData = new FormData(form);
    const messageDiv = document.getElementById('message');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Verificar se as senhas coincidem
    const senha = form.querySelector('#senha').value;
    const confirmarSenha = form.querySelector('#confirmar_senha').value;
    
    if (senha !== confirmarSenha) {
        showMessage(messageDiv, 'As senhas não coincidem.', 'error');
        return;
    }
    
    if (senha.length < 6) {
        showMessage(messageDiv, 'A senha deve ter pelo menos 6 caracteres.', 'error');
        return;
    }
    
    // Desabilitar botão durante a requisição
    setLoadingState(submitBtn, true);
    
    fetch('register_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na rede');
        }
        return response.text();
    })
    .then(data => {
        if (data.startsWith('success')) {
            showMessage(messageDiv, 'Conta criada com sucesso! Redirecionando...', 'success');
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 2000);
        } else {
            const errorMsg = data.replace('error: ', '');
            showMessage(messageDiv, errorMsg, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage(messageDiv, 'Erro ao criar conta. Tente novamente.', 'error');
    })
    .finally(() => {
        setLoadingState(submitBtn, false);
    });
}

// Handler para Alteração de Senha
function handleChangePassword() {
    const form = document.getElementById('changePasswordForm');
    const formData = new FormData(form);
    const messageDiv = document.getElementById('passwordMessage');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Verificar se as novas senhas coincidem
    const novaSenha = form.querySelector('#nova_senha').value;
    const confirmarNovaSenha = form.querySelector('#confirmar_nova_senha').value;
    
    if (novaSenha !== confirmarNovaSenha) {
        showMessage(messageDiv, 'As novas senhas não coincidem.', 'error');
        return;
    }
    
    if (novaSenha.length < 6) {
        showMessage(messageDiv, 'A nova senha deve ter pelo menos 6 caracteres.', 'error');
        return;
    }
    
    // Desabilitar botão durante a requisição
    setLoadingState(submitBtn, true);
    
    fetch('change_password_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na rede');
        }
        return response.text();
    })
    .then(data => {
        if (data.startsWith('success')) {
            showMessage(messageDiv, 'Senha alterada com sucesso!', 'success');
            form.reset();
        } else {
            const errorMsg = data.replace('error: ', '');
            showMessage(messageDiv, errorMsg, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage(messageDiv, 'Erro ao alterar senha. Tente novamente.', 'error');
    })
    .finally(() => {
        setLoadingState(submitBtn, false);
    });
}

// Handler para Recuperação de Senha
function handleRecovery() {
    const form = document.getElementById('recoveryForm');
    const formData = new FormData(form);
    const messageDiv = document.getElementById('message');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Desabilitar botão durante a requisição
    setLoadingState(submitBtn, true);
    
    fetch('recovery_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na rede');
        }
        return response.text();
    })
    .then(data => {
        if (data.startsWith('success')) {
            const successMsg = data.replace('success: ', '');
            showMessage(messageDiv, successMsg, 'success');
        } else {
            const errorMsg = data.replace('error: ', '');
            showMessage(messageDiv, errorMsg, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage(messageDiv, 'Erro ao processar solicitação. Tente novamente.', 'error');
    })
    .finally(() => {
        setLoadingState(submitBtn, false);
    });
}

// Handler para Redefinição de Senha
function handleResetPassword() {
    const form = document.getElementById('resetPasswordForm');
    const formData = new FormData(form);
    const messageDiv = document.getElementById('message');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Verificar se as senhas coincidem
    const novaSenha = form.querySelector('#nova_senha').value;
    const confirmarSenha = form.querySelector('#confirmar_senha').value;
    
    if (novaSenha !== confirmarSenha) {
        showMessage(messageDiv, 'As senhas não coincidem.', 'error');
        return;
    }
    
    if (novaSenha.length < 6) {
        showMessage(messageDiv, 'A senha deve ter pelo menos 6 caracteres.', 'error');
        return;
    }
    
    // Desabilitar botão durante a requisição
    setLoadingState(submitBtn, true);
    
    fetch('reset_password_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na rede');
        }
        return response.text();
    })
    .then(data => {
        if (data.startsWith('success')) {
            showMessage(messageDiv, 'Senha redefinida com sucesso! Redirecionando...', 'success');
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 2000);
        } else {
            const errorMsg = data.replace('error: ', '');
            showMessage(messageDiv, errorMsg, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage(messageDiv, 'Erro ao redefinir senha. Tente novamente.', 'error');
    })
    .finally(() => {
        setLoadingState(submitBtn, false);
    });
}

// Função auxiliar para mostrar mensagens
function showMessage(element, message, type) {
    if (!element) return;
    
    element.textContent = message;
    element.className = 'message ' + type;
    element.style.display = 'block';
    
    // Auto-esconder mensagens de sucesso após 5 segundos
    if (type === 'success') {
        setTimeout(() => {
            element.style.display = 'none';
        }, 5000);
    }
    
    // Scroll para a mensagem
    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// Loading states para melhor UX
function setLoadingState(button, isLoading) {
    if (!button) return;
    
    if (isLoading) {
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.textContent = 'Carregando...';
        button.style.opacity = '0.7';
    } else {
        button.disabled = false;
        button.textContent = button.dataset.originalText || button.textContent;
        button.style.opacity = '1';
    }
}

// Mostrar informações de fuso horário (para páginas não-dashboard)
function mostrarInfoFusoHorario() {
    const timezoneInfo = document.getElementById('timezoneInfo');
    if (timezoneInfo) {
        try {
            const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            const offset = new Date().getTimezoneOffset();
            const offsetHours = Math.abs(Math.floor(offset / 60));
            const offsetMinutes = Math.abs(offset % 60);
            const offsetString = `${offset < 0 ? '+' : '-'}${offsetHours.toString().padStart(2, '0')}:${offsetMinutes.toString().padStart(2, '0')}`;
            
            timezoneInfo.textContent = `${timezone} (UTC${offsetString})`;
        } catch (error) {
            console.error('Erro ao obter informações do fuso horário:', error);
            if (timezoneInfo) {
                timezoneInfo.textContent = 'America/Sao_Paulo (UTC-03:00)';
            }
        }
    }
}

// Validação em tempo real para formulários
document.addEventListener('DOMContentLoaded', function() {
    // Validação de e-mail em tempo real
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateEmail(this);
        });
    });
    
    // Validação de senha em tempo real
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', function() {
            validatePassword(this);
        });
    });
    
    // Confirmação de senha em tempo real
    const confirmPasswordInputs = document.querySelectorAll('input[name="confirmar_senha"], input[name="confirmar_nova_senha"]');
    confirmPasswordInputs.forEach(input => {
        input.addEventListener('input', function() {
            validatePasswordConfirmation(this);
        });
    });
});

function validateEmail(input) {
    const email = input.value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email && !emailRegex.test(email)) {
        showInputError(input, 'Por favor, insira um e-mail válido.');
        return false;
    } else {
        clearInputError(input);
        return true;
    }
}

function validatePassword(input) {
    const password = input.value;
    
    if (password && password.length < 6) {
        showInputError(input, 'A senha deve ter pelo menos 6 caracteres.');
        return false;
    } else {
        clearInputError(input);
        return true;
    }
}

function validatePasswordConfirmation(input) {
    const form = input.closest('form');
    let passwordField, confirmField;
    
    if (input.name === 'confirmar_senha') {
        passwordField = form.querySelector('#senha');
        confirmField = form.querySelector('#confirmar_senha');
    } else if (input.name === 'confirmar_nova_senha') {
        passwordField = form.querySelector('#nova_senha');
        confirmField = form.querySelector('#confirmar_nova_senha');
    } else if (input.name === 'confirmar_senha') {
        passwordField = form.querySelector('[name="senha"]');
        confirmField = form.querySelector('[name="confirmar_senha"]');
    }
    
    if (passwordField && confirmField && passwordField.value !== confirmField.value) {
        showInputError(confirmField, 'As senhas não coincidem.');
        return false;
    } else {
        clearInputError(confirmField);
        return true;
    }
}

function showInputError(input, message) {
    clearInputError(input);
    
    input.style.borderColor = '#dc3545';
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'input-error';
    errorDiv.style.color = '#dc3545';
    errorDiv.style.fontSize = '12px';
    errorDiv.style.marginTop = '5px';
    errorDiv.textContent = message;
    
    input.parentNode.appendChild(errorDiv);
}

function clearInputError(input) {
    if (!input) return;
    
    input.style.borderColor = '';
    
    const existingError = input.parentNode.querySelector('.input-error');
    if (existingError) {
        existingError.remove();
    }
}

// Prevenir múltiplos envios de formulário
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && submitBtn.disabled) {
                e.preventDefault();
                return false;
            }
        });
    });
});

// Melhorar UX para mobile
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar classes para melhor responsividade
    if (window.innerWidth < 768) {
        document.body.classList.add('mobile-view');
    }
    
    window.addEventListener('resize', function() {
        if (window.innerWidth < 768) {
            document.body.classList.add('mobile-view');
        } else {
            document.body.classList.remove('mobile-view');
        }
    });
});

// Função para formatar datas no JavaScript (consistente com PHP)
function formatarDataJS(dataString) {
    if (!dataString) return 'N/A';
    
    try {
        const data = new Date(dataString);
        return data.toLocaleString('pt-BR', {
            timeZone: 'America/Sao_Paulo',
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        console.error('Erro ao formatar data:', e);
        return 'Data inválida';
    }
}

// Tratamento de erros global
window.addEventListener('error', function(e) {
    console.error('Erro global:', e.error);
});

// Exportar funções para uso global (se necessário)
window.openModal = openModal;
window.closeModal = closeModal;
window.handleLogin = handleLogin;
window.handleRegister = handleRegister;
window.handleChangePassword = handleChangePassword;
window.handleRecovery = handleRecovery;
window.handleResetPassword = handleResetPassword;