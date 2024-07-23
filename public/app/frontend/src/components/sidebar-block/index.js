import './sidebar-block.html.twig';
import './sidebar-block.scss';

export default function() {
    const els = document.querySelectorAll('.sidebar-block');

    // If active, open child sublist by default - use atr to pass info on
    // Add arrow similar to in main nav to indicate?

    // Layout columns. Add 8 margin top to all children except first. Remove this from individual components? Otherwise image won't work in second sidebar.

    if (!els) {
        return;
    }

    els.forEach((el) => {
        const button = el.querySelector('.sidebar-block__show-more');
        const content = el.querySelector('.sidebar-block__content');
        button.addEventListener('click', () => {
            content.classList.toggle('sidebar-block__content--open')
            button.setAttribute('aria-expanded', !(button.getAttribute('aria-expanded') === 'true'))
        })
    })
}