import { test, expect } from '@playwright/test';

// --- Composite Image URL Helper ---
const crypto = require('crypto');

/**
 * Replicates PHP's processLayers logic and returns the composite image URL.
 * @param {string} productSku - The product SKU (e.g., 'phantom-edge-stadium-bp105')
 * @param {object} selectedLayers - { base, metal, top }
 * @param {string} [siteUrl] - The site base URL (default: 'https://tm-store-jan-26.local')
 * @returns {string} - The expected composite image URL (700px)
 */
function getCompositeImageUrl(productSku, selectedLayers, siteUrl = 'https://tm-store-jan-26.local') {
  
    // Helper to format layer names
    const format = v => v ? v.toLowerCase().replace(/ /g, '-') : '';

    // Replicate PHP's SKU parsing logic
    const match = productSku.toLowerCase().match(/^(.*?)-bp(.*)$/);
    const top_layer = match && match[1] ? match[1].replace(/-$/, '') : '';
    const base_layer = 'bp' + (match && match[2] ? match[2] : '');
    const shadow_layer = top_layer.includes('-') ? top_layer.split('-')[0] : top_layer;
    const metal_layer = top_layer.includes('metal') ? top_layer : null;

    // Use absolute path if needed, or match your PHP logic
    const base_folder = '/Users/neilwilliams/Local Sites/tm-store-jan-26/app/public/wp-content/themes/tm-shop-child/assets/layers';

    // Build array of image paths for each layer, using the formatted selected layer values
    const layers = {
        shadow: `${base_folder}/${shadow_layer}-shadow.png`,
        base: `${base_folder}/${base_layer}-${format(selectedLayers.base)}.png`,
        metal: metal_layer ? `${base_folder}/${metal_layer}-${format(selectedLayers.metal)}.png` : null,
        top: `${base_folder}/${top_layer}-${format(selectedLayers.top)}.png`
    };

    // Remove null values (like PHP's array_filter)
    Object.keys(layers).forEach(key => {
        if (!layers[key]) delete layers[key];
    });
    
    // JSON encode with slashes escaped to match PHP's json_encode behavior
    let json = JSON.stringify(layers).replace(/\//g, '\\/');
    
    // Generate MD5 hash of the JSON string
    const hash = crypto.createHash('md5').update(json).digest('hex');

    // Construct the expected composite image URL, sizes to be added in specific checks as needed
    return `${siteUrl}/wp-content/themes/tm-shop-child/assets/layers/composites/${hash}`;

}
// --- End Composite Image URL Helper ---

    test('User model changes correctly update product @critical', async ({ page }) => {

        // Define the product URL
        const productUrl = '/product/phantom-edge-stadium/';

        // Visit the product page
        await page.goto(productUrl);

        // Save base price for later comparison
        const basePriceStr = await page.locator('.product-model-from-price').getAttribute('data-from-price');

        // Extract the numeric value from the price string and convert to a number, e.g. "From £6,000" -> 6000
        const numericBasePriceStr = basePriceStr.replace(/,/g, '').replace(/[^\d.]/g, '');
        const basePrice = parseFloat(numericBasePriceStr);

        // Open model drawer
        await page.click('#option-model');

        // Get all option values 
        const options = await page.$$eval('select[name="product-model-size"] option', opts =>
            opts.map(o => ({ value: o.value, label: o.getAttribute('data-label') }))
        );

        // Select a random option
        const randomIndex = Math.floor(Math.random() * options.length);
        const randomLabel = options[randomIndex].label;

        // Select the option
        await page.selectOption('select[name="product-model-size"]', { index: randomIndex });
        
        // Get the selected option element again to access its data attributes
        const selectedOption = await page.locator(`select[name="product-model-size"] option[data-label="${randomLabel}"]`);

        // Check that model change is reflected in the URL with the correct label (spaces replaced with dashes and lowercased)
        await expect(page).toHaveURL(new RegExp(`model=${randomLabel.toLowerCase().replace(/\s+/g, '-')}`));
        
        // Wait for the price to update in the UI
        const modelPriceStr = await selectedOption.getAttribute('data-wapf-price');
        
        // Convert the price string to a number
        const modelPrice = parseInt(modelPriceStr, 10);

        // Check that change is reflected in URL
        await expect(page).toHaveURL(new RegExp(`model=${await selectedOption.getAttribute('data-label')}`));

        // Add them together
        const total = basePrice + modelPrice;

        // Check that total price is correctly updated in the UI
        await expect(page.locator('.status-price')).toHaveText(`£${total.toFixed(2)}`);

        // Check that model dimensions label below dropdown updates to match selected option
        const dimsContainer = await page.locator('.config-selectors .model-dims');

        // Find model dims element that contains 'model-{dims label}' class
        const modelDims = dimsContainer.locator(`.model-${randomLabel.toLowerCase().replace(/\s+/g, '-')}`);

        // Check that the correct model dims element is visible
        await expect(modelDims).toBeVisible();

        // Check that the text content of the model dims element contains the label of the selected option
        await expect(modelDims).toContainText(randomLabel);
        
        // Check that change is reflected in current status dimensions text
        const statusDims = await page.locator('.status-dimensions');
        await expect(statusDims).toContainText(randomLabel);

        // Check that change is reflected in current status specification text
        const statusParagraph = page.locator('.status-specification-text p');
        await expect(statusParagraph).toContainText(randomLabel);

    });

    test('User top colour changes correctly update product @critical', async ({ page }) => {

        // Define the product URL
        const productUrl = '/product/phantom-edge-stadium/';
        
        // Visit the product page
        await page.goto(productUrl);

        // Wait for colour options to load before executing test
        await page.waitForResponse(resp =>
            resp.url().includes('/wp-json/tmpc/v1/colour-options?product_id=') && resp.status() === 200
        );
    
        // Open top colour drawer
        await page.click('#option-top-colour');

        // Get all .wapf-swatch elements for top colour
        const swatches = await page.$$('.obj-top-colour .wapf-swatch');

        // Pick a random swatch
        const randomIndex = Math.floor(Math.random() * swatches.length);
        const randomSwatch = swatches[randomIndex];

        // Find the child radio button with name="top_colour" and check it
        const topColourRadio = await randomSwatch.$('input[type="radio"][name="top_colour"]');
        await topColourRadio.check();

        // Close config drawer
        await page.click('#configCloseButton');

        // Get the value of the selected radio button
        const topColour = await topColourRadio.getAttribute('value');

        // Check that top colour change is reflected in the URL with the correct label (spaces replaced with dashes and lowercased)
        await expect(page).toHaveURL(new RegExp(`colour=${encodeURIComponent(topColour)}`, 'i'));

        // Get product SKU from the DOM
        const productSku = (await page.locator('.sku').innerText()).toLowerCase();

        // Open base drawer
        await page.click('#option-base');

        // Get checked base and metal colour values from the DOM that accompany top colour selection
        const baseColour = await page.locator('.obj-base input[name="base_colour"]:checked').getAttribute('value');

        // Close config drawer
        await page.click('#configCloseButton');

        // Open metals drawer
        await page.click('#option-metal-edge-veneer');
        const metalColour = await page.locator('.obj-metal-edge-veneer input[name="metal_edge_veneer"]:checked').getAttribute('value');

        // Close config drawer
        await page.click('#configCloseButton');

        // Gather selected layers from DOM
        const selectedLayers = {
            top: topColour,
            base: baseColour,
            metal: metalColour
        };

        // Get the expected composite image URL
        const expectedImageUrl = getCompositeImageUrl(productSku, selectedLayers);

        // Assert that status image is updated with the expected composite image URL
        const statusImage = page.locator('.status-image img');

        // Wait for the src attribute to match the expected value
        await expect(statusImage).toHaveAttribute('src', `${expectedImageUrl}-700.png`, { timeout: 15000 });

        // Gallery images update with valid options
        const galleryComposite = page.locator('.tm-gallery .composite-image img');

        // Wait for the src attribute of gallery images to match the expected value
        await expect(galleryComposite).toHaveAttribute('src', `${expectedImageUrl}-1600.png`, { timeout: 15000 });

    });

    test('User base colour changes correctly update product @critical', async ({ page }) => {
        
        // Define the product URL
        const productUrl = '/product/phantom-edge-stadium/';
        
        // Visit the product page
        await page.goto(productUrl);
        
        // Wait for colour options to load before executing test
        await page.waitForResponse(resp =>
        resp.url().includes('/wp-json/tmpc/v1/colour-options?product_id=') && resp.status() === 200
        );

        // Open base colour drawer
        await page.click('#option-base');
        
        // Get all .wapf-swatch elements for base colour
        const swatches = await page.$$('.obj-base .wapf-swatch');
        
        // Filter swatches to only include visible ones (in case some are hidden based on other selections)
        const visibleSwatches = [];
        for (const swatch of swatches) {
            if (await swatch.isVisible()) visibleSwatches.push(swatch);
        }
        
        // Pick a random swatch
        const randomIndex = Math.floor(Math.random() * visibleSwatches.length);
        const randomSwatch = visibleSwatches[randomIndex];

        // Find the child radio button with name="base_colour" and check it
        const baseColourRadio = await randomSwatch.$('input[type="radio"][name="base_colour"]');
    
        await baseColourRadio.check();
        
        // Close config drawer
        await page.click('#configCloseButton');
        
        // Get the value of the selected radio button
        const baseColour = await baseColourRadio.getAttribute('value');
        
        // Check that base colour change is reflected in the URL with the correct label (spaces replaced with dashes and lowercased)
        await expect(page).toHaveURL(new RegExp(`base=${encodeURIComponent(baseColour)}`, 'i'));
        
        // Get product SKU from the DOM
        const productSku = (await page.locator('.sku').innerText()).toLowerCase();
        
        // Get current top and metal colours
        const topColour = await page.locator('.obj-top-colour input[name="top_colour"]:checked').getAttribute('value');
        
        // Open metals drawer
        await page.click('#option-metal-edge-veneer');
        
        // Get checked metal colour value from the DOM that accompany base colour selection
        const metalColour = await page.locator('.obj-metal-edge-veneer input[name="metal_edge_veneer"]:checked').getAttribute('value');
        
        // Close config drawer
        await page.click('#configCloseButton');
        
        // Gather selected layers from DOM
        const selectedLayers = {
        top: topColour,
        base: baseColour,
        metal: metalColour
        };

        // Get the expected composite image URL
        const expectedImageUrl = getCompositeImageUrl(productSku, selectedLayers);
        
        // Assert that status image is updated with the expected composite image URL
        const statusImage = page.locator('.status-image img');
        
        // Wait for the src attribute to match the expected value
        await expect(statusImage).toHaveAttribute('src', `${expectedImageUrl}-700.png`, { timeout: 15000 });
        
        // Gallery images update with valid options
        const galleryComposite = page.locator('.tm-gallery .composite-image img');
        
        // Wait for the src attribute of gallery images to match the expected value
        await expect(galleryComposite).toHaveAttribute('src', `${expectedImageUrl}-1600.png`, { timeout: 15000 });
    
    });

    test('User metal colour changes correctly update product @critical', async ({ page }) => {
        
        // Define the product URL
        const productUrl = '/product/phantom-edge-stadium/';
        
        // Visit the product page
        await page.goto(productUrl);
        
        // Wait for colour options to load before executing test
        await page.waitForResponse(resp =>
        resp.url().includes('/wp-json/tmpc/v1/colour-options?product_id=') && resp.status() === 200
        );
        
        
        // Open metal colour drawer
        await page.click('#option-metal-edge-veneer');
        
        // Get all .wapf-swatch elements for metal colour
        const swatches = await page.$$('.obj-metal-edge-veneer .wapf-swatch');
        
        // Filter swatches to only include visible ones
        const visibleSwatches = [];
        for (const swatch of swatches) {
        if (await swatch.isVisible()) visibleSwatches.push(swatch);
        }

        // Select a random swatch
        const randomIndex = Math.floor(Math.random() * visibleSwatches.length);
        const randomSwatch = visibleSwatches[randomIndex];
        
        // Find the child radio button with name="metal_edge_veneer" and check it
        const metalColourRadio = await randomSwatch.$('input[type="radio"][name="metal_edge_veneer"]');
        await metalColourRadio.check();
        
        // Close config drawer
        await page.click('#configCloseButton');
        
        // Get the value of the selected radio button
        const metalColour = await metalColourRadio.getAttribute('value');
        
        // Check that metal colour change is reflected in the URL with the correct label (spaces replaced with dashes and lowercased)
        await expect(page).toHaveURL(new RegExp(`veneer=${encodeURIComponent(metalColour)}`, 'i'));
        
        // Get product SKU from the DOM
        const productSku = (await page.locator('.sku').innerText()).toLowerCase();
        
        
        // Get current top and base colours
        const topColour = await page.locator('.obj-top-colour input[name="top_colour"]:checked').getAttribute('value');
        
        // Open base drawer
        await page.click('#option-base');
        
        // Get checked base colour value from the DOM that accompany metal colour selection
        const baseColour = await page.locator('.obj-base input[name="base_colour"]:checked').getAttribute('value');
        
        // Close config drawer
        await page.click('#configCloseButton');
        
        // Gather selected layers from DOM
        const selectedLayers = {
        top: topColour,
        base: baseColour,
        metal: metalColour
        };
        
        // Get the expected composite image URL
        const expectedImageUrl = getCompositeImageUrl(productSku, selectedLayers);
        
        // Assert that status image is updated with the expected composite image URL
        const statusImage = page.locator('.status-image img');
        
        // Wait for the src attribute to match the expected value
        await expect(statusImage).toHaveAttribute('src', `${expectedImageUrl}-700.png`, { timeout: 15000 });
        
        // Gallery images update with valid options
        const galleryComposite = page.locator('.tm-gallery .composite-image img');
        
        // Wait for the src attribute of gallery images to match the expected value
        await expect(galleryComposite).toHaveAttribute('src', `${expectedImageUrl}-1600.png`, { timeout: 15000 });
    
    });


        test('options changes are reflected in the URL and configure product on reload @critical', async ({ page }) => {
            
            // Define the product URL
            const productUrl = '/product/phantom-edge-stadium/';
            
            // Visit the product page
            await page.goto(productUrl);
            
            // Wait for colour options to load before executing test
            await page.waitForResponse(resp =>
            resp.url().includes('/wp-json/tmpc/v1/colour-options?product_id=') && resp.status() === 200
            );

            // Set model option
            await page.click('#option-model');
            
            // Get all option values
            const modelOptions = await page.$$('select[name="product-model-size"] option');
        
            // Select a random option
            const modelRandomIndex = Math.floor(Math.random() * modelOptions.length);
        
            // Get the value of the random option
            const modelValue = await modelOptions[modelRandomIndex].getAttribute('value');
        
            // Select the option
            await page.selectOption('select[name="product-model-size"]', { value: modelValue });
        
            // Close config drawer
            await page.click('#configCloseButton');

            // Set top colour
            await page.click('#option-top-colour');
            
            // Get all .wapf-swatch elements for top colour
            const topSwatches = await page.$$('.obj-top-colour .wapf-swatch');

            // Filter swatches to only include visible ones (in case some are hidden based on other selections)
            const visibleTopSwatches = [];
            for (const swatch of topSwatches) {
            if (await swatch.isVisible()) visibleTopSwatches.push(swatch);
            }

            // Pick a random visible swatch
            const topRandomIndex = Math.floor(Math.random() * visibleTopSwatches.length);
            const topRandomSwatch = visibleTopSwatches[topRandomIndex];

            // Find the child radio button with name="top_colour" and check it
            const topColourRadio = await topRandomSwatch.$('input[type="radio"][name="top_colour"]');

            // Check the radio button
            await topColourRadio.check();

            // Close config drawer
            await page.click('#configCloseButton');

            // Get the value of the selected radio button
            const topColour = await topColourRadio.getAttribute('value');

            // Set base colour (only visible swatches)
            await page.click('#option-base');
            
            // Get all .wapf-swatch elements for base colour
            const baseSwatches = await page.$$('.obj-base .wapf-swatch');
            
            // Filter swatches to only include visible ones (in case some are hidden based on other selections)
            const visibleBaseSwatches = [];
            for (const swatch of baseSwatches) {
            if (await swatch.isVisible()) visibleBaseSwatches.push(swatch);
            }
            
            // Pick a random visible swatch
            const baseRandomIndex = Math.floor(Math.random() * visibleBaseSwatches.length);
            
            // Get the random swatch
            const baseRandomSwatch = visibleBaseSwatches[baseRandomIndex];
            
            // Find the child radio button with name="base_colour" and check it
            const baseColourRadio = await baseRandomSwatch.$('input[type="radio"][name="base_colour"]');
            
            // Check the radio button
            await baseColourRadio.check();
            
            // Close config drawer
            await page.click('#configCloseButton');
            
            // Get the value of the selected radio button
            const baseColour = await baseColourRadio.getAttribute('value');

            // Set metal colour (only visible swatches)
            await page.click('#option-metal-edge-veneer');
            
            // Get all .wapf-swatch elements for metal colour
            const metalSwatches = await page.$$('.obj-metal-edge-veneer .wapf-swatch');
            
            // Filter swatches to only include visible ones (in case some are hidden based on other selections)     
            const visibleMetalSwatches = [];
            for (const swatch of metalSwatches) {
            if (await swatch.isVisible()) visibleMetalSwatches.push(swatch);
            }
            
            // Pick a random visible swatch
            const metalRandomIndex = Math.floor(Math.random() * visibleMetalSwatches.length);
            
            // Get the random swatch
            const metalRandomSwatch = visibleMetalSwatches[metalRandomIndex];
            
            // Find the child radio button with name="metal_edge_veneer" and check it
            const metalColourRadio = await metalRandomSwatch.$('input[type="radio"][name="metal_edge_veneer"]');
            
            // Check the radio button   
            await metalColourRadio.check();
            
            // Close config drawer
            await page.click('#configCloseButton');
            
            // Get the value of the selected radio button
            const metalColour = await metalColourRadio.getAttribute('value');

            // Assert all values are present in the URL
            await expect(page).toHaveURL(new RegExp(`model=${encodeURIComponent(modelValue)}`, 'i'));
            await expect(page).toHaveURL(new RegExp(`colour=${encodeURIComponent(topColour)}`, 'i'));
            await expect(page).toHaveURL(new RegExp(`base=${encodeURIComponent(baseColour)}`, 'i'));
            await expect(page).toHaveURL(new RegExp(`(metal|veneer)=${encodeURIComponent(metalColour)}`, 'i'));

            // Reload the page
            await page.reload();

            await expect(page.locator('select[name="product-model-size"]')).toHaveValue(modelValue);
            await expect(page.locator('.obj-top-colour input[name="top_colour"]:checked')).toHaveValue(topColour);
            await expect(page.locator('.obj-base input[name="base_colour"]:checked')).toHaveValue(baseColour);
            await expect(page.locator('.obj-metal-edge-veneer input[name="metal_edge_veneer"]:checked')).toHaveValue(metalColour);

        });

    test('top colour config drawer populated with correct values based on product type @critical', async ({ page }) => {
      
        // Define the product URL
        const productUrl = '/product/phantom-edge-stadium/';
      
        // Visit the product page
        await page.goto(productUrl);
      
        // Wait for colour options to load before executing test
        await page.waitForResponse(resp =>
            resp.url().includes('/wp-json/tmpc/v1/colour-options?product_id=') && resp.status() === 200
        );

        // Fetch colour options directly from the API
        const [response] = await Promise.all([
            page.waitForResponse(resp => resp.url().includes('/wp-json/tmpc/v1/colour-options?product_id=') && resp.status() === 200),
            page.reload()
        ]);
      
        // Parse the JSON response to get the colour options
        const apiData = await response.json();
        
        // Extract the top colour options from the API response
        const topColours = apiData.colour_options ? Object.keys(apiData.colour_options) : [];

        // Open the top colour config drawer
        await page.click('#option-top-colour');

        // Count the number of visible top colour swatches
        const swatches = await page.$$('.obj-top-colour .wapf-swatch');
        const visibleSwatches = [];
        for (const swatch of swatches) {
            if (await swatch.isVisible()) visibleSwatches.push(swatch);
        }

        // Assert the number of visible swatches matches the number of top colours from the API
        expect(visibleSwatches.length).toBe(topColours.length);

    });