import { test, expect } from '@playwright/test';

// Update this to a real product slug or URL for your environment
const PRODUCT_URL = '/product/phantom-edge-stadium/';

function normalize(str) {
  return str.replace(/\s+/g, ' ').trim();
}

function normalizeDashes(str) {
  return str.replace(/[\u2013\u2014\u2012\u2011\u2010]/g, '-').replace(/\s+/g, ' ').trim();
}

test.describe('TMPC Product Configurator Basket', () => {

    test('Add product to cart via AJAX @critical', async ({ page }) => {

        // Intercept AJAX add-to-cart request (set up BEFORE navigation)
        let ajaxPayload = null;
        await page.route(/\/wp-admin\/admin-ajax\.php(\?.*)?$/, async (route, request) => {
            if (request.method() === 'POST' && request.postData()?.includes('tm_add_to_cart')) {
                const body = request.postData();
                const result = {};
                // Simple regex to extract key-value pairs
                const regex = /name="([^"]+)"\r?\n\r?\n([\s\S]*?)\r?\n-+WebKitFormBoundary/g;
                let match;
                while ((match = regex.exec(body)) !== null) {
                    result[match[1]] = match[2].trim();
                }
                ajaxPayload = result;
            }
            route.continue();
        });

        // Open the page and wait for the dropdown to be visible
        await page.goto(PRODUCT_URL);

        // Add product to basket
        await page.click('.single_add_to_cart_button');

        // Wait for WooCommerce success message
        await page.waitForSelector('.ajax-add-to-cart-message');
        await expect(page.locator('.ajax-add-to-cart-message')).toContainText('Product added to cart');

        // Get product name for verification
        const productName = await page.locator('.featured-banner h1').textContent();

        // Get price for verification
        const price = await page.locator('.status-price').textContent();

        // Hover over mini cart
        await page.hover('.tm-header-cart-link');

        // Check that the swatch is in the mini cart
        const miniCartItems = await page.locator('.mini_cart_item').allTextContents();
        expect(miniCartItems.some(text => normalizeDashes(text).includes(normalizeDashes(productName)))).toBeTruthy();

        // Load basket page
        await page.click('.checkout');

        // Wait for cart items to appear (max 5s)
        await page.waitForSelector('.cart_item', { timeout: 5000 });

        // Check that swatch is in the basket with correct note
        const basketHtml = await page.locator('.cart_item').allInnerTexts();
        expect(basketHtml.some(html => normalizeDashes(html).includes(normalizeDashes(productName)))).toBeTruthy();

        // --- Assert that each metadata value in the mini-cart matches the AJAX payload ---
        if (ajaxPayload) {
            
            // Only check for top_colour, base, and model keys
            const keysToCheck = ['top_colour', 'base', 'model'];

            // Get the keys from the AJAX payload that we want to check
            const metaKeys = Object.keys(ajaxPayload).filter(k => keysToCheck.includes(k));

            // Loop through each key and check that its value appears in the mini-cart HTML
            for (const key of metaKeys) {

                // Get the value from the AJAX payload
                const value = ajaxPayload[key];

                // Assert that this value appears somewhere in the mini-cart HTML (case-insensitive, ignoring dashes)
                expect(basketHtml.some(html => normalizeDashes(html).toLowerCase().includes(normalizeDashes(value).toLowerCase()))).toBeTruthy();
            }

        } else {
            console.warn('AJAX add-to-cart payload not captured.');
        }

    });

    test('Add sample to cart via AJAX @critical', async ({ page }) => {

        // Open the page and wait for the dropdown to be visible
        await page.goto(PRODUCT_URL);
       
        // Open the dropdown
        await page.click('.select2-container'); 

        // Select the third option (index 2, since index is zero-based and first is usually a placeholder)
        const options = await page.$$('.select2-results__options li');

        // Get the text of the third option for verification
        const thirdOptionSpan = await options[2].$('span');
        const thirdOptionText = await thirdOptionSpan.textContent();

        // Click swatch option
        await options[2].click();

        // Add swatch to basket
        await page.click('.product-add-sample-button');
        
        // Wait for WooCommerce success message
        await page.waitForSelector('.swatch-add-message');
        await expect(page.locator('.swatch-add-message')).toContainText('Swatch added to cart');

        // Hover over mini cart
        await page.hover('.tm-header-cart-link');

        // Check that the swatch is in the mini cart
        const miniCartItems = await page.locator('.mini_cart_item a').allTextContents();
        expect(miniCartItems.some(text => text.trim().includes(thirdOptionText.trim()))).toBeTruthy();

        // Load checkout page
        await page.click('.checkout');

        // Wait for cart items to appear (max 5s)
        await page.waitForSelector('.cart_item', { timeout: 5000 });

        // Check that swatch is in the basket with correct note
        const basketHtml = await page.locator('.cart_item').allInnerTexts();
        expect(basketHtml.some(html => normalize(html).includes(normalize(thirdOptionText)))).toBeTruthy();
    });

});