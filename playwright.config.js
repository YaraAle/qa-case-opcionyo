import { defineConfig, devices } from '@playwright/test';

export default defineConfig({

  testDir: './tests/e2e',

  use: {
    baseURL: 'http://127.0.0.1:8000',
    headless: true,
    launchOptions: {
      args: [
        '--use-fake-ui-for-media-stream',
        '--use-fake-device-for-media-stream'
      ]
    }
  },

  webServer: {
    command: 'php artisan serve --host=127.0.0.1',
    url: 'http://127.0.0.1:8000',
    reuseExistingServer: !process.env.CI,
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],

});