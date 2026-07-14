import { test, expect } from '@playwright/test';

test.describe('Flujo C — Agendamiento', () => {

    // This test simulates two patients interacting with the same slot
    test('flujo completo de reserva, prevención de doble agendamiento y liberación', async ({ page, context }) => {
        test.setTimeout(60000); // Extended timeout for multi-user flow

        const futureDate = '2026-12-25T10:00'; // Formato datetime-local

        // --- PACIENTE 1 ---
        // Registrar e iniciar sesión como Paciente 1
        await page.goto('/register');
        await page.fill('input[name="name"]', 'Paciente Uno');
        await page.fill('input[name="email"]', `paciente1_${Date.now()}@example.com`);
        await page.fill('input[name="password"]', 'password123');
        await page.fill('input[name="password_confirmation"]', 'password123');
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/dashboard/);

        // Agendar cita — select the first available specialist option
        await page.goto('/appointments');
        // Use the first <option> inside the select (the seeded specialist)
        const firstOptionValue = await page.locator('select[name="specialist_id"] option').first().getAttribute('value');
        await page.selectOption('select[name="specialist_id"]', firstOptionValue);
        await page.fill('input[name="scheduled_at"]', futureDate);
        await page.click('button[type="submit"]');
        await expect(page.locator('body')).toContainText('Sesión creada correctamente');

        // --- PACIENTE 2 ---
        // Crear un nuevo contexto para simular otro navegador / usuario
        const page2 = await context.newPage();

        // Registrar e iniciar sesión como Paciente 2
        await page2.goto('/register');
        await page2.fill('input[name="name"]', 'Paciente Dos');
        await page2.fill('input[name="email"]', `paciente2_${Date.now()}@example.com`);
        await page2.fill('input[name="password"]', 'password123');
        await page2.fill('input[name="password_confirmation"]', 'password123');
        await page2.click('button[type="submit"]');
        await expect(page2).toHaveURL(/dashboard/);

        // Intentar agendar el mismo especialista y horario
        await page2.goto('/appointments');
        await page2.selectOption('select[name="specialist_id"]', firstOptionValue);
        await page2.fill('input[name="scheduled_at"]', futureDate);
        await page2.click('button[type="submit"]');

        // Verificar que sale el mensaje "Horario ocupado"
        await expect(page2.locator('body')).toContainText('Horario ocupado');

        // --- CANCELACIÓN (Paciente 1 libera el slot) ---
        // Paciente 1 realiza la cancelación de su sesión via API fetch
        await page.goto('/appointments');
        const cancelResult = await page.evaluate(async () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // Find the most recently created appointment to cancel
            const res = await fetch('/appointments/1/cancel', {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            return { status: res.status, body: await res.json() };
        });
        expect(cancelResult.body.message).toBe('Sesión cancelada');

        // --- PACIENTE 2 INTENTA DE NUEVO ---
        // Ahora Paciente 2 intenta agendar el mismo slot liberado
        await page2.goto('/appointments');
        await page2.selectOption('select[name="specialist_id"]', firstOptionValue);
        await page2.fill('input[name="scheduled_at"]', futureDate);
        await page2.click('button[type="submit"]');

        // Debería permitir agendar exitosamente ahora
        await expect(page2.locator('body')).toContainText('Sesión creada correctamente');
    });

});
