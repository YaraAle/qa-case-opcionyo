import { test, expect } from '@playwright/test';


test('usuario puede iniciar sesión en la plataforma', async ({ page }) => {

    await page.goto('/login');


    await page.fill(
        'input[name="email"]',
        'test@example.com'
    );


    await page.fill(
        'input[name="password"]',
        'password'
    );


    await page.click(
        'button[type="submit"]'
    );


    await expect(page)
        .toHaveURL(/dashboard/);

});



test('usuario no puede iniciar sesión con contraseña incorrecta', async ({ page }) => {

    await page.goto('/login');


    await page.fill(
        'input[name="email"]',
        'test@example.com'
    );


    await page.fill(
        'input[name="password"]',
        'incorrecta123'
    );


    await page.click(
        'button[type="submit"]'
    );


    await expect(page.locator('body'))
        .toContainText('These credentials do not match');

});



test('usuario no registrado no puede iniciar sesión', async ({ page }) => {

    await page.goto('/login');


    await page.fill(
        'input[name="email"]',
        'usuario-no-existe@test.com'
    );


    await page.fill(
        'input[name="password"]',
        'password'
    );


    await page.click(
        'button[type="submit"]'
    );


    await expect(page.locator('body'))
        .toContainText('These credentials do not match');

});
