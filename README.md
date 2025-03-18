# SENA_ASISTENCIAS - Sistema de Gestión de Asistencias

SENA_ASISTENCIAS es un sistema web para gestionar las asistencias de los aprendices del SENA (Servicio Nacional de Aprendizaje), siguiendo la estructura organizacional de la institución.

## Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache, Nginx)
- Navegador web moderno


## Instalación

1. **Clonar o descargar el repositorio**
2. **Crear la base de datos**


```sql
CREATE DATABASE sena_asistencias;
```

3. **Importar la estructura de la base de datos**


Ejecuta el siguiente script SQL para crear las tablas necesarias:

```sql
-- Crear tabla de usuarios
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'coordinator', 'instructor') NOT NULL
);

-- Crear tabla de regionales
CREATE TABLE regionales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

-- Crear tabla de centros
CREATE TABLE centros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    regional_id INT NOT NULL,
    FOREIGN KEY (regional_id) REFERENCES regionales(id)
);

-- Crear tabla de programas de formación
CREATE TABLE programas_formacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    centro_id INT NOT NULL,
    FOREIGN KEY (centro_id) REFERENCES centros(id)
);

-- Crear tabla de ambientes
CREATE TABLE ambientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    centro_id INT NOT NULL,
    FOREIGN KEY (centro_id) REFERENCES centros(id)
);

-- Crear tabla de fichas
CREATE TABLE fichas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    programa_id INT NOT NULL,
    FOREIGN KEY (programa_id) REFERENCES programas_formacion(id)
);

-- Crear tabla de relación instructores-programas
CREATE TABLE instructores_programas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instructor_id INT NOT NULL,
    programa_id INT NOT NULL,
    FOREIGN KEY (instructor_id) REFERENCES users(id),
    FOREIGN KEY (programa_id) REFERENCES programas_formacion(id)
);

-- Crear tabla de aprendices
CREATE TABLE aprendices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    ficha_id INT NOT NULL,
    FOREIGN KEY (ficha_id) REFERENCES fichas(id)
);

-- Crear tabla de asistencias
CREATE TABLE asistencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    aprendiz_id INT NOT NULL,
    asistio BOOLEAN NOT NULL DEFAULT 1,
    FOREIGN KEY (aprendiz_id) REFERENCES aprendices(id)
);

-- Crear tabla de logs de seguridad
CREATE TABLE security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    username VARCHAR(50),
    action VARCHAR(255) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    timestamp DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Crear usuario super_admin por defecto
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');
-- La contraseña es 'password'
```

4. **Configurar la conexión a la base de datos**


Edita el archivo `includes/Database.php` con tus credenciales:

```php
private function __construct()
{
    $this->connection = new mysqli(
        'localhost',    // Host
        'root',         // Usuario
        '',             // Contraseña
        'sena_asistencias' // Nombre de la base de datos
    );

    if ($this->connection->connect_error) {
        die("Error de conexión: " . $this->connection->connect_error);
    }

    $this->connection->set_charset("utf8mb4");
}
```

5. **Configurar el servidor web**


Coloca los archivos en la carpeta de tu servidor web (por ejemplo, `htdocs` en XAMPP).

## Acceso al sistema

- **URL**: [http://localhost/sena_asistencias/](http://localhost/sena_asistencias/)
- **Usuario**: admin
- **Contraseña**: password


## Estructura del proyecto

- **assets/**: Archivos estáticos (JS)
- **includes/**: Clases PHP (Auth, User, Coordinator, Instructor, etc.)
- **bd/**: Archivos de base de datos
- **Archivos PHP**: Páginas y funcionalidades del sistema


## Roles y funcionalidades

### Super Administrador

- Crear regionales
- Crear centros
- Crear coordinadores


### Coordinador

- Crear programas de formación
- Crear ambientes
- Crear fichas
- Crear instructores


### Instructor

- Tomar lista de asistencia
- Registrar aprendices
- Ver reportes de asistencias


## Flujo de trabajo

1. El Super Administrador crea regionales y centros
2. El Super Administrador crea coordinadores
3. Los Coordinadores crean programas, ambientes, fichas e instructores
4. Los Instructores registran aprendices y toman asistencia
5. Los Instructores consultan reportes de inasistencias


## Tecnologías utilizadas

- PHP (POO)
- MySQL
- Tailwind CSS
- Patrón Singleton para conexión a base de datos
