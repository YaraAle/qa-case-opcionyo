import { test, expect } from '@playwright/test';

test.describe('Flujo B — Pago con Stripe (Sandbox)', () => {

    let email;

    test.beforeEach(async ({ page }) => {
        email = `stripe_${Date.now()}@example.com`;
        
        // Registrar usuario
        await page.goto('/register');
        await page.fill('input[name="name"]', 'Stripe User');
        await page.fill('input[name="email"]', email);
        await page.fill('input[name="password"]', 'password123');
        await page.fill('input[name="password_confirmation"]', 'password123');
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/dashboard/);
    });

    test('pago exitoso con tarjeta de prueba', async ({ page }) => {
        await page.goto('/subscription');
        await page.fill('input[name="card"]', '4242424242424242');
        await page.click('button[type="submit"]');
        
        await expect(page.locator('body')).toContainText('Pago exitoso');
    });

    test('pago rechazado con tarjeta declinada', async ({ page }) => {
        await page.goto('/subscription');
        await page.fill('input[name="card"]', '4000000000000002');
        await page.click('button[type="submit"]');
        
        await expect(page.locator('body')).toContainText('Pago rechazado');
    });

    test('webhook de Stripe actualiza el estado de la suscripción', async ({ page, request }) => {
        // Generar una suscripción realizando un pago exitoso
        await page.goto('/subscription');
        await page.fill('input[name="card"]', '4242424242424242');
        await page.click('button[type="submit"]');
        await expect(page.locator('body')).toContainText('Pago exitoso');

        // Simular webhook de Stripe para cancelar la suscripción
        const response = await request.post('/stripe/webhook', {
            headers: {
                'X-Stripe-Signature': 'mock_stripe_signature_secret'
            },
            data: {
                subscription_id: 1, // La primera suscripción creada en la base de datos limpia de pruebas
                status: 'cancelled'
            }
        });

        expect(response.ok()).toBe(true);
        const json = await response.json();
        expect(json.message).toBe('Webhook processed');
    });

});
