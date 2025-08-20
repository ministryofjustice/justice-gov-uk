import './navigation-secondary.html.twig';
import './navigation-secondary.scss';

export default function () {
    document
        .querySelector('.navigation-secondary')
        ?.addEventListener('click', ({ target }) => {
            // If the target does not have the button class `'.navigation-secondary__button'` do noting.
            if (!target.classList.contains('navigation-secondary__button')) {
                return;
            }

            const button = target;
            const controlId = button.getAttribute('aria-controls');
            const controlledElement = document.getElementById(controlId);

            if (!controlledElement) {
                return;
            }

            // Get the current state, so that the following actions don't go out of sync.
            const willOpen = button.getAttribute('aria-expanded') === 'false';

            // Determine the classList method to use based on whether we are opening or closing.
            // Avoid using toggle, since it can cause the class and aria attributes to get out of sync.
            const classListMethod = willOpen ? 'add' : 'remove';

            // Set the aria-expanded attribute on the button - this applies to both the main nav and sublists.
            button.setAttribute('aria-expanded', willOpen);

            // Handle toggling of the nav for small screens, i.e. the menu button has been clicked.
            if (controlId === 'navigation-secondary') {
                // Toggle the open class on the nav element.
                controlledElement.classList[classListMethod](
                    'navigation-secondary__nav--open'
                );

                // Update the text content of the button's visually hidden span.
                button.querySelector('.visually-hidden').textContent = willOpen
                    ? 'Close secondary '
                    : 'Open secondary ';

                // Return early, since we are only toggling the main nav.
                return;
            }

            // If we are here, we are toggling a sublist.

            // Toggle the open class on the controlled element.
            controlledElement.classList[classListMethod](
                'navigation-secondary__sublist--open'
            );

            // Update the aria-label of the button.
            button.setAttribute(
                'aria-label',
                willOpen ? 'Show less' : 'Show more'
            );
        });
}
