# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: appointment.spec.js >> Flujo C — Agendamiento >> flujo completo de reserva, prevención de doble agendamiento y liberación
- Location: tests\e2e\appointment.spec.js:6:5

# Error details

```
Test timeout of 60000ms exceeded.
```

```
Error: locator.getAttribute: Test timeout of 60000ms exceeded.
Call log:
  - waiting for locator('select[name="specialist_id"] option').first()

```

# Page snapshot

```yaml
- generic [ref=e2]:
  - navigation [ref=e3]:
    - generic [ref=e5]:
      - generic [ref=e6]:
        - link [ref=e8] [cursor=pointer]:
          - /url: http://127.0.0.1:8000/dashboard
          - img [ref=e9]
        - generic [ref=e11]:
          - link "Dashboard" [ref=e12] [cursor=pointer]:
            - /url: http://127.0.0.1:8000/dashboard
          - link "Agendar" [ref=e13] [cursor=pointer]:
            - /url: http://127.0.0.1:8000/appointments
          - link "Suscripción" [ref=e14] [cursor=pointer]:
            - /url: http://127.0.0.1:8000/subscription
          - link "Videollamada" [ref=e15] [cursor=pointer]:
            - /url: http://127.0.0.1:8000/meeting
      - button "Paciente Uno" [ref=e19] [cursor=pointer]:
        - generic [ref=e20]: Paciente Uno
        - img [ref=e22]
  - main [ref=e24]:
    - generic [ref=e25]:
      - heading "Agendar sesión" [level=1] [ref=e26]
      - generic [ref=e27]:
        - text: Especialista
        - combobox [ref=e28]
        - text: Fecha y hora
        - textbox [ref=e29]
        - button "Agendar" [ref=e30] [cursor=pointer]
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | 
  3  | test.describe('Flujo C — Agendamiento', () => {
  4  | 
  5  |     // This test simulates two patients interacting with the same slot
  6  |     test('flujo completo de reserva, prevención de doble agendamiento y liberación', async ({ page, context }) => {
  7  |         test.setTimeout(60000); // Extended timeout for multi-user flow
  8  | 
  9  |         const futureDate = '2026-12-25T10:00'; // Formato datetime-local
  10 | 
  11 |         // --- PACIENTE 1 ---
  12 |         // Registrar e iniciar sesión como Paciente 1
  13 |         await page.goto('/register');
  14 |         await page.fill('input[name="name"]', 'Paciente Uno');
  15 |         await page.fill('input[name="email"]', `paciente1_${Date.now()}@example.com`);
  16 |         await page.fill('input[name="password"]', 'password123');
  17 |         await page.fill('input[name="password_confirmation"]', 'password123');
  18 |         await page.click('button[type="submit"]');
  19 |         await expect(page).toHaveURL(/dashboard/);
  20 | 
  21 |         // Agendar cita — select the first available specialist option
  22 |         await page.goto('/appointments');
  23 |         // Use the first <option> inside the select (the seeded specialist)
> 24 |         const firstOptionValue = await page.locator('select[name="specialist_id"] option').first().getAttribute('value');
     |                                                                                                    ^ Error: locator.getAttribute: Test timeout of 60000ms exceeded.
  25 |         await page.selectOption('select[name="specialist_id"]', firstOptionValue);
  26 |         await page.fill('input[name="scheduled_at"]', futureDate);
  27 |         await page.click('button[type="submit"]');
  28 |         await expect(page.locator('body')).toContainText('Sesión creada correctamente');
  29 | 
  30 |         // --- PACIENTE 2 ---
  31 |         // Crear un nuevo contexto para simular otro navegador / usuario
  32 |         const page2 = await context.newPage();
  33 | 
  34 |         // Registrar e iniciar sesión como Paciente 2
  35 |         await page2.goto('/register');
  36 |         await page2.fill('input[name="name"]', 'Paciente Dos');
  37 |         await page2.fill('input[name="email"]', `paciente2_${Date.now()}@example.com`);
  38 |         await page2.fill('input[name="password"]', 'password123');
  39 |         await page2.fill('input[name="password_confirmation"]', 'password123');
  40 |         await page2.click('button[type="submit"]');
  41 |         await expect(page2).toHaveURL(/dashboard/);
  42 | 
  43 |         // Intentar agendar el mismo especialista y horario
  44 |         await page2.goto('/appointments');
  45 |         await page2.selectOption('select[name="specialist_id"]', firstOptionValue);
  46 |         await page2.fill('input[name="scheduled_at"]', futureDate);
  47 |         await page2.click('button[type="submit"]');
  48 | 
  49 |         // Verificar que sale el mensaje "Horario ocupado"
  50 |         await expect(page2.locator('body')).toContainText('Horario ocupado');
  51 | 
  52 |         // --- CANCELACIÓN (Paciente 1 libera el slot) ---
  53 |         // Paciente 1 realiza la cancelación de su sesión via API fetch
  54 |         await page.goto('/appointments');
  55 |         const cancelResult = await page.evaluate(async () => {
  56 |             const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  57 | 
  58 |             // Find the most recently created appointment to cancel
  59 |             const res = await fetch('/appointments/1/cancel', {
  60 |                 method: 'PATCH',
  61 |                 headers: {
  62 |                     'X-CSRF-TOKEN': csrfToken || '',
  63 |                     'Content-Type': 'application/json',
  64 |                     'Accept': 'application/json'
  65 |                 }
  66 |             });
  67 |             return { status: res.status, body: await res.json() };
  68 |         });
  69 |         expect(cancelResult.body.message).toBe('Sesión cancelada');
  70 | 
  71 |         // --- PACIENTE 2 INTENTA DE NUEVO ---
  72 |         // Ahora Paciente 2 intenta agendar el mismo slot liberado
  73 |         await page2.goto('/appointments');
  74 |         await page2.selectOption('select[name="specialist_id"]', firstOptionValue);
  75 |         await page2.fill('input[name="scheduled_at"]', futureDate);
  76 |         await page2.click('button[type="submit"]');
  77 | 
  78 |         // Debería permitir agendar exitosamente ahora
  79 |         await expect(page2.locator('body')).toContainText('Sesión creada correctamente');
  80 |     });
  81 | 
  82 | });
  83 | 
```