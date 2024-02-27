// @ts-check
import { Fragment } from "@wordpress/element";
import { select } from "@wordpress/data";
import { PluginDocumentSettingPanel } from "@wordpress/edit-post";
import { registerPlugin } from "@wordpress/plugins";

import controlsIndex from "./controlsIndex";

// /** @type {(string: string) => string} */
const capitalizeFirstLetter = (string) => {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

const CustomFieldsPanel = () => {

  let fields = sgf_data.fields;

  let currentCpt = select("core/editor").getCurrentPostType();

  if (!fields.map((field) => field.post_type).includes(currentCpt)) {
    return null;
  }

  if (fields) {
    fields = fields.filter((field) => field.post_type == currentCpt);
  }

  let panels = fields
    .map((field) => field.panel)
    .filter((item, i, array) => array.indexOf(item) === i);

  return (
    <div>
      {panels.map((panel, panelIndex) => {
        return (
          <div key={panelIndex}>
            <PluginDocumentSettingPanel
              name={panel}
              title={capitalizeFirstLetter(panel.replace(/[-_]/g, " "))}
              className="custom-panel"
            >
              {fields
                .filter((field) => field.panel === panel)
                .map((field, index) => {
                  let ControlHoc = controlsIndex[field.control];
                  return (
                    <Fragment key={index}>
                      <ControlHoc index={index} field={field} />
                      <hr />
                    </Fragment>
                  );
                })}
            </PluginDocumentSettingPanel>
          </div>
        );
      })}
    </div>
  );
};

registerPlugin("plugin-document-setting-panel-demo", {
  icon: 'admin-generic',
  render: CustomFieldsPanel,
});
