import '../base.scss';

// Components
import './components/button';
import './components/link';
import './components/navigation-main';
import './components/text-input';
import './components/text-input-form';
import './components/rich-text';
import './components/footer';
import './components/header';
import './components/image';
import './components/image-with-text';
import './components/file-download';
import './components/search-bar-block';
import './components/search-result-list';
import './components/search-result-card';
import './components/pagination';
import './components/to-the-top';
import './components/navigation-sections';
import './components/breadcrumbs';
import './components/hero';
import './components/updated-date';
import './components/horizontal-rule';
import './components/skip-link';

import navigationSecondary from './components/navigation-secondary';
import sidebarBlock from './components/sidebar-block';

// Form elements
import './components/text-input';
import './components/selection-input';

// Layouts
import './layouts/base';
import './layouts/one-sidebar';
import './layouts/two-sidebars';

addEventListener('DOMContentLoaded', () => {
    navigationSecondary();
    sidebarBlock();
});
