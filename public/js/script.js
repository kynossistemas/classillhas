// Funções gerais

// OLHO PRA VISUALIZAR A SENHA
function toggleSenha(inputId, btnElement) {
    // Se não passar parâmetros (ex: login simples), busca por padrão
    const inputSenha = inputId ? document.getElementById(inputId) : document.getElementById('senha');
    const btn = btnElement || document.querySelector('.toggle-btn') || document.querySelector('.btn-toggle-pass');
    
    if (!inputSenha) return;

    const eyeOpen = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
    const eyeClosed = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';

    if (inputSenha.type === 'password') {
        inputSenha.type = 'text';
        if (btn) btn.innerHTML = eyeClosed;
    } else {
        inputSenha.type = 'password';
        if (btn) btn.innerHTML = eyeOpen;
    }
}