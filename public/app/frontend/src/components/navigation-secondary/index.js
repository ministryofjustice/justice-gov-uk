import './navigation-secondary.html.twig';
import './navigation-secondary.scss';

// export default function () {
//     const el = document.querySelector('.navigation-secondary');

//     if (!el) {
//         return;
//     }

//     // There should only ever be one secondary navigation
//     const button = el.querySelector('.navigation-secondary__button');
//     const nav = el.querySelector('.navigation-secondary__nav');
//     button.addEventListener('click', () => {
//         nav.classList.toggle('navigation-secondary__nav--open');
//         button.setAttribute(
//             'aria-expanded',
//             !(button.getAttribute('aria-expanded') === 'true')
//         );
//         // // Get the current state, so that the following actions don't go out of sync.
//         // const willOpen = !nav.classList.contains('navigation-secondary__nav--open');
//         // // Toggle the open class on the nav element.
//         // nav.classList.toggle('navigation-secondary__nav--open');
//         // // Set the aria-expanded attribute on the button.
//         // button.setAttribute('aria-expanded', willOpen);
//         // // Update the text content of the button's visually hidden span.
//         // button.querySelector('.visually-hidden').textContent = willOpen ? 'Close secondary ' : 'Open secondary ';
//     });

//     function initDropdowns(list) {
//         const showMore = list.querySelector('.navigation-secondary__button');
//         const sublist = list.querySelector('.navigation-secondary__sublist');
//         if (!showMore || !sublist) {
//             return;
//         }
//         showMore.addEventListener('click', () => {
//             sublist.classList.toggle('navigation-secondary__sublist--open');
//             showMore.setAttribute(
//                 'aria-expanded',
//                 !(showMore.getAttribute('aria-expanded') === 'true')
//             );
//             showMore.setAttribute(
//                 'aria-label',
//                 showMore.getAttribute('aria-label') === 'Show more'
//                     ? 'Show less'
//                     : 'Show more'
//             );
//         });
//         const listItems = sublist.querySelectorAll(
//             '.navigation-secondary__list-item'
//         );
//         listItems.forEach((listItem) => {
//             initDropdowns(listItem);
//         });
//     }

//     const topLevelListItems = el.querySelectorAll(
//         '.navigation-secondary__list-item--level-0'
//     );

//     topLevelListItems.forEach((list) => {
//         initDropdowns(list);
//     });
// }
