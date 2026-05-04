class BuildPDF {

    constructor() {

        // Class property to hold model size
        this.currentModel = null;

        // Variable to hold SKU value
        this.sku = this.getSKU();

        // Initialize an empty array to hold PDF elements
        this.elsToAdd = [];

        // Attach click handler for PDF generation
        this.generatePDF();

    }

    /**
     * Method to create layout to be turned into PDF, send to Puppeteer and trigger download on success
     */
    generatePDF = () => {
    
        const pdfButton = document.getElementById("make-pdf");
        if (!pdfButton) return;

        pdfButton.addEventListener("click", async (event) => {
            event.preventDefault();
            pdfButton.classList.add('pdf-working');

            this.getCurrentModel();
            const productPage = document.querySelector(".current-status");
            if (!productPage) {
                pdfButton.classList.remove('pdf-working');
                return;
            }

            const pdfName = this.sku || 'Product';
            let pdfWrapper;

            try {

                // Clone the entire product page
                pdfWrapper = this.buildPDF(productPage.cloneNode(true));

                // Remove all wapf-layer-image elements that are display: none
                pdfWrapper.querySelectorAll('.wapf-layer-image').forEach(img => {
                    if (img.style.display === 'none') {
                        img.remove();
                    }
                });

                // Append PDF wrapper to bottom of the page for Puppeteer to render
                pdfWrapper.style.position = 'relative';
                pdfWrapper.style.width = '100%';
                pdfWrapper.style.height = '100%';   // full PDF height
                pdfWrapper.style.background = '#fff';
                pdfWrapper.style.visibility = 'visible';

                // Force it to be visible for inspection
                // pdfWrapper.style.position = 'fixed';
                // pdfWrapper.style.top = '50px';
                // pdfWrapper.style.left = '50px';
                // pdfWrapper.style.width = '800px';
                // pdfWrapper.style.height = '1000px';
                // pdfWrapper.style.background = '#fff';
                // pdfWrapper.style.zIndex = '999999';
                // pdfWrapper.style.visibility = 'visible';
                // pdfWrapper.style.display = 'block';
                // pdfWrapper.style.border = '4px solid red';
                // pdfWrapper.style.overflow = 'auto';

                document.body.appendChild(pdfWrapper);

                // Wait for layout/paint
                await new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r)));

                // Construct HTML for Puppeteer
                const html = `
                        <html>
                            <head>
                                <meta charset="UTF-8">
                                <link rel="stylesheet" href="https://fast.fonts.net/cssapi/939a4cc7-4305-49d3-9eb7-d6746fdc66d3.css">
                                <link rel="stylesheet" href="https://pdf.store.tailormade.uk/static/pdf-styles.css">
                            </head>
                            <body>${pdfWrapper.outerHTML}</body>
                        </html>
                    `;

                // Send to Puppeteer server
                const response = await fetch('https://pdf.store.tailormade.uk/generate-pdf', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ html, pdfName })
                });

                if (!response.ok) throw new Error(`PDF request failed: ${response.status}`);
                const blob = await response.blob();
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `${pdfName}.pdf`;
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);

            } catch (err) {
                console.error('PDF generation failed:', err);
            } finally {
                pdfButton.classList.remove('pdf-working');
                if (document.body.contains(pdfWrapper)) pdfWrapper.remove();
            }
        });
    };

    /** Method to find current model from content-area div */
    getCurrentModel() {

        // Select content area div
        const contentArea = document.querySelector('.content-area');

        // Loop over classes and find our model- class
        const modelClass = Array.from(contentArea.classList).find(cls => cls.startsWith('model-'));

        if (modelClass) {

            // Set currentModel property with current value
            this.currentModel = modelClass;

        }

    }

    /**
     * Save SKU value globally
     */
    getSKU() {

        const skuElement = document.querySelector('.sku');

        return skuElement.textContent.trim();

    }

    /**
     * Build and return the wrapper containing all content for the PDF
     */
    buildPDF = (productPage) => {
        // Reset the array for a fresh build
        this.elsToAdd = [];

        // Remove any previously generated wrapper
        const existingPDF = document.querySelector('.pdf-class');
        if (existingPDF) existingPDF.remove();

        // Create the wrapper element
        const pdfWrapper = document.createElement('div');
        pdfWrapper.classList.add('pdf-class');

        // Add sections to the PDF
        this.addBanner();
        this.addProductData(productPage);
        this.addContactDetails();

        // Append all collected elements to the wrapper
        this.elsToAdd.forEach(el => pdfWrapper.appendChild(el));

        return pdfWrapper;
    };

    addProductData(productPage) {

        this.elsToAdd.push(productPage.cloneNode(true));

    }
    
     /**
     * Add TailorMade Banner
     */
    addBanner = () => {

        // Create banner image element
        const banner = document.createElement('img');

        // Uploads folder path
        const uploads = 'https://store.tailormade.uk/wp-content/uploads/';

        // Object containing banner images
        const bannerMap = {
            'tt02': 'tt02-pdf-banner.jpg',
            'tt04': 'tt04-pdf-banner.jpg',
            'tt12': 'tt12-pdf-banner.jpg',
        };

        // Find a key that exists in the SKU
        const match = Object.keys(bannerMap).find(key => this.sku.includes(key));

        // Default to tt03 if no match
        const bannerMatch = bannerMap[match] || 'tt03-pdf-banner.jpg';

        // Set banner src string
        banner.src = uploads + bannerMatch

        // Add pdf-class to banner image element
        banner.classList.add('pdf-banner');

        // Add banner image to els to be included in PDF
        this.elsToAdd.push(banner);

    };

    /**
     * Method to add contact details in footer area
     */
    addContactDetails() {

        // Select email element from DOM
        const salesEmail = document.querySelector('.sales-email');

        // Select telephone number from DOM
        const telNo = document.querySelector('.tel-no');

        // Select address from DOM
        const factoryAddress = document.querySelector('.factory-address').innerText;

        // Extract and clean text
        const salesEmailText = salesEmail?.innerText.trim() || '';
        const telNoText = telNo?.innerText.trim() || '';
        const oneLineAddress = factoryAddress
        .replace(/\s*\n\s*/g, ' ')
        .replace(/\s+/g, ' ')
        .trim() || '';

        // Join with pipes
        const combinedString = [salesEmailText, telNoText, oneLineAddress].join(' | ');

        // Wrap in a <p> node
        const contactNode = document.createElement('p');
        contactNode.classList.add('pdf-footer');
        contactNode.textContent = combinedString;

        // Add to final array
        this.elsToAdd.push(contactNode);

    }

}

// Initialize the class when the DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new BuildPDF();
});