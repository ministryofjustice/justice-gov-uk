// @ts-check
import { select, useSelect } from "@wordpress/data";
import { PluginDocumentSettingPanel } from "@wordpress/editor";
import { Fragment } from "@wordpress/element";
import { registerPlugin } from "@wordpress/plugins";

import controlsIndex from "./controlsIndex";

/** @type {(string: string) => string} */
const capitalizeFirstLetter = (string) => {
  return string.charAt(0).toUpperCase() + string.slice(1);
};

/**
 * @typedef {import('../../js/block-editor.d.ts').SimpleGutemField} SimpleGutemField
 * @typedef {import('@wordpress/data/src/types').SelectFunction} SelectFunction
 */

/** @type {(field: SimpleGutemField, select: SelectFunction) => boolean} */
const meetsConditons = (field, select) => {
  const { conditions } = field;
  if (!conditions) {
    return true;
  }

  return conditions.every((condition) => {
    const { target, operator, value } = condition;
    const targetArray = target.split(".");

    let postValue = undefined;

    switch (targetArray[0]) {
      case "attribute":
        // @ts-ignore https://github.com/WordPress/gutenberg/pull/46881
        const attriute = select("core/editor").getEditedPostAttribute(
          targetArray[1],
        );
        // Get a nested property if necessary
        postValue = targetArray[2] ? attriute[targetArray[2]] : attriute;
      default:
      // console.warn('Could net get value for condition target:', target)
    }

    switch (operator) {
      case "===":
        return postValue === value;
      case "!==":
        return postValue !== value;
      case "INTERSECTS":
        return !!postValue.filter((entry) => value.includes(entry)).length;
      case "NOT INTERSECTS":
        return !postValue.filter((entry) => value.includes(entry)).length;
      default:
        return true;
    }
  });
};

const CustomFieldsPanel = () => {
  let fields = sgf_data.fields;

  let currentCpt = select("core/editor").getCurrentPostType();

  if (fields) {
    fields = fields.filter(
      (field) =>
        field.post_type == currentCpt &&
        useSelect((select) => meetsConditons(field, select), []),
    );
  }

  if (!fields.map((field) => field.post_type).includes(currentCpt)) {
    return null;
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
  icon: "admin-generic",
  render: CustomFieldsPanel,
});
