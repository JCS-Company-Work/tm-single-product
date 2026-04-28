class TailormadeGallery {

    constructor() {
        this.lightbox = null;
    }

    async initGallery() {

        try {
            // Dynamically import PhotoSwipe modules at runtime
            const { default: PhotoSwipe } = await import('./photoswipe/photoswipe.esm.min.js');
            const { default: PhotoSwipeLightbox } = await import('./photoswipe/photoswipe-lightbox.esm.min.js');

            this.lightbox = new PhotoSwipeLightbox({
                gallery: '.tm-gallery',
                children: 'li > a',
                pswpModule: () => PhotoSwipe,
            });

            this.lightbox.init();

        } catch (err) {
            console.error('PhotoSwipe modules failed to load', err);
        }

    }

    init() {
        this.initGallery();
    }
}

// Initialize gallery once DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const gallery = new TailormadeGallery();
    gallery.init();
});
