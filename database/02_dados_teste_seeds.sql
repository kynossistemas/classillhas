-- 1. Inserir Super Admin (Você / Dono do Sistema)
INSERT INTO escolas.usuarios (nome, email, senha_hash, perfil) 
VALUES (
    'Teste Super Admin', 
    'teste@admin.com', 
    '$2y$10$w8uMvA9fS9b.P4e0QZ7lEe8/xS5lF7s/xZ5K3j1X4.Y0W9Z8q7W2m', 
    'SUPER_ADMIN'
) ON CONFLICT (email) DO NOTHING;

-- 2. Inserir Admin de Teste da Escola
INSERT INTO escolas.usuarios (nome, email, senha_hash, perfil) 
VALUES (
    'Administrador da Escola', 
    'escolateste@escola.com', 
    '$2y$10$w8uMvA9fS9b.P4e0QZ7lEe8/xS5lF7s/xZ5K3j1X4.Y0W9Z8q7W2m', 
    'ADMIN_ESCOLA'
) ON CONFLICT (email) DO NOTHING;