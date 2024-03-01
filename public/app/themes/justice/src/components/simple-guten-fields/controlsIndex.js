import CheckboxField from "./CheckboxControl";
import ColorPickerComponent from "./ColorPicker";
import DatePicker from "./DatePickerControl/panel.js";
import MediaUpload from "./MediaUpload";
import Multiselect from "./Mulitselect";
import PageField from "./PageControl/panel.js";
import RepeaterControl from "./RepeaterControl";
import SelectControlComponent from "./SelectControl";
import TextField from "./TextControl";
import TextareaField from "./TextareaControl";
import ToggleField from "./ToggleControl";

const controlsIndex = {
  checkbox: CheckboxField,
  color: ColorPickerComponent,
  datepicker: DatePicker,
  media: MediaUpload,
  multiselect: Multiselect,
  page: PageField,
  repeater: RepeaterControl,
  select: SelectControlComponent,
  text: TextField,
  textarea: TextareaField,
  toggle: ToggleField,
};

export default controlsIndex;
