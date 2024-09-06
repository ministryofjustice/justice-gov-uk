import './sidebar-block.html.twig';
import './sidebar-block.scss';

export default function () {
    // Exclude the brand variant as it doesn't open/close
    const els = document.querySelectorAll(
        '.sidebar-block:not(.sidebar-block--brand)'
    );

    if (!els) {
        return;
    }

    els.forEach((el) => {
        const button = el.querySelector('.sidebar-block__heading-button');
        const content = el.querySelector('.sidebar-block__content');
        button.addEventListener('click', () => {
            content.classList.toggle('sidebar-block__content--open');
            button.setAttribute(
                'aria-expanded',
                !(button.getAttribute('aria-expanded') === 'true')
            );
        });

        const searchForm = el.querySelector('.sidebar-block__search-filter');

        // Clear all inputs when the reset button is clicked
        if (searchForm) {
            const resetButton = searchForm.querySelector('input[type="reset"]');
            resetButton.addEventListener('click', (e) => {
                e.preventDefault();
                const inputs = searchForm.querySelectorAll(
                    'input[type="radio"], input[type="checkbox"]'
                );
                inputs.forEach((input) => {
                    input.checked = false;
                });
            });
        }
    });
}
