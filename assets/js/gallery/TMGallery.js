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
    document.querySelectorAll('.tm-gallery, .tm-gallery-grid').forEach(galleryEl => {
        const gallery = new TailormadeGallery(galleryEl);
        gallery.init();
    });

    // Combine .status-image and .status-layer-img <img> elements into a single gallery instance, using the existing TailormadeGallery method
    const statusImageImgs = document.querySelectorAll('.status-image img');
    const statusLayerImgs = document.querySelectorAll('.status-layer-img img');
    const allImgs = [
        ...statusImageImgs,
        ...statusLayerImgs
    ];
    if (allImgs.length) {
        allImgs.forEach((el, i) => el.setAttribute('data-pswp-idx', i));
        const childrenSelector = allImgs.map((el, i) => `[data-pswp-idx="${i}"]`).join(', ');
        // Use the existing TailormadeGallery, but override children selector
        [
            ...new Set([
                ...Array.from(statusImageImgs).map(img => img.closest('.status-image')),
                ...Array.from(statusLayerImgs).map(img => img.closest('.status-layer-img'))
            ])
        ].filter(Boolean).forEach(container => {
            const gallery = new TailormadeGallery(container);
            gallery.initGallery = async function() {
                try {
                    const { default: PhotoSwipe } = await import('./photoswipe/photoswipe.esm.min.js');
                    const { default: PhotoSwipeLightbox } = await import('./photoswipe/photoswipe-lightbox.esm.min.js');
                    this.lightbox = new PhotoSwipeLightbox({
                        gallery: this.container,
                        children: childrenSelector,
                        pswpModule: () => PhotoSwipe,
                    });
                    this.lightbox.init();
                } catch (err) {
                    console.error('PhotoSwipe modules failed to load', err);
                }
            };
            gallery.init();
        });
    }
});