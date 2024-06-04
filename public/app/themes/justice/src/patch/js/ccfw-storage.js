/**
 * A script for determining our storage method
 *
 * Falls back to cookies if localStorage is unavailable, otherwise
 * we disable storage all together.
 */
let Storage = {
    disabled: true
}

const CCFWStorage = {
    /**
     * Both local() and cookie() return booleans
     * false = invalid
     * true = valid
     * First we test for localStorage, if this is false, we test for cookies
     * If cookie returns true, valid() returns true, otherwise, valid() returns
     * false.
     *
     * @returns {boolean}
     */
    valid: () => {
        return CCFWStorage.local() || CCFWStorage.cookie()
    },
    local: () => {
        let mod = 'ccfw';
        try {
            localStorage.setItem(mod, mod);
            localStorage.removeItem(mod);
            return true;
        } catch (e) {
            return false
        }
    },
    cookie: () => {
        try {
            // try and set it...
            document.cookie = 'cookie_test=1'
            // test it exists...
            const cookiesEnabled = document.cookie.indexOf('cookie_test=') !== -1
            // remove it...
            document.cookie = 'cookie_test=1; expires=Thu, 01-Jan-1970 00:00:01 GMT'
            return cookiesEnabled
        } catch (e) {
            // Catch and handle the error if cookies are disabled.
            return false
        }
    }
}

if (CCFWStorage.valid()) {
    /**
     * If localStorage isn't available, we can use cookies
     */
    if (!CCFWStorage.local()) {
        Storage = {
            getItem: (key) => {
                const value = `; ${document.cookie}`
                const parts = value.split(`; ${key}=`)

                if (parts.length === 2) {
                    let part = parts.pop().split(';').shift()
                    return part === '' ? null : part
                }
                return null
            },
            setItem: (key, value, set = true) => {
                const date = new Date(
                    new Date().setFullYear(new Date().getFullYear() + 1)
                ).toUTCString()
                document.cookie = key + '=' + (value || '') + '; expires=' + (set ? date : '') + '; path=/'
            },
            removeItem: (key) => {
                Storage.setItem(key, null, false)
            }
        }
    } else {
        Storage = window.localStorage
    }
}

export { Storage }
