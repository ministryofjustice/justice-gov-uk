// Helper functions for use in Storybook documentation
const reviewPeriod = 6;
const formatOptions = {
    year: "numeric",
    month: "long",
    day: "numeric",
};

/**
 * Formats a date string in 'j l, F Y' format
 * @param {string} lastReview The date that a page was last reviewed.
 * @return {string} The date string formatted as
 */
function getLastReview(lastReview)
{
    const date = new Date(lastReview);
    return date.toLocaleString('en-GB', formatOptions);
}

/**
 * Adds the review period to a date string and returns the date in 'jS l, F Y' format
 * @param {string} lastReview The date that a page was last reviewed.
 * @return {string} The date string formatted as
 */
function getNextReview(lastReview)
{
    const date = new Date(lastReview);
    const reviewDate =  new Date(date.setMonth(date.getMonth() + reviewPeriod));
    return reviewDate.toLocaleString('en-GB', formatOptions);
}

/**
 * Returns a block that displays the last date that a page was reviewed and when it is due a review again
 * @param {string} lastReview The date that a page was last reviewed.
 * @return {string} The date string formatted as
 */
export function getReviewBlock(lastReview)
{
    return `This page was last reviewed on ${getLastReview(lastReview)}. It should be reviewed again on ${getNextReview(lastReview)}`;
}