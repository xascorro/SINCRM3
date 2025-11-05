# SINCRM3 🏊‍♀️

**SINCRM3** es un sistema de gestión web integral para competiciones de natación artística (sincronizada). Ha sido diseñado para facilitar y digitalizar cada etapa de un evento, desde la inscripción de atletas hasta la publicación de resultados.

---

## ✨ Características Principales

*   **🏆 Gestión de Competiciones**: Crea y administra competiciones completas. Define fases (eliminatorias, finales), tipos de evento (solo, dúo, equipo), y gestiona rutinas y figuras.
*   **👥 Gestión de Entidades**: Centraliza la información de:
    *   **Nadadoras**: Perfiles, historial y estadísticas.
    *   **Clubes**: Información de contacto y listado de atletas.
    *   **Jueces**: Asignación a paneles y gestión de acreditaciones.
    *   **Federaciones**: Administración a nivel regional o nacional.
*   **✍️ Sistema de Inscripciones**: Un portal para que los clubes inscriban a sus nadadoras en las diferentes pruebas de la competición de forma sencilla.
*   **🧮 Puntuación en Vivo**: Interfaz digital para que los jueces introduzcan sus puntuaciones en tiempo real, eliminando la necesidad de papel y agilizando el cálculo de resultados.
*   **🔐 Gestión de Usuarios y Roles**: Sistema de autenticación seguro con roles definidos (Administrador, Juez, Entrenador) para un control de acceso granular.
*   **📄 Generación de Informes**: Descarga informes oficiales en formato **PDF** con un solo clic. Incluye:
    *   Listas de salida (`Orden de Salida`).
    *   Resultados finales detallados.
    *   Resúmenes por club o nadadora.
*   **📱 Compatible con PWA (Progressive Web App)**: Gracias a su *manifest* y *service worker*, la aplicación puede ser "instalada" en la pantalla de inicio de dispositivos móviles y tablets para un acceso rápido y una experiencia similar a una app nativa.

---

## 🛠️ Tecnologías Utilizadas

| Tecnología | Propósito |
| :--- | :--- |
| **PHP** | Lógica del backend y procesamiento de datos. |
| **HTML5** | Estructura y semántica del contenido web. |
| **CSS3 / SCSS** | Estilos, diseño y maquetación visual. |
| **JavaScript** | Interactividad del frontend y comunicación asíncrona. |
| **MySQL/MariaDB** | Almacenamiento y gestión de la base de datos relacional. |
| **TCPDF** | Librería para la generación de documentos PDF. |
| **PHPMailer** | Envío de correos electrónicos (notificaciones, recuperación de contraseña, etc.). |

---

## 🚀 Puesta en Marcha

Para desplegar esta aplicación, necesitarás un entorno de servidor web clásico (como LAMP, WAMP o MAMP).

### 1. Prerrequisitos

*   Servidor web (Apache, Nginx).
*   PHP (versión 7.4 o superior recomendada).
*   Servidor de base de datos MySQL o MariaDB.

### 2. Instalación

1.  **Clonar el Repositorio**:
    ```bash
    git clone https://github.com/xascorro/SINCRM3.git
    ```
    O descarga el archivo ZIP y descomprímelo en el directorio raíz de tu servidor (ej. `/var/www/html` o `htdocs`).

2.  **Base de Datos**:
    *   Crea una nueva base de datos (ej. `sincrm3_db`) desde tu gestor de base de datos (como phpMyAdmin).
    *   Importa el archivo `.sql` que se encuentra en el directorio `/database` para crear la estructura de tablas y (si existen) los datos iniciales.

3.  **Configuración**:
    *   Busca el archivo de configuración de la base de datos (probablemente en `includes/db_config.php` o similar).
    *   Modifica los parámetros de conexión con tus credenciales:
      ```php
      define('DB_HOST', 'localhost');
      define('DB_USER', 'tu_usuario');
      define('DB_PASS', 'tu_contraseña');
      define('DB_NAME', 'sincrm3_db');
      ```

4.  **Acceder a la Aplicación**:
    *   Abre tu navegador y visita `http://localhost/SINCRM3` (o la ruta donde lo hayas instalado). ¡Listo!

---
