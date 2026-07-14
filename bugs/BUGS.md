# Reporte de Bugs y Edge Cases — OpciónYo

Este documento detalla tres fallos lógicos y de seguridad críticos encontrados en la implementación original del mock de OpciónYo, con sus respectivos pasos de reproducción, comportamiento esperado y nivel de severidad.

---

## Bug 1: Vulnerabilidad IDOR (Insecure Direct Object Reference) en Cancelación de Citas

* **Qué pasa:** Cualquier usuario autenticado en la plataforma puede cancelar el agendamiento de cualquier otro usuario simplemente enviando una petición `PATCH` al endpoint `/appointments/{appointment}/cancel`. El controlador no valida si la cita pertenece al usuario autenticado.
* **Cómo reproducirlo:**
  1. Iniciar sesión como `Usuario A` y agendar un turno (ID de cita generado: `1`).
  2. Iniciar sesión como `Usuario B` (o utilizar su token de sesión).
  3. Enviar una petición HTTP `PATCH` a `/appointments/1/cancel`.
  4. La cita se cancela en la base de datos y se recibe una respuesta exitosa (`200 OK - "Sesión cancelada"`).
* **Qué debería pasar:** El sistema debe comprobar que el `user_id` de la cita a cancelar sea igual al ID del usuario autenticado (`Auth::id()`). Si no coinciden, debe rechazar la solicitud con un código HTTP `403 Forbidden` ("No autorizado para cancelar esta sesión").
* **Severidad:** **Crítica** (Permite sabotear la agenda de especialistas y pacientes de forma masiva).

---

## Bug 2: Webhook de Stripe inseguro (Sin firma) e Inconsistencia de Identificadores

* **Qué pasa:** El endpoint del webhook de Stripe `/stripe/webhook` carece de verificación de firmas (`Stripe-Signature`). Además, busca la suscripción en la base de datos usando el ID incremental interno (`subscription_id` de la tabla) en lugar del `stripe_id` (el identificador de la transacción que Stripe retorna en su respuesta de pago). Esto permite alterar estados de suscripción enviando cargas útiles arbitrarias.
* **Cómo reproducirlo:**
  1. Enviar una petición `POST` al endpoint `/stripe/webhook` sin ningún encabezado de autenticación con el siguiente payload:
     ```json
     {
       "subscription_id": 1,
       "status": "active"
     }
     ```
  2. El servidor responde con `200 OK` y el estado de la suscripción se actualiza en la base de datos a `active`.
* **Qué debería pasar:** El webhook debe simular o verificar el encabezado `X-Stripe-Signature` para asegurar que la llamada proviene de Stripe. Asimismo, la búsqueda debe realizarse contra el campo `stripe_id` (ej. `mock_tx_...`) para mantener la coherencia e impedir que los usuarios adivinen y manipulen suscripciones de otros mediante IDs secuenciales.
* **Severidad:** **Crítica** (Vulnerabilidad de fraude financiero directo).

---

## Bug 3: Falta de validación temporal y disponibilidad en el Agendamiento

* **Qué pasa:** El sistema permite que los pacientes programen sesiones médicas en fechas del pasado. Además, no comprueba si el especialista existe o si está disponible (`available = true`), permitiendo reservar citas inválidas.
* **Cómo reproducirlo:**
  1. Iniciar sesión como paciente.
  2. Enviar una petición `POST` a `/appointments` enviando una fecha anterior a hoy (ej. `2015-05-12 14:00`) y el ID de un especialista cuya disponibilidad es falsa (`available = false`).
  3. El sistema registra la cita exitosamente y responde con un redirect indicando éxito.
* **Qué debería pasar:** El validador de Laravel debe exigir que `scheduled_at` sea una fecha posterior al momento actual (`after:now`). Adicionalmente, el controlador debe comprobar que el especialista exista en la base de datos y que su estado de disponibilidad sea verdadero. De lo contrario, debe retornar un error.
* **Severidad:** **Alta** (Afecta directamente la lógica y usabilidad del negocio).
