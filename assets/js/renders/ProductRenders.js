import * as THREE from './three-js/build/three.module.js';
import { OrbitControls } from './three-js/examples/jsm/controls/OrbitControls.js';
import { ShadowMapViewer } from './three-js/examples/jsm/utils/ShadowMapViewer.js';
import { MTLLoader } from './three-js/examples/jsm/loaders/MTLLoader.js';
import { OBJLoader } from './three-js/examples/jsm/loaders/OBJLoader.js';

class ProductRenders {

    constructor(containerSelector) {
        // Main container where the 3D scene will render
        this.container = document.querySelector(containerSelector);
        if (!this.container) {
            console.error(`[ProductRenders] Container not found for selector: ${containerSelector}`);
        }

        // Object to hold preloaded textures for materials
        this.queryString = '';
        
        // Track if camera animation has already run
        this.cameraAnimated = false;

        // Adjust camera settings based on screen size
        if (window.innerWidth < 768) {
            this.adjustment = 50;
        } else {
            this.adjustment = 40;
        }

        // Determine texture name from container attribute
        this.textureName = this.container ? this.container.getAttribute('item-name') : '';
        if (!this.textureName) {
            console.error('[ProductRenders] textureName is missing or empty. Check that the container has the item-name attribute.');
            this.textureName = 'default-model'; // fallback
        }

        // Assign shadow image and camera adjustment based on model type
        if (this.textureName.includes('tt02')) {
            this.shadowName = "shadow-tt02.jpg";
            this.adjustment = (window.innerWidth < 768) ? 35 : 29;
        } else if (this.textureName.includes('tt03')) {
            this.shadowName = "shadow-tt03.jpg";
        } else if (this.textureName.includes('tt12')) {
            this.shadowName = "shadow-tt12.jpg";
        } else {
            this.shadowName = "shadow-tt04.jpg";
        }

        // Core 3D properties
        this.loadedModel = null;
        this.scene = null;
        this.defaults = {};
        this.isAnimating = false;

        // Fullscreen toggle button
        this.toggleButton = document.querySelector('.obj3dviewer-toggle');

        // Bind event handler methods to maintain correct `this` context
        this.toggleHandler = this.toggleFullscreen.bind(this);
        this.fullscreenChangeHandler = this.onFullscreenChange.bind(this);

        // Initialize event listeners (fullscreen, toggle button)
        this.initEventListeners();
    }

    // Fades out loading screen when the 3D scene is ready
    fadeLoading() {
        const loadingScreen = document.getElementById('loading-screen');
        if (!loadingScreen) return;

        loadingScreen.classList.add('fade-out');
        loadingScreen.addEventListener('transitionend', function handler(e) {
            if (e.propertyName === 'opacity') {
                loadingScreen.remove();
            }
        });

        // Safety fallback in case transitionend doesn't fire
        setTimeout(() => {
            if (document.body.contains(loadingScreen)) {
                loadingScreen.remove();
            }
        }, 3000);
    }

    // Sets up listeners for fullscreen toggle and changes
    initEventListeners() {
        if (this.toggleButton) {
            this.toggleButton.addEventListener('click', this.toggleHandler);
            this.toggleButton.addEventListener('touchstart', this.toggleHandler);
        }
        document.addEventListener('fullscreenchange', this.fullscreenChangeHandler);
        document.addEventListener('webkitfullscreenchange', this.fullscreenChangeHandler);
        document.addEventListener('msfullscreenchange', this.fullscreenChangeHandler);

        // Listen for custom event when colour options change
        window.addEventListener('colourOptionsChanged', (e) => {
            this.updateColourOptions(e);
        });
    }

