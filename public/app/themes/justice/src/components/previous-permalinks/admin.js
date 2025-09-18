// import jQuery from "jquery";

jQuery(document).ready(function ($) {
  const handlePermalinkDelete = (postId, postName, permalink, nonce) => {
    const confirmDelete = confirm(
      `Are you sure you want to delete the previous permalink?\n` +
      `It could result in broken links.\n\n` +
      `${permalink}`,
    );
    if (!confirmDelete) {
      return; // If user cancels, exit the function
    }

    $.ajax({
      url: window.ajaxurl,
      type: "post",
      data: {
        action: "delete_previous_permalink",
        nonce,
        post_id: postId,
        post_name: postName,
      },
      success: function (data) {
        if (data != "-1") {
          $("#edit-slug-box").html(data);
        }
      },
    });
    return false;
  };

  // Add an event listener to post-body-content, which is the container for the the buttons.
  // We do this because the buttons are dynamically added to the page.
  $("body.post-php.post-type-document #post-body-content").on(
    "click",
    function (e) {
      const $target = $(e.target);
      if (
        $target.hasClass("previous-permalinks__edit") ||
        $target.hasClass("previous-permalinks__close")
      ) {
        e.preventDefault();
        $("body").toggleClass("previous-permalinks-is-editing");
      }

      // Check if the clicked element is a link with class 'previous-permalink'
      if ($target.hasClass("previous-permalinks__delete")) {
        e.preventDefault();

        return Promise.resolve().then(() => {
          handlePermalinkDelete(
            e.target.dataset["postId"],
            e.target.dataset["postName"],
            e.target.dataset["permalink"],
            e.target.dataset["nonce"],
          );
        });
      }
    },
  );
});
