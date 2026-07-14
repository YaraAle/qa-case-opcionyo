# Plan de Aseguramiento de Calidad (QA) — OpciónYo

Este documento detalla la estrategia de pruebas, la selección de herramientas para cada capa y los criterios de aceptación para la puesta en producción de la plataforma de videollamadas de OpciónYo.

---

## 1. ¿Por dónde arrancamos y por qué?

Para una plataforma con ~11,000 sesiones al mes y **cero pruebas automatizadas**, el inicio debe priorizar la mitigación de riesgos críticos de negocio. Nuestra estrategia sigue un enfoque de **fuera hacia adentro** priorizando el valor y la estabilidad:

1. **Flujo de Pago con Stripe (Capa de Negocio / Dinero):** Sin pasarela de pago funcional, la empresa no recibe ingresos. Es el flujo de mayor impacto financiero.
2. **Agendamiento de Sesiones (Core del Servicio):** Si las citas se duplican, se pierden o no se pueden cancelar, la confianza de pacientes y especialistas se destruye de inmediato.
3. **Flujo de Acceso y Autenticación (Seguridad y Privacidad):** Protege la información confidencial de salud mental de los pacientes.
4. **Videollamadas con AWS Chime (Entrega del Servicio):** Es el canal de comunicación. Aunque es crítico, es el que más depende del cliente, por lo que requiere una estrategia de aislamiento para pruebas estables en integración continua.

---

## 2. Herramientas por Capa y Justificación

| Capa / Tipo de Test | Herramientas | Justificación |
| :--- | :--- | :--- |
| **Pruebas de Integración y API** | **Pest PHP** | Pest ofrece una sintaxis moderna, minimalista y basada en BDD muy superior a PHPUnit convencional. En Laravel, Pest permite probar los controladores, validaciones de seguridad (como IDORs en cancelaciones) y base de datos con transacciones rápidas y seguras sin sobrecargar el pipeline. |
| **Pruebas E2E (End-to-End)** | **Playwright (JS/TS)** | Elegido sobre Cypress o Selenium por tres motivos clave:<br>1. **Aislamiento Multi-Contexto:** Permite simular a dos usuarios distintos (Paciente 1 y Paciente 2) en el mismo test para verificar la prevención de doble agendamiento sin abrir navegadores separados.<br>2. **Hardware Mocking:** Admite flags nativos para inyectar flujos de audio y video simulados en el navegador (clave para probar AWS Chime).<br>3. **Velocidad:** Ejecuta en paralelo de forma nativa e incluye auto-esperas inteligentes reduciendo tests inestables (flaky tests). |
| **Pipeline de CI/CD** | **GitHub Actions** | Integración nativa con el repositorio, soporte robusto para contenedores y servicios (como bases de datos SQLite/MySQL en memoria) y rapidez en la ejecución de flujos de trabajo mediante caché de dependencias Composer y NPM. |
| **Base de Datos de Test** | **SQLite (:memory:)** | Permite que cada suite de pruebas se ejecute de forma aislada, rápida y sin interferir con bases de datos persistentes. |

---

## 3. ¿Cómo sabemos que algo está listo para ir a producción?

Una funcionalidad se considera lista para producción únicamente cuando cumple con la siguiente **Definición de Terminado (Definition of Done — DoD)**:

1. **Pruebas Unitarias e Integración (Pest) al 100% de éxito:** Cobertura de caminos felices y flujos alternativos (rechazo de tarjetas, validación de fechas pasadas, control de especialistas).
2. **Pruebas E2E (Playwright) exitosas en navegadores críticos:** El flujo de login, registro, pago simulado, agendamiento/cancelación y flujo de conexión de AWS Chime pasa limpiamente.
3. **Validación de Seguridad Completada:** Confirmación de que no hay fallos de autorización básicos (por ejemplo, validación de pertenencia en la cancelación de turnos, y firmas verificadas en webhooks externos).
4. **Pipeline Verde en CI:** Ninguna rama puede mezclarse (`merge`) a `main` si el workflow de GitHub Actions falla.
5. **Verificación de Performance Básica en Videollamadas:** Comprobación del correcto manejo de desconexiones o denegación de permisos de micrófono/cámara en la interfaz web (evitando pantallas colgadas para el usuario).
