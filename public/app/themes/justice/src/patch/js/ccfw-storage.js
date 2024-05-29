/**
 * A script for determining our storage method
 *
 * Falls back to cookies if localStorage is unavailable
 */
if (window.localStorage === undefined) {
    Storage = {
        getItem: (key) => {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${key}=`);

            if (parts.length === 2) {
                let part = parts.pop().split(';').shift();
                return part === '' ? null : part;
            }
            return null;
        },
        setItem: (key, value, set = true) => {
            const date = new Date(new Date().setFullYear(new Date().getFullYear() + 1)).toUTCString();
            document.cookie = key + '=' + (value || '') + '; expires=' + (set ? date : '') + '; path=/'
        },
        removeItem: function (key) {
            this.setItem(key, null, false)
        }
    }
} else {
    Storage = window.localStorage;
}
export let Storage
