import domReady from "@wordpress/dom-ready";

domReady(() => {
    const checkImages = () => {
        const images = document.querySelectorAll('.editor-styles-wrapper img');
        images.forEach(img => {
            const src = img.getAttribute('src');
            if (
                src &&
                !src.startsWith(document.location.origin) &&
                !img.classList.contains('external-image-warning')
            ) {
                img.classList.add('external-image-warning');
            }
        });
    };

    const waitForEditorWrapper = () => {
        const target = document.querySelector('.editor-styles-wrapper');
        if (target) {
            // Initial check
            checkImages();

            // Observe changes
            const observer = new MutationObserver(() => {
                checkImages();
            });

            observer.observe(target, {
                childList: true,
                subtree: true,
                attributes: true,
            });
        } else {
            // Retry after a short delay
            setTimeout(waitForEditorWrapper, 300);
        }
    };

    waitForEditorWrapper();
});
