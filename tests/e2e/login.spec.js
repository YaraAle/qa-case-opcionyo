import { test, expect } from '@playwright/test';

test.describe('Flujo A — Login y Registro', () => {

    test('usuario puede registrarse con email y contraseña', async ({ page }) => {
        const uniqueEmail = `user_${Date.now()}@example.com`;

        await page.goto('/register');

        await page.fill('input[name="name"]', 'Usuario Test E2E');
        await page.fill('input[name="email"]', uniqueEmail);
        await page.fill('input[name="password"]', 'password123');
        await page.fill('input[name="password_confirmation"]', 'password123');

        await page.click('button[type="submit"]');

        await expect(page).toHaveURL(/dashboard/);
    });

    test('usuario puede iniciar sesión con credenciales válidas', async ({ page }) => {
        // We register first or use the seeded user. Let's register a new one to avoid dependencies.
        const email = `login_${Date.now()}@example.com`;

        // Register
        await page.goto('/register');
        await page.fill('input[name="name"]', 'Login User');
        await page.fill('input[name="email"]', email);
        await page.fill('input[name="password"]', 'password123');
        await page.fill('input[name="password_confirmation"]', 'password123');
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/dashboard/);

        // Logout
        await page.click('button:has-text("Login User")'); // Click dropdown
        await page.click('a:has-text("Log Out")');
        await expect(page).toHaveURL('/');

        // Login
        await page.goto('/login');
        await page.fill('input[name="email"]', email);
        await page.fill('input[name="password"]', 'password123');
        await page.click('button[type="submit"]');

        await expect(page).toHaveURL(/dashboard/);
    });

    test('usuario no puede iniciar sesión con contraseña incorrecta', async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', 'test@example.com');
        await page.fill('input[name="password"]', 'incorrecta123');
        await page.click('button[type="submit"]');

        await expect(page.locator('body')).toContainText('These credentials do not match');
    });

    test('usuario no registrado no puede iniciar sesión', async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', 'usuario-no-existe@test.com');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');

        await expect(page.locator('body')).toContainText('These credentials do not match');
    });

    test('acceso denegado a recurso protegido sin autenticación', async ({ page }) => {
        // Attempt to visit protected dashboard
        await page.goto('/dashboard');
        await expect(page).toHaveURL(/login/);

        // Attempt to visit protected appointments page
        await page.goto('/appointments');
        await expect(page).toHaveURL(/login/);
    });

});