    /**
     * Update the model's colour options from colour default rules in colourOptions.js
     * Function triggered by the custom 'colourOptionsChanged' event dispatched from 
     * the colour options module when a swatch is clicked or on initial load
     * @param {object} e 
     */
    updateColourOptions(e) {

        // Mapping of layer keys from event to our defaults structure
        const layerMap = {
            top: 'colour',
            metal: 'metalcolour',
            base: 'secondcolour',
            meshcolour: 'meshcolour',
            profilecolour: 'profilecolour',
            undercolour: 'undercolour'
        }

        // Extract the defaults object from the event details
        const layerValues = e.detail.defaults;
console.log('Received colour options update:', layerValues);
        // Build an update object based on the event details and our mapping
        const update = {};

        // Iterate over expected keys and map them to our defaults structure
        for (const [layer, data] of Object.entries(layerValues)) {

            // layer will be 'top', 'base', 'metal', etc.
            // data will be the object for that layer (e.g., { filename, swatchName })
            if (data && data.filename) {

                // Update the corresponding entry in the update object with the cleaned filename
                update[layerMap[layer]] = data.filename.replace(/\s+/g, '-').toLowerCase();
                this.updateURL({ [layerMap[layer]]: data.swatchName });

                // If base, also set secondcolourname as required by mtl.php for the base colour name to show in the UI
                if (layer === 'base' && data.swatchName) {
                    update['secondcolourname'] = data.swatchName;
                }
                
                update['meshcolour'] = 'meshcolour';
                update['profilecolour'] = 'profilecolour';
                update['undercolour'] = 'undercolour';

            } else {
                // These are the fallback values for layers that don't have swatches (mesh, profile, undercolour)
                // Colours are set on the fly in mtl.php
                update[layerMap[layer]] = data;
            }

        }

        // Build query string from the update object and update our defaults
        this.queryString = this.buildQueryString(update);

        // Reload the model to reflect the new colour options
        this.loadModel();

    }

    // Builds a query string from defaults for backend calls
    buildQueryString(update = {}) {

        // Update the defaults with the new values from the update object
        Object.assign(this.defaults, update);

        // Build query string parts from the updated defaults
        const queryParts = [];

        // Loop through the defaults and create query parameters for non-empty values
        for (const [key, value] of Object.entries(this.defaults)) {
            // Only include keys that have non-empty values
            if (value !== '') {
                // Encode both key and value to ensure special characters are handled correctly
                queryParts.push(`${encodeURIComponent(key)}=${encodeURIComponent(value)}`);
            }
        }
        // Add plugin version for cache busting from data attribute
        const version = this.container?.dataset.version || '1.0.0';
        queryParts.push(`fileversion=${encodeURIComponent(version)}`);

        // Join the query parts with '&' and prepend with '?' to form the full query string
        return '?' + queryParts.join('&');
    }

    // Sets event listeners for color swatches to update the model dynamically
    attachSwatchListeners() {

        // Mapping of field labels to our query parameter keys
        const map = {
            'Top Colour': 'colour',
            'Metal Edge Veneer': 'metalcolour',
            'Base': 'secondcolour'
        };

        // Select all swatch group containers on the page
        const swatchGroups = document.querySelectorAll('.obj-base .wapf-image-swatch-wrapper, .obj-metal-edge-veneer .wapf-image-swatch-wrapper');

        // Loop through each swatch group
        swatchGroups.forEach(swatchGroup => {

            // Find all individual swatches inside this group
            const swatches = swatchGroup.querySelectorAll('.wapf-swatch');

            // Loop through each swatch inside the group
            swatches.forEach(swatch => {

                // Add a click event listener for when the user clicks on a swatch
                swatch.addEventListener('click', () => {

                    // Get the field label text related to this swatch group
                    const fieldLabel = swatchGroup.parentElement?.parentElement
                        ?.querySelector('.wapf-field-label label span')
                        ?.textContent;

                    // Extract swatch name from label    
                    const swatchName = swatch.querySelector('label').textContent;

                    // Get the image source URL from the clicked swatch
                    const imgSrc = swatch.querySelector('img')?.src;

                    // Extract a clean identifier from the image URL using a regex
                    // Matches the part after "uploads/" and before "-{width}x{height}.jpg"
                    const match = imgSrc?.match(/uploads\/(.+?)-\d+x\d+\.jpg/);
                    const result = match ? match[1] : null;

                    // If we have a valid result AND a corresponding map entry for the fieldLabel
                    if (result && map[fieldLabel]) {

                        // Build a query string from the mapped key and extracted result
                        // Example: { material: "calacatta-gold" }
                        this.queryString = this.buildQueryString({ [map[fieldLabel]]: result });

                        // Reload or update the model based on new swatch selection
                        this.loadModel();
                    }

                    // Update the URL in the browser to reflect selected swatches
                    this.updateURL({ [map[fieldLabel]]: swatchName });

                });
            });
        });
    }

    updateModel() {

        // Get model select element from DOM
        const modelSelect = document.querySelector('.obj-model select');

        // Listen for changes
        modelSelect.addEventListener('change', () => {

            // Extract selected model size
            const selectedOption = modelSelect.options[modelSelect.selectedIndex];
            const label = selectedOption.getAttribute('data-label');

            // Update URL with new model size
            this.updateURL({'model': label});

        });

    }

