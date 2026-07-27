class TailormadeGallery {
    constructor(container) {
        this.container = container;
        this.lightbox = null;
    }

    async initGallery() {
        try {
            const { default: PhotoSwipe } = await import('./photoswipe/photoswipe.esm.min.js');
            const { default: PhotoSwipeLightbox } = await import('./photoswipe/photoswipe-lightbox.esm.min.js');

            this.lightbox = new PhotoSwipeLightbox({
                gallery: this.container,
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

// Initialize all galleries on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.tm-gallery, .tm-gallery-grid, .current-status-wrapper').forEach(galleryEl => {
        const gallery = new TailormadeGallery(galleryEl);
        gallery.init();
    });
});