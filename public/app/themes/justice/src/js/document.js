import { __experimentalPublishDateTimePicker as PublishDateTimePicker } from "@wordpress/block-editor";
import { Dropdown, Button } from "@wordpress/components";
import domReady from "@wordpress/dom-ready";
import { dateI18n, getSettings } from "@wordpress/date";
import { useEffect, useState } from "@wordpress/element";


/**
 * A form/control to schedule a revision.
 *
 * @returns {React.ReactElement}
 */

const RevisionScheduleControl = () => {
  const [date, setDate] = useState(null);

  const { datetimeAbbreviated } = getSettings().formats;
  const button = date ? dateI18n(datetimeAbbreviated, date) : "Immediately";

  const postContent = document.querySelector("#post_content");
  const postContentOverride = document.querySelector("#post_content_override");

  useEffect(() => {
    // If date and it's in the future
    if(date && new Date(date) > new Date()) {
      // Switch the names of the post_content fields.
      postContent.name = "post_content_ignore";
      postContentOverride.name = "post_content";
    } else {
      // Or, switch them back.
      postContent.name = "post_content";
      postContentOverride.name = "post_content_ignore";
    }
  }, [date]);

  return (
    <>
      <span style={{ paddingRight: "8px" }}>Publish </span>
      <Dropdown
        renderToggle={({ isOpen, onToggle }) => (
          <Button onClick={onToggle} aria-expanded={isOpen} isTertiary={true}>
            {button}
          </Button>
        )}
        renderContent={({ onClose }) => (
          <PublishDateTimePicker
            currentDate={date}
            onChange={(newDate) => setDate(newDate)}
            onClose={onClose}
          />
        )}
      />
      <input
        type="hidden"
        name="schedule"
        value={date ? date.toString() : ""}
      />
    </>
  );
};

/**
 * Mirror the visibility of #revision-summary to #revision-schedule
 *
 * Add a listener to #revision-summary meta box,
 * this element is hidden and shown based on the user interactions.
 * Copy it's visibility and apply it to our custom meta box, #schedule-wrap
 *
 * @returns void
 */

const mirrorVisibility = () => {
  const revisionSummary = document.querySelector("#revision-summary");
  const revisionSchedule = document.querySelector("#revision-schedule");

  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      if (mutation.attributeName === "style") {
        revisionSchedule.style.display = revisionSummary.style.display;
      }
    });
  });

  if (revisionSummary && revisionSchedule) {
    observer.observe(revisionSummary, { attributes: true });
  }
};

/**
 * Create a new hidden element that can be used to override the post_content field.
 * 
 * This is necessary because the post_content field is used to store the attachment ID.
 * When the post is scheduled, the attachment ID is should not be updated.
 * 
 * @returns void
 */

const overridePostContentField = () => {
  const postContent = document.querySelector("#post_content");

  // Create 2 elements after the post content
  const newPostContent = document.createElement("input");

  // Set the type and value of the new elements
  newPostContent.type = "hidden";
  newPostContent.id = "post_content_override";
  newPostContent.name = "post_content_ignore";
  newPostContent.value = postContent.value;

  // Append the new elements to the form
  postContent.after(newPostContent);
}

/**
 * Run the script and render the react component.
 */

domReady(() => {
  // mirrorVisibility();

  overridePostContentField();

  const el = document.querySelector("body.post-type-document #schedule-wrap");
  const root = ReactDOM.createRoot(el);
  root.render(<RevisionScheduleControl />);
});
