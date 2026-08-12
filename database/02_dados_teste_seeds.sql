-- Inserir usuário Admin de teste para conseguir logar no sistema local
INSERT INTO escolas.usuarios (nome, email, senha_hash, perfil) 
VALUES (
    'Administrador Inicial', 
    'admin@escola.com', 
    '$2y$10$w8uMvA9fS9b.P4e0QZ7lEe8/xS5lF7s/xZ5K3j1X4.Y0W9Z8q7W2m', 
    'ADMIN'
) ON CONFLICT (email) DO NOTHING;