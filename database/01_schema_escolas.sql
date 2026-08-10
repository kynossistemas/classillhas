-- Criação do Schema Isolado
CREATE SCHEMA IF NOT EXISTS escolas;

-- 1. Usuários do Sistema
CREATE TABLE escolas.usuarios (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    perfil VARCHAR(20) NOT NULL CHECK (perfil IN ('ADMIN', 'PROFESSOR', 'VISUALIZADOR')),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Cadastro de Salas / Espaços
CREATE TABLE escolas.salas (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    capacidade INT DEFAULT 40,
    tipo VARCHAR(30) DEFAULT 'SALA_AULA'
);

-- 3. Cadastro de Turmas
CREATE TABLE escolas.turmas (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    turno VARCHAR(10) NOT NULL CHECK (turno IN ('MANHA', 'TARDE', 'NOITE'))
);

-- 4. Cadastro de Disciplinas
CREATE TABLE escolas.materias (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(50) NOT NULL
);

-- 5. Cadastro de Professores
CREATE TABLE escolas.professores (
    id SERIAL PRIMARY KEY,
    usuario_id INT REFERENCES escolas.usuarios(id) ON DELETE CASCADE,
    nome VARCHAR(100) NOT NULL,
    carga_horaria_semanal INT NOT NULL DEFAULT 20
);

-- 6. Tabela de Ligação: Matérias que o Professor Leciona
CREATE TABLE escolas.professor_materias (
    professor_id INT REFERENCES escolas.professores(id) ON DELETE CASCADE,
    materia_id INT REFERENCES escolas.materias(id) ON DELETE CASCADE,
    PRIMARY KEY (professor_id, materia_id)
);

-- 7. Matriz de Disponibilidade do Professor
CREATE TABLE escolas.disponibilidades (
    id SERIAL PRIMARY KEY,
    professor_id INT REFERENCES escolas.professores(id) ON DELETE CASCADE,
    dia_semana INT NOT NULL CHECK (dia_semana BETWEEN 1 AND 7),
    turno VARCHAR(10) NOT NULL CHECK (turno IN ('MANHA', 'TARDE', 'NOITE')),
    disponivel BOOLEAN DEFAULT TRUE
);

-- 8. Tabela Mestra: O Cronograma Gerado
CREATE TABLE escolas.cronograma (
    id SERIAL PRIMARY KEY,
    turma_id INT REFERENCES escolas.turmas(id) ON DELETE CASCADE,
    sala_id INT REFERENCES escolas.salas(id) ON DELETE CASCADE,
    professor_id INT REFERENCES escolas.professores(id) ON DELETE CASCADE,
    materia_id INT REFERENCES escolas.materias(id) ON DELETE CASCADE,
    dia_semana INT NOT NULL CHECK (dia_semana BETWEEN 1 AND 7),
    horario_inicio TIME NOT NULL,
    horario_fim TIME NOT NULL,
    data_inicio_validade DATE,
    data_fim_validade DATE
);