    updateURL(params = {}) {

        const map = {
            'colour': 'colour',
            'metalcolour': 'veneer',
            'secondcolour': 'base',
            'model': 'model'
        };

        // Get current URL object
        const url = new URL(window.location.href);

        // Loop over params and update URL
        for (const [key, value] of Object.entries(params)) {

            if (value) {

                // Clean value: remove "swatch" or "banding"
                let cleanedValue = value.replace(/(swatch-|banding-)/gi, '').trim();

                if (cleanedValue) {
                    // Capitalize first letter of each word
                    cleanedValue = cleanedValue
                        .split(/[\s-]+/) // split by spaces or hyphens
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                        .join(' ');

                    // Encode properly with %20
                    const encodedValue = encodeURIComponent(cleanedValue);

                    // Add or update parameter manually
                    url.searchParams.set(map[key], encodedValue);

                } else {

                    url.searchParams.delete(map[key]);

                }

            } else {

                url.searchParams.delete(map[key]);

            }

        }

        // Manually rebuild query string to prevent + for spaces
        let queryString = '';
        url.searchParams.forEach((val, key) => {
            queryString += `${key}=${val}&`;
        });
        queryString = queryString.slice(0, -1); // remove trailing &

        // Build new URL
        const newUrl = `${url.origin}${url.pathname}${queryString ? '?' + queryString : ''}`;

        // Update browser URL without reload
        window.history.replaceState({}, '', newUrl);

        // Update QR code
        this.updateQRCode(new URLSearchParams(queryString));

    }

    // Update QR code with latest url post model change
    updateQRCode(params) {

        const qrcodeEl = document.querySelector('.qrcode');
        if (!qrcodeEl) return;

        // Build full URL using current location
        const url = new URL(window.location.href);

        // Replace search params with the provided params (if passed)
        if (params instanceof URLSearchParams) {
            url.search = params.toString();
        }

        // Only keep allowed params
        const paramsToInclude = ['model', 'colour', 'veneer', 'base'];
        // Collect keys to delete to avoid mutation during iteration
        const keysToDelete = [];
        url.searchParams.forEach((value, key) => {
            if (!paramsToInclude.includes(key)) {
                keysToDelete.push(key);
            }
        });

        // Delete unwanted params
        keysToDelete.forEach(key => url.searchParams.delete(key));

        // Set title attribute for debugging or hover text
        qrcodeEl.setAttribute('title', url.toString());

        // Clear existing QR code
        qrcodeEl.innerHTML = '';

        // Generate QR code
        new QRCode(qrcodeEl, {
            text: url.toString(),
            width: 128,
            height: 128
        });
    }

