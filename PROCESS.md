# Bitácora de Proceso (PROCESS.md) — OpciónYo

Este documento explica las herramientas utilizadas, las decisiones técnicas tomadas, los problemas identificados y la justificación detrás de las soluciones implementadas en este proyecto.

---

## 1. Herramientas Utilizadas y por qué

Para este proyecto de QA, hemos elegido un stack de pruebas moderno que nos proporciona velocidad, fiabilidad y alta mantenibilidad:

* **Pest PHP (Pruebas de Integración y API):**
  * *Por qué:* Laravel viene con soporte nativo de Pest. Pest nos permite escribir pruebas más limpias utilizando la sintaxis de BDD (Behavior-Driven Development). Sus aserciones de base de datos (`assertDatabaseHas`) son muy potentes para verificar que el webhook de Stripe y el agendamiento guardaron los datos correctos sin depender del frontend.
* **Playwright (Pruebas E2E):**
  * *Por qué:* A diferencia de Cypress, Playwright permite manejar de manera nativa **múltiples navegadores y múltiples contextos independientes en una sola prueba**. Esto es crítico para simular que el *Paciente 2* intente agendar un slot mientras el *Paciente 1* ya lo tiene reservado (probando la condición de carrera y el doble agendamiento).
  * *Por qué:* Además, Playwright nos permite pasar parámetros de línea de comandos a Chromium para inyectar periféricos de audio y video simulados, facilitando el testing automatizado en entornos de Integración Continua (CI).
* **SQLite:**
  * *Por qué:* Al ser una base de datos local basada en archivos (o en memoria), acelera la velocidad de las pruebas integradas y evita depender de una base de datos externa en el pipeline de GitHub Actions.
* **Inteligencia Artificial (Antigravity de Google DeepMind):**
  * *Por qué:* Se utilizó IA para acelerar el análisis estático de vulnerabilidades del código Laravel, escribir la estructura base de los spec de Playwright y asegurar la cobertura de casos de prueba tanto felices como de error.

---

## 2. Decisiones de Diseño y Estrategias por Flujo

### Flujo A — Login y Registro
* **Estrategia:** Creamos una suite en `tests/e2e/login.spec.js` que cubre todo el flujo. Para evitar que las pruebas dependieran de usuarios previamente creados (lo que causaría que el test fallara en ejecuciones subsecuentes), el test de login dinámicamente registra un usuario con un correo único (`Date.now()`), cierra la sesión y luego inicia sesión de nuevo.
* **Seguridad:** Agregamos una prueba explícita para verificar que si un usuario intenta navegar a `/dashboard` o `/appointments` sin estar autenticado, la aplicación lo redirija automáticamente al `/login`.

### Flujo B — Pago con Stripe
* **Solución de Bugs:** El webhook original (`StripeWebhookController`) buscaba la suscripción usando el ID incremental interno de la base de datos y no tenía seguridad. Lo corregimos para buscar por el campo `stripe_id` (el token de transacción que Stripe envía en su payload, ej: `mock_tx_...`) y agregamos una verificación simulada de firma mediante el encabezado `X-Stripe-Signature`.
* **Prueba E2E:** En `tests/e2e/payment.spec.js` llenamos el formulario de tarjeta con `4242...` (éxito) y `4000...` (declinado) para verificar las alertas visuales en el frontend. Para el webhook, Playwright envía una petición POST al endpoint simulando la firma correcta para verificar que el estado pase a `cancelled` de forma segura.

### Flujo C — Agendamiento y Prevención de Doble Reserva
* **Solución de Bugs:** El agendamiento original permitía reservar en el pasado y no tenía transacciones para evitar condiciones de carrera. Agregamos validación de fecha (`after:now`) y usamos transacciones de base de datos con bloqueo exclusivo (`lockForUpdate()`). Además, agregamos control de IDOR en la cancelación para evitar que usuarios cancelen turnos de otros.
* **Prueba E2E:** En `tests/e2e/appointment.spec.js` levantamos dos páginas del navegador concurrentes (`page` y `page2`) usando contextos de navegador separados. El Paciente 1 reserva un slot. El Paciente 2 intenta reservar el mismo slot y recibe la alerta "Horario ocupado". Luego, el Paciente 1 cancela su cita enviando un `fetch` firmado y el Paciente 2 inmediatamente reintenta agendarlo con éxito.

### Videollamadas con AWS Chime
* **La Estrategia de Hardware:** Al no contar con hardware real en CI, la solución es el aislamiento.
  1. Configurar Playwright para iniciar el navegador con flags especiales:
     * `--use-fake-device-for-media-stream` (simula cámara/micrófono inyectando barras de color estáticas y tono de audio).
     * `--use-fake-ui-for-media-stream` (evita la ventana emergente de permisos del navegador y auto-concede).
  2. Implementar lógica interactiva en `meeting.blade.php` que invoca `navigator.mediaDevices.getUserMedia()`.
  3. En Playwright, probamos el camino exitoso (usando los dispositivos falsos) y los flujos de error (bloqueando programáticamente el acceso a la cámara y micrófono mediante scripts inyectados para retornar `NotAllowedError` o `NotFoundError`). Esto prueba cómo se comporta la interfaz de videollamada ante fallas de hardware.

---

## 3. Filosofía de QA Aplicada

Nuestra filosofía de QA es que **un buen ingeniero de calidad no solo señala problemas, sino que ayuda a resolverlos**. En lugar de simplemente escribir pruebas automatizadas contra endpoints inseguros y con fallas, mejoramos proactivamente la base de código del mock para cerrar brechas de seguridad (como el IDOR y el Webhook libre de firma) y prevenir condiciones de carrera críticas. Esto garantiza que la suite de pruebas sea robusta, realista y agregue el máximo valor posible al equipo de desarrollo.
