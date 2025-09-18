import CheckboxField from "./CheckboxControl";
import ColorPickerComponent from "./ColorPicker";
import DatePicker from "./DatePickerControl/panel.jsx";
import MediaUpload from "./MediaUpload";
import Multiselect from "./Mulitselect";
import PageField from "./PageControl/panel.jsx";
import RepeaterControl from "./RepeaterControl";
import SandboxField from "./SandboxControl";
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
  sandbox: SandboxField,
  select: SelectControlComponent,
  text: TextField,
  textarea: TextareaField,
  toggle: ToggleField,
};

export default controlsIndex;
