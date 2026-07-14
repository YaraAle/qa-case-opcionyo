import { test, expect } from '@playwright/test';

test.describe('Pruebas de AWS Chime (Videollamada)', () => {

    test.beforeEach(async ({ page }) => {
        // Authenticate
        await page.goto('/register');
        await page.fill('input[name="name"]', 'Chime Tester');
        await page.fill('input[name="email"]', `chime_${Date.now()}@example.com`);
        await page.fill('input[name="password"]', 'password123');
        await page.fill('input[name="password_confirmation"]', 'password123');
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/dashboard/);
    });

    test('usuario puede entrar a la reunion exitosamente (permisos y dispositivos OK)', async ({ page }) => {
        await page.goto('/meeting');

        // Initial state
        await expect(page.locator('#status-text')).toHaveText('Desconectado');

        // Click join
        await page.click('#btn-join');

        // Assert success states
        await expect(page.locator('#status-text')).toHaveText('Conectado');
        await expect(page.locator('#camera-text')).toHaveText('Lista');
        await expect(page.locator('#mic-text')).toHaveText('Listo');
        await expect(page.locator('#success-area')).toBeVisible();
        await expect(page.locator('#error-message')).toBeHidden();
    });

    test('error controlado cuando el usuario deniega los permisos de camara/microfono', async ({ page }) => {
        // IMPORTANT: addInitScript must be called BEFORE page.goto so the mock
        // is installed when the page loads and overrides the fake device stream.
        await page.addInitScript(() => {
            navigator.mediaDevices.getUserMedia = () =>
                Promise.reject(new DOMException('Permission denied', 'NotAllowedError'));
        });

        await page.goto('/meeting');

        // Click join
        await page.click('#btn-join');

        // Assert failure states
        await expect(page.locator('#status-text')).toHaveText('Error');
        await expect(page.locator('#camera-text')).toHaveText('No disponible');
        await expect(page.locator('#mic-text')).toHaveText('No disponible');
        await expect(page.locator('#error-message')).toBeVisible();
        await expect(page.locator('#error-message')).toContainText('NotAllowedError');
        await expect(page.locator('#success-area')).toBeHidden();
    });

    test('error controlado cuando no se encuentran dispositivos fisicos conectados', async ({ page }) => {
        // IMPORTANT: addInitScript must be called BEFORE page.goto
        await page.addInitScript(() => {
            navigator.mediaDevices.getUserMedia = () =>
                Promise.reject(new DOMException('Requested device not found', 'NotFoundError'));
        });

        await page.goto('/meeting');

        // Click join
        await page.click('#btn-join');

        // Assert failure states
        await expect(page.locator('#status-text')).toHaveText('Error');
        await expect(page.locator('#camera-text')).toHaveText('No disponible');
        await expect(page.locator('#mic-text')).toHaveText('No disponible');
        await expect(page.locator('#error-message')).toBeVisible();
        await expect(page.locator('#error-message')).toContainText('NotFoundError');
        await expect(page.locator('#success-area')).toBeHidden();
    });

});
