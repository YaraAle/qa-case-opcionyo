import { defineConfig } from '@playwright/test';


export default defineConfig({

    testDir: './tests/e2e',

    use: {

        baseURL: 'http://qa-case-opcionyo.test',

        browserName: 'chromium',

        headless: false

    },

});