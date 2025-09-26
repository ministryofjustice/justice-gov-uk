/**
 * A script for determining our storage method
 *
 * Falls back to cookies if localStorage is unavailable, otherwise
 * we disable storage all together.
 */

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
        const mod = 'ccfw'
        try {
            localStorage.setItem(mod, mod)
            localStorage.removeItem(mod)
            return true
        } catch (e) {
            return false
        }
    },
    cookie: () => {
        const mod = 'ccfw'
        const secureString = window.mojCcfwConfig?.https ? '; secure' : ''
        try {
            // try and set it...
            document.cookie = `${mod}=1${secureString}`
            // test it exists...
            const cookiesEnabled = document.cookie.indexOf(mod + '=') !== -1
            // remove it...
            document.cookie = `${mod}=1; expires=Thu, 01-Jan-1970 00:00:01 GMT${secureString}`
            return cookiesEnabled
        } catch (e) {
            // Catch and ignore if cookies are disabled.
            return false
        }
    },
    getStorage: () => {
        if (!CCFWStorage.valid()) {
            return {
                disabled: true
            }
        }

        /**
         * If localStorage is available...
         */
        if (CCFWStorage.local()) {
            return window.localStorage
        }

        /**
         * If we are here, return the cookie polyfill
         */
        return {
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
                    new Date().setFullYear(new Date().getFullYear() + 1),
                ).toUTCString()
                const secureString = window.mojCcfwConfig?.https ? '; secure' : ''
                document.cookie = `${key}=${value || ''}; expires=${set ? date : ''}; path=/${secureString}`
            },
            removeItem: (key) => {
                Storage.setItem(key, null, false)
            }
        }
    }
}

/**
 * Storage Object
 */
const Storage = CCFWStorage.getStorage()

export { Storage }
