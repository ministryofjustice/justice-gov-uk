import '../base.scss';

// Components
import './components/button';
import './components/link';
import './components/navigation-main';
import './components/text-input';
import './components/search-bar';
import './components/rich-text';
import './components/footer';
import './components/header';
import './components/image';
import './components/image-with-text';
import './components/file-download';
import './components/to-the-top';
import './components/navigation-sections';
import './components/breadcrumbs';
import './components/hero';

import sidebarBlock from '@components/sidebar-block';

// Layouts
import './layouts/one-sidebar';
import './layouts/two-sidebars';

addEventListener('DOMContentLoaded', () => {
    sidebarBlock();
});
