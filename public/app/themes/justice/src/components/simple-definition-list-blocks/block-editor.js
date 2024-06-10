// @ts-check
import { addFilter } from '@wordpress/hooks';

/** 
 * Changes the settings of the blocks in the simple-definition-list-blocks plugin.
 * 
 * @param {Object} settings
 * @param {string} name
 */
function updateBlockSettings(settings, name) {

  switch (name) {
    case 'simple-definition-list-blocks/list':
      settings.title = 'Definition List';
      settings.keywords.push('glossary');
      break;
    case 'simple-definition-list-blocks/term':
      settings.title = 'Term';
      break;
    case 'simple-definition-list-blocks/details':
      settings.title = 'Details';
      break;
  }

  return settings;
}

addFilter(
  'blocks.registerBlockType',
  'moj/simple-definition-list-blocks',
  updateBlockSettings
);