    // Initializes 3D scene and camera
    initScene() {
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0xffffff);
        this.camera = new THREE.PerspectiveCamera(
            this.adjustment || 40,
            this.container.clientWidth / this.container.clientHeight,
            1,
            100
        );
        this.camera.position.set(25, 24, -25);
    }

    // Creates WebGL renderer and attaches it to the container
    initRenderer() {
        this.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        const maxPixelRatio = 1.5;
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, maxPixelRatio));
        this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
        this.renderer.shadowMap.enabled = true;
        this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        this.container.appendChild(this.renderer.domElement);
    }

    // Adds lights to the scene
    initLights() {
        this.scene.add(new THREE.AmbientLight(0xffffff, 2));
        // Spotlights and directional lights for realistic shadows
        this.spotLight = new THREE.SpotLight(0xffffff, 250);
        this.spotLight.angle = Math.PI / 15;
        this.spotLight.penumbra = 0.4;
        this.spotLight.castShadow = true;
        this.spotLight.position.set(0, 25, 20);
        this.spotLight.shadow.camera.near = 8;
        this.spotLight.shadow.camera.far = 36;
        this.spotLight.shadow.mapSize.set(256, 256);
        this.scene.add(this.spotLight);
        this.scene.add(this.spotLight.target);
        this.spotLight.target.position.set(1, 0, -25);

        // Additional lights
        this.dirLight = new THREE.SpotLight(0xffffff, 5000);
        this.dirLight.position.set(35, 1, 22);
        this.dirLight.castShadow = true;
        this.dirLight.penumbra = 0.1;
        this.dirLight.angle = Math.PI / 12;
        this.dirLight.shadow.camera.near = 3;
        this.dirLight.shadow.camera.far = 100;
        this.dirLight.shadow.mapSize.set(512, 512);
        this.scene.add(this.dirLight);
        this.scene.add(this.dirLight.target);
        this.dirLight.target.position.set(0, 11, 0);

        this.dirLight2 = new THREE.SpotLight(0xffffff, 100);
        this.dirLight2.position.set(-15, 8, 8);
        this.dirLight2.angle = Math.PI / 6;
        this.dirLight2.penumbra = 0.1;
        this.dirLight2.castShadow = true;
        this.scene.add(this.dirLight2);
        this.scene.add(this.dirLight2.target);
        this.dirLight2.target.position.set(0, 8, 0);

        this.dirLight3 = new THREE.SpotLight(0xffffff, 500);
        this.dirLight3.position.set(-10, 6, -15);
        this.dirLight3.angle = Math.PI / 4;
        this.dirLight3.penumbra = 0.5;
        this.dirLight3.castShadow = true;
        this.scene.add(this.dirLight3);
        this.scene.add(this.dirLight3.target);
        this.dirLight3.target.position.set(0, 10, 0);

        this.rectLight = new THREE.SpotLight(0xffffff, 1500);
        this.rectLight.position.set(-25, 29, -75);
        this.rectLight.lookAt(new THREE.Vector3(0, 12, 0));
        this.scene.add(this.rectLight);
        this.scene.add(this.rectLight.target);
    }

    // Initializes shadow map debugging tools
    initShadowMapViewers() {
        this.dirLightShadowMapViewer = new ShadowMapViewer(this.dirLight);
        this.spotLightShadowMapViewer = new ShadowMapViewer(this.spotLight);
    }

    // Sets up OrbitControls for camera interaction and zoom toggling
    initControls() {
        this.controls = new OrbitControls(this.camera, this.renderer.domElement);
        this.controls.target.set(0, 5, 0);
        this.controls.minPolarAngle = 0;
        this.controls.maxPolarAngle = Math.PI / 2;
        this.controls.minDistance = 15;
        this.controls.maxDistance = 80;
        this.controls.enableZoom = false;
        this.controls.enableDamping = true;
        this.controls.dampingFactor = 0.07;
        this.controls.rotateSpeed = 0.5;
        this.controls.update();

        this.zoomActivated = false;
        const canvas = this.renderer.domElement;

        // Prevent accidental zoom gestures initially
        this.blockZoom = (e) => { if (e.ctrlKey) e.preventDefault(); };
        this.blockGesture = (e) => e.preventDefault();
        this.blockTouchZoom = (e) => { if (e.touches && e.touches.length > 1) e.preventDefault(); };

        // Prevent double-tap zoom
        let lastTap = 0;
        this.blockDoubleTap = (e) => {
            const currentTime = new Date().getTime();
            const tapLength = currentTime - lastTap;
            if (tapLength < 350 && tapLength > 0) e.preventDefault();
            lastTap = currentTime;
        };

        // Attach event blocking initially
        canvas.addEventListener('wheel', this.blockZoom, { passive: false });
        canvas.addEventListener('gesturestart', this.blockGesture, { passive: false });
        canvas.addEventListener('touchmove', this.blockTouchZoom, { passive: false });
        canvas.addEventListener('touchstart', this.blockDoubleTap, { passive: false });

        // Enable zoom on first click/touch
        this.handleActivate = (e) => {

            if (!this.zoomActivated) {
                this.controls.enableZoom = true;
                this.zoomActivated = true;

                canvas.removeEventListener('wheel', this.blockZoom, { passive: false });
                canvas.removeEventListener('gesturestart', this.blockGesture, { passive: false });
                canvas.removeEventListener('touchmove', this.blockTouchZoom, { passive: false });
                canvas.removeEventListener('touchstart', this.blockDoubleTap, { passive: false });
                canvas.removeEventListener('click', this.handleActivate);
                canvas.removeEventListener('touchstart', this.handleActivate);
            }
        };

        canvas.addEventListener('click', this.handleActivate);
        canvas.addEventListener('touchstart', this.handleActivate);

        // Reset zoom when mouse leaves
        canvas.addEventListener('mouseleave', () => {
            this.controls.enableZoom = false;
            this.zoomActivated = false;
            canvas.addEventListener('wheel', this.blockZoom, { passive: false });
            canvas.addEventListener('gesturestart', this.blockGesture, { passive: false });
            canvas.addEventListener('touchmove', this.blockTouchZoom, { passive: false });
            canvas.addEventListener('touchstart', this.blockDoubleTap, { passive: false });
            canvas.addEventListener('click', this.handleActivate);
            canvas.addEventListener('touchstart', this.handleActivate);
        });
    }

    // Creates ground plane with shadow texture
    loadGround() {
        const texPath = '/wp-content/plugins/tm-product-configurator/assets/js/renders/three-js/examples/models/obj/textures/';
        const url = texPath + this.shadowName;

        const texture = this.preloadedTextures && this.preloadedTextures['groundshadow']
            ? this.preloadedTextures['groundshadow']
            : new THREE.TextureLoader().load(url);

        texture.center = new THREE.Vector2(0.5, 0.5);
        texture.rotation = - Math.PI / 4;
        texture.repeat.set(0.70, 0.70);

        const geometry = new THREE.BoxGeometry(45, 0.75, 45);
        const material = new THREE.MeshBasicMaterial({
            map: texture,
            color: 0xf7f7f7,
            combine: 0
        });

        const ground = new THREE.Mesh(geometry, material);
        ground.receiveShadow = true;
        ground.position.set(0, 0, 0);
        ground.rotation.y = Math.PI / 4;
        this.scene.add(ground);
    }

    // Loads the OBJ model and applies preloaded materials
    loadModel() {

        // Base path for model files
        const basePath = '/wp-content/plugins/tm-product-configurator/assets/js/renders/three-js/examples/models/obj/';

        // Defensive: check for required properties
        if (!this.textureName) {
            console.error('[ProductRenders] Cannot load model: textureName is missing.');
            return;
        }
        if (!this.scene) {
            console.error('[ProductRenders] Cannot load model: scene is not initialized.');
            return;
        }

        // Load material file first, then load the OBJ model with the materials applied
        const mtlLoader = new MTLLoader().setPath(basePath);
        mtlLoader.setCrossOrigin('anonymous');
        const mtlUrl = `mtl.php${this.queryString}`;
        console.log(`[ProductRenders] Loading MTL file from URL: ${mtlUrl}`);
        mtlLoader.load(mtlUrl, (materials) => {
            if (!materials) {
                console.error('[ProductRenders] MTLLoader failed to load materials.');
                return;
            }
            materials.preload();

            const objLoader = new OBJLoader().setMaterials(materials).setPath(basePath);
            const objUrl = `${this.textureName}-obj.php${this.queryString}`;
            objLoader.load(objUrl, (object) => {
                // Remove previous model if it exists
                if (this.loadedModel) {
                    this.scene.remove(this.loadedModel);
                    this.loadedModel.traverse((child) => {
                        if (child.geometry) child.geometry.dispose();
                        if (child.material) {
                            if (Array.isArray(child.material)) {
                                child.material.forEach(m => m.dispose());
                            } else {
                                child.material.dispose();
                            }
                        }
                    });
                }

                object.position.y = 4.5;
                object.scale.setScalar(0.1);

                // Apply textures to materials
                object.traverse(node => {
                    if (node.isMesh && node.material && node.material.name) {
                        if (this.preloadedTextures && this.preloadedTextures[node.material.name]) {
                            node.material.map = this.preloadedTextures[node.material.name];
                            node.material.needsUpdate = true;
                        } else {
                            console.warn(`[ProductRenders] Texture not found for material: ${node.material.name}`);
                        }
                    }
                    if (node.isMesh) node.castShadow = true;
                });

                this.scene.add(object);
                this.loadedModel = object;

                // Animate camera on first model load
                if (!this.cameraAnimated) {
                    gsap.to(this.camera.position, {
                        x: 0,
                        y: 14,
                        z: 30,
                        duration: 2.5,
                        ease: "back.inOut"
                    });
                    this.cameraAnimated = true;
                }
            }, (xhr) => {
                if (xhr.lengthComputable) {
                    const percent = (xhr.loaded / xhr.total) * 100;
                    console.log(`${percent.toFixed(2)}% downloaded`);
                }
            }, (err) => {
                console.error('[ProductRenders] OBJLoader failed to load model:', objUrl, err);
            });
        }, (err) => {
            if (err && err.type === 'progress') {
                console.error(`[ProductRenders] MTLLoader failed to load: ProgressEvent for URL: ${mtlUrl}`, err);
            } else {
                console.error(`[ProductRenders] MTLLoader failed to load: ${mtlUrl}`, err);
            }
        });
    }

    // Main render loop
    animate = () => {
        requestAnimationFrame(this.animate);
        if (this.controls) {
            this.controls.update();
        }
        this.renderer.render(this.scene, this.camera);
    };

    // Handles fullscreen toggle
    toggleFullscreen() {
        const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
        const isFullscreen =
            document.fullscreenElement ||
            document.webkitFullscreenElement ||
            document.msFullscreenElement;

        if (isMobile) {
            this.container.classList.toggle('fullscreen-active');
        } else {
            if (!isFullscreen) {
                this.container.requestFullscreen().catch(() => {
                    this.container.classList.toggle('fullscreen-active');
                });
            } else {
                document.exitFullscreen();
            }
        }
    }

    // Removes fullscreen-active class when exiting fullscreen
    onFullscreenChange() {
        const isFullscreen = !!document.fullscreenElement;
        if (!isFullscreen) {
            this.container.classList.remove('fullscreen-active');
        }
    }

    // Updates renderer and camera when container size changes
    onResize() {
        this.camera.aspect = this.container.clientWidth / this.container.clientHeight;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
    }

    // Preloads textures before initializing the scene
    preloadTextures(urls) {
        const loader = new THREE.TextureLoader();
        loader.setCrossOrigin('anonymous');
        const texturePromises = urls.map(url =>
            new Promise((resolve, reject) => {
                loader.load(
                    url,
                    texture => {
                        resolve({ url, texture });
                    },
                    undefined,
                    error => {
                        reject({ url, error });
                    }
                );
            })
        );
        return Promise.all(texturePromises).then(results => {
            return results.reduce((acc, curr) => {
                acc[curr.url] = curr.texture;
                return acc;
            }, {});
        });
    }

    // Initializes the entire rendering process
    init() {
        
        // Initial values for textures based on the default swatches selected in the UI
        const initialValues = {"colour":"swatch-macchia-vecchia","metalcolour":"banding-brushed-gold","secondcolour":"swatch-macchia-vecchia"};

        const texPath = '/wp-content/plugins/tm-product-configurator/assets/js/renders/three-js/examples/models/obj/textures/';
        const version = '?ver=223';
        // Collect texture URLs from global ProductRendersData
        const textureKeys = Object.keys(initialValues);
        const textureURLsByKey = {};
        textureKeys.forEach(key => {
            const imageName = initialValues?.[key];
            if (imageName) {
                textureURLsByKey[key] = texPath + imageName + '.jpg' + version;
            }
        });

        this.preloadTextures(Object.values(textureURLsByKey))
            .then(texturesByUrl => {
                this.preloadedTextures = {};
                for (const [key, url] of Object.entries(textureURLsByKey)) {
                    this.preloadedTextures[key] = texturesByUrl[url];
                }
                console.log("Preloaded textures:", this.preloadedTextures);
                // Initialize scene and related components once
                if (!this.scene) {
                    this.initScene();
                    this.initRenderer();
                    this.initLights();
                    this.initShadowMapViewers();
                    this.initControls();
                    this.loadGround();
                    window.addEventListener('resize', () => this.onResize());
                    this.attachSwatchListeners();
                    this.updateModel();
                    if (!this.isAnimating) {
                        this.isAnimating = true;
                        this.animate();
                    }
                }
                this.loadModel();
                this.fadeLoading();
            })
            .catch(err => {
                console.warn('Failed to preload one or more images:', err);

                if (!this.scene) {
                    this.initScene();
                    this.initRenderer();
                    this.initLights();
                    this.initShadowMapViewers();
                    this.initControls();
                    this.loadGround();
                    window.addEventListener('resize', () => this.onResize());
                    this.attachSwatchListeners();
                    this.updateModel();
                    if (!this.isAnimating) {
                        this.isAnimating = true;
                        this.animate();
                    }
                }
                this.loadModel();
                this.fadeLoading();
            });
    }
}

// Init class when DOM is ready
document.addEventListener('DOMContentLoaded', () => {

  // Create a new ProductRenders instance targeting the container with ID 'obj3dviewer'
  const viewer = new ProductRenders('#obj3dviewer');

  // Initialize the viewer (sets up scene, loads model, etc.)
  viewer.init();

});