# GEL - Gestor de Estadías en Línea UTMIR

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![Google Drive API](https://img.shields.io/badge/Google_Drive_API-4285F4?style=for-the-badge&logo=google-drive&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

Plataforma web desarrollada para la **Universidad Tecnológica de Mineral de la Reforma (UTMiR)**. Este sistema permite la recepción, gestión y almacenamiento automático de los proyectos de estadías (Memorias) de los alumnos de Nivel Técnico Superior Universitario (TSU) y Licenciatura/Ingeniería.

---

## Características Principales

*  **Autenticación Segura:** Acceso para alumnos utilizando su Matrícula y contraseña cifrada (CURP).
* **Integración con Google Drive:** Los archivos PDF se suben directamente a la nube de la universidad mediante la API de Google Drive (OAuth 2.0).
* **Creación Dinámica de Carpetas:** El sistema analiza los datos del alumno y crea automáticamente la ruta de carpetas en Drive (Ej. `6to Cuatrimestre > TIeID`).
* **Experiencia de Usuario (UX):** Interfaz amigable con arrastrar y soltar (Drag & Drop) y una barra de progreso en tiempo real usando AJAX.
* **Panel de Administración:** Acceso exclusivo para administrativos con buscador integrado, visualización de enlaces y eliminación en cascada (borra el registro en MySQL y el archivo en Google Drive simultáneamente).
* **Validación Inteligente:** Bloqueo automático para evitar que un alumno suba más de un archivo por nivel educativo.

---

## 🛠️ Tecnologías Utilizadas

* **Frontend:** HTML5, CSS3, JavaScript (Vanilla, AJAX, Fetch API).
* **Backend:** PHP 8.x
* **Base de Datos:** MySQL (MariaDB).
* **Librerías/APIs:** Google API PHP Client (Google Drive API v3).

---

## 🚀 Instalación y Despliegue Local

Si deseas correr este proyecto en tu entorno local (XAMPP/WAMP), sigue estos pasos:

1. **Clonar el repositorio:**
   ```bash
   git clone [https://github.com/Doge8458/Gestor-de-Estadias-en-L-nea-UTMIR-GEL-.git](https://github.com/Doge8458/Gestor-de-Estadias-en-L-nea-UTMIR-GEL-.git)
