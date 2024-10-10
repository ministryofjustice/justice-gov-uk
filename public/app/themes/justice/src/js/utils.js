// cyrb53 (c) 2018 bryc (github.com/bryc). License: Public domain. Attribution appreciated.
// A fast and simple 64-bit (or 53-bit) string hash function with decent collision resistance.
// Largely inspired by MurmurHash2/3, but with a focus on speed/simplicity.
// See https://stackoverflow.com/questions/7616461/generate-a-hash-from-string-in-javascript/52171480#52171480
// https://github.com/bryc/code/blob/master/jshash/experimental/cyrb53.js
const stringToFloatHash = (str, seed = 0) => {
  let h1 = 0xdeadbeef ^ seed,
    h2 = 0x41c6ce57 ^ seed;
  for (let i = 0, ch; i < str.length; i++) {
    ch = str.charCodeAt(i);
    h1 = Math.imul(h1 ^ ch, 2654435761);
    h2 = Math.imul(h2 ^ ch, 1597334677);
  }
  h1 = Math.imul(h1 ^ (h1 >>> 16), 2246822507);
  h1 ^= Math.imul(h2 ^ (h2 >>> 13), 3266489909);
  h2 = Math.imul(h2 ^ (h2 >>> 16), 2246822507);
  h2 ^= Math.imul(h1 ^ (h1 >>> 13), 3266489909);
  // For a single 53-bit numeric return value we could return
  // 4294967296 * (2097151 & h2) + (h1 >>> 0);
  // but we instead return the full 64-bit value:
  return [h2 >>> 0, h1 >>> 0];
};

// An improved, *insecure* 64-bit hash that's short, fast, and has no dependencies.
// Output is always 14 characters.
// https://gist.github.com/jlevy/c246006675becc446360a798e2b2d781
export const stringToHash = (str, seed = 0) => {
  const [h2, h1] = stringToFloatHash(str, seed);
  return h2.toString(36).padStart(7, "0") + h1.toString(36).padStart(7, "0");
};

/**
 * A generic throttle function.
 *
 * @param {function} mainFunction
 * @param {number} delay
 * @returns
 */

export const throttle = (mainFunction, delay) => {
  let timerFlag = null; // Variable to keep track of the timer

  // Returning a throttled version
  return (...args) => {
    if (timerFlag === null) {
      // If there is no timer currently running
      mainFunction(...args); // Execute the main function
      timerFlag = setTimeout(() => {
        // Set a timer to clear the timerFlag after the specified delay
        timerFlag = null; // Clear the timerFlag to allow the main function to be executed again
      }, delay);
    }
  };
};

/**
 * Debounce and combo of debounce + throttle.
 *
 * @see https://trungvose.com/experience/debounce-throttle-combination/
 */

// Simplest, dont run once when event haven been seen for 50 ms
// You have to wait until events end firing before function is run
function debounce(method, delayMs) {
  delayMs = delayMs || 50;
  var timer = null;
  return function () {
    var context = this,
      args = arguments;
    clearTimeout(timer);
    timer = setTimeout(function () {
      method.apply(context, args);
    }, delayMs);
  };
}

// Somewhat more complicated. function is fired right away, then for maximum
// every 250 ms. You might potentially have to wait 250 ms after last seen event
export const comboDebounce = (fn, threshold) => {
  threshold = threshold || 250;
  var last, deferTimer;

  var db = debounce(fn);
  return function () {
    var now = +new Date(),
      args = arguments;
    if (!last || (last && now < last + threshold)) {
      clearTimeout(deferTimer);
      db.apply(this, args);
      deferTimer = setTimeout(function () {
        last = now;
        fn.apply(this, args);
      }, threshold);
    } else {
      last = now;
      fn.apply(this, args);
    }
  };
};

/**
 * A function to round a date's minutes up or down.
 *
 * @param {?Date} originalDate
 * @param {number} resolution
 * @param {'up'|'down'} direction
 * @returns Date
 */

export const roundMins = (
  originalDate = new Date(),
  direction = "up",
  resolution = 30,
) => {
  // Make a clone so as not to mutate the original value.
  const date = new Date(originalDate.getTime());

  // Set secs and ms to 0;
  date.setSeconds(0);
  date.setMilliseconds(0);

  if (direction === "down") {
    date.setMinutes(Math.floor(date.getMinutes() / resolution) * resolution);
  } else {
    // As we've rounded down secs and ms, add 1 to the mins here, then ceil.
    // e.g.
    // - When zero-ing in previous step: 10:00:30:123 -> 10:00:00:000
    // - Here: 10:01:00:000 -> 10:30:00:000
    date.setMinutes(
      Math.ceil((date.getMinutes() + 1) / resolution) * resolution,
    );
  }

  return date;
};
