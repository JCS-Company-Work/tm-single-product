import { test, expect } from '@playwright/test';

const adminUrl = 'https://tm-store-jan-26.local/wp-admin/'; // Change to your local admin URL
const username = '@ptshop'; // Change to your admin username
const password = 'DYydKT3GkqfxmQpzdsvZ'; // Change to your admin password
const productEditUrl = 'https://tm-store-jan-26.local/wp-admin/post.php?post=4999&action=edit';

test.describe('Product Configurator Admin @critical', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto(adminUrl);
    await page.fill('#user_login', username);
    await page.fill('#user_pass', password);
    await page.click('#wp-submit');
    await expect(page).toHaveURL(/wp-admin/);
  });

  // test('Save and reload model sizes @critical', async ({ page }) => {

  //   // Navigate to product edit page
  //   await page.goto(productEditUrl);

  //   // Open model tab
  //   await page.click('.tmpc_model_size_options');

  //   // Select "200cm" in the correct row
  //   await page.selectOption('select[name="tmpc_model_sizes[0][label]"]', '200cm');

  //   // Click the toggle (slider) for the correct model size row
  //   await page.click('label.tmpc-toggle-switch:has(input[type="radio"][name="tmpc_model_sizes_default"][value="0"]) .tmpc-slider');

  //   // Wait for the button to be visible and enabled
  //   const saveButton = page.locator('.editor-post-publish-button');
  //   await saveButton.waitFor({ state: 'visible', timeout: 10000 });
  //   await expect(saveButton).toBeEnabled();
    
  //   // Save changes
  //   await saveButton.click();

  //   // Wait for the success notice
  //   await expect(page.locator('.components-snackbar__content')).toContainText('Post updated', { timeout: 30000 });

  //   // Reload and check the selection and default
  //   await page.reload();

  //   // Open model tab again to check values
  //   await page.click('.tmpc_model_size_options');

  //   // Verify the selected value and default toggle
  //   await expect(page.locator('select[name="tmpc_model_sizes[0][label]"]')).toHaveValue('200cm');
  //   await expect(page.locator('input[type="radio"][name="tmpc_model_sizes_default"][value="0"]')).toBeChecked();

  // });

  // test('Save and reload default colours @critical', async ({ page }) => {

  //   // Navigate to product edit page
  //   await page.goto(productEditUrl);

  //   // Open colour tab
  //   await page.click('.tmpc_colours_options');

  //   // Select colours from dropdowns (adjust selectors as needed)
  //   await page.selectOption('select[name="tmpc_top_colour"]', 'viola rosso');
  //   await page.selectOption('select[name="tmpc_base_colour"]', 'american walnut');
  //   await page.selectOption('select[name="tmpc_metal_colour"]', 'brushed bronze');
    
  //   // Wait for the button to be visible and enabled
  //   const saveButton = page.locator('.editor-post-publish-button');
  //   await saveButton.waitFor({ state: 'visible', timeout: 10000 });
  //   await expect(saveButton).toBeEnabled();
    
  //   // Save changes
  //   await saveButton.click();

  //   // Wait for the success notice
  //   await expect(page.locator('.components-snackbar__content')).toContainText('Post updated', { timeout: 30000 });

  //   // Reload and check the selection and default
  //   await page.reload();

  //   // Open colour tab again to check values
  //   await page.click('.tmpc_colours_options');

  //   await expect(page.locator('select[name="tmpc_top_colour"]')).toHaveValue('viola rosso');
  //   await expect(page.locator('select[name="tmpc_base_colour"]')).toHaveValue('american walnut');
  //   await expect(page.locator('select[name="tmpc_metal_colour"]')).toHaveValue('brushed bronze');

  // });

  test('Saved admin settings are applied on the frontend @critical', async ({ page }) => {

    // Navigate to product edit page
    await page.goto(productEditUrl);

    // Open model tab and set values
    await page.click('.tmpc_model_size_options');
    await page.selectOption('select[name="tmpc_model_sizes[0][label]"]', '200cm');
    await page.click('label.tmpc-toggle-switch:has(input[type="radio"][name="tmpc_model_sizes_default"][value="0"]) .tmpc-slider');

    // Open colour tab and set values
    await page.click('.tmpc_colours_options');
    await page.selectOption('select[name="tmpc_top_colour"]', 'viola rosso');
    await page.selectOption('select[name="tmpc_base_colour"]', 'american walnut');

    // Only select metal if present
    const metalSelect = page.locator('select[name="tmpc_metal_colour"]');
    if (await metalSelect.count() > 0) {
      await metalSelect.selectOption('brushed bronze');
    }

    // Save changes
    const saveButton = page.locator('.editor-post-publish-button');
    await saveButton.waitFor({ state: 'visible', timeout: 10000 });
    await expect(saveButton).toBeEnabled();
    await saveButton.click();

    // Wait for the success notice
    await expect(page.locator('.components-snackbar__content')).toContainText('Post updated', { timeout: 30000 });

    // Go to the frontend product page
    await page.goto('https://tm-store-jan-26.local/product/phantom-edge-stadium/');

    // Verify that the default model size and colours are applied (adjust selectors as needed)
    const modelSelect = await page.locator('select[name="product-model-size"] option[selected]').first();
    await expect(modelSelect).toHaveAttribute('data-label', '200cm');

    await expect(page.locator('input[name="top_colour"]:checked')).toHaveValue('viola rosso');
    await expect(page.locator('input[name="base_colour"]:checked')).toHaveValue('american walnut');

    // Only check metal if present
    const metalInput = page.locator('input[name="metal_edge_veneer"]:checked');
    if (await metalInput.count() > 0) {
      await expect(metalInput).toHaveValue('brushed bronze');
    }

  });
});
