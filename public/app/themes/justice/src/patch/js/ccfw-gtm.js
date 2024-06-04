import { Storage } from './ccfw-storage'

/**
 * A dataLayer management script for GTM
 *
 * First step; define some settings
 */
const CCFW = {
    gtmID: document.getElementById('ccfw-page-banner').getAttribute('data-gtm-id'),
    isValidID: () => CCFW.gtmID.startsWith('GTM') || false,
    allowedIds: [],
    selector: {
        all: {
            accept: 'cookie-accept',
            decline: 'cookie-decline'
        },
        moreInfo: 'cookie-more-info',
        settings: 'js-ccfw-settings-button'
    },
    jq: {
        toggles: jQuery('.ccfw-banner__toggle-slider'),
        button: {
            save_preferences: jQuery('.ccfw-banner__save-preferences')
        }
    },
    storage: {
        time: {
            get: () => JSON.parse(Storage.getItem('ccfw-time')),
            set: () => Storage.setItem(
                'ccfw-time',
                JSON.stringify(new Date(
                    new Date().setFullYear(new Date().getFullYear() + 1)
                ).getTime())
            )
        },
        allowed: {
            get: () => JSON.parse(Storage.getItem('ccfw-gtm-allowed')),
            set: (value) => Storage.setItem('ccfw-gtm-allowed', JSON.stringify(value))
        },
        bannerHidden: {
            get: () => JSON.parse(Storage.getItem('ccfw-banner-hidden')),
            set: (value) => Storage.setItem(
                'ccfw-banner-hidden',
                JSON.stringify(value)
            )
        },
        clear: (key) => Storage.removeItem(key)
    },
    listItem: {
        set: (value) => {
            if (!dataLayer[0]) {
                return false
            }

            dataLayer[0]['gtm.allowlist'] = value
            CCFW.storage.allowed.set(value)
        },
        clear: (key, value) => {
            dataLayer[0][key].forEach((element, index, array) => {
                if (element === value) {
                    array.splice(index, 1)
                }
            })
        }
    },
    listen: {
        toggles: () => CCFW.jq.toggles.on('click', toggle)
    },
    /**
    * Wrap the dataLayer.push() function
    * @param event
    * @param object
    * @private
    */
    trackEvent: (event, object) => {
        if (!object) {
            dataLayer.push({ 'event': event })
            return
        }
        dataLayer.push(jQuery.extend({}, { 'event': event }, object))
    },
    /**
    * Runs on load
    */
    maybeExpired: () => {
        let stored = CCFW.storage.time.get() // always a year from storage
        let now = new Date().getTime()

        if (now > stored) { // a year has past
            CCFW.storage.clear('ccfw-gtm-allowed')
            CCFW.storage.clear('ccfw-banner-hidden')
            CCFW.storage.clear('ccfw-time')
        }
    },

    /**
    * @param remove acknowledges that we are removing all allowed ids
    */
    toggleAll: function (remove) {
        let allowList = CCFW.storage.allowed.get() || []

        if (remove) {
            allowList = []
        }

        CCFW.jq.toggles.each(function (key, element) {
            const toggle = jQuery(element)
            let allowed = toggle.data('allowlist')

            if (remove) {
                toggle.attr('aria-checked', false)
                jQuery('#ccfw-' + allowed + '-toggle-off').removeAttr('aria-hidden').show()
                jQuery('#ccfw-' + allowed + '-toggle-on').attr('aria-hidden', 'true').hide()
            } else {
                if (allowList.indexOf(allowed) === -1) {
                    allowList.push(allowed)
                }
                toggle.attr('aria-checked', true)
                jQuery('#ccfw-' + allowed + '-toggle-on'). removeAttr('aria-hidden').show()
                jQuery('#ccfw-' + allowed + '-toggle-off'). attr('aria-hidden', 'true').hide()
            }
        })

        return allowList
    },
    cache: () => CCFW.jq.toggles.each((key, element) => CCFW.allowedIds.push(jQuery(element).data('allowlist'))),
    manageAll: (allowList, allowed, pressed) => {
        if (!allowList) {
            return
        }

        let totalAllowed = CCFW.jq.toggles.length - 1

        if (allowed !== 'all') {
            if (pressed) {
                allowList = allowList.filter(item => item !== 'all')
                jQuery('#ccfw-all-toggle-off').removeAttr('aria-hidden').show()
                jQuery('#ccfw-all-toggle-on').attr('aria-hidden', 'true').hide()
                if (allowList.length === 0) {
                    jQuery('button[data-allowlist="all"]').attr('aria-checked', false)
                }
            }

            if (allowList.length > 0) {
                jQuery('button[data-allowlist="all"]').attr('aria-checked', true)
            }

            if (totalAllowed === allowList.length) {
                if (allowList.indexOf('all') === -1) {
                    allowList.push('all')
                }
                jQuery('#ccfw-all-toggle-on').removeAttr('aria-hidden').show()
                jQuery('#ccfw-all-toggle-off').attr('aria-hidden', 'true').hide()
                jQuery('button[data-allowlist="all"]').attr('aria-checked', true)
            }
        }

        return allowList
    },
    patch: {
        popup: {
            button: {
                save: () => {
                    const button = jQuery(
                        CCFW.jq.button.save_preferences.find('button')
                    ).clone(true)

                    button.attr({
                        id: 'cookie-save-preferences-top',
                        class: 'ccfw-banner__button',
                        disabled: 'disabled'
                    }).text('Save')

                    CCFW.jq.button.save_preferences.find('button').text('Save cookie preferences')
                    jQuery('#cookie-popup').prepend(button)
                    jQuery(CCFW.jq.toggles).on(
                        'click',
                        () => jQuery('#cookie-save-preferences-top').removeAttr('disabled')
                    )
                }
            }
        }
    }
}

/**
 * An init function to start the GTM feature
 * @returns {boolean}
 */
const init = () => {
    if (CCFW.isValidID()) {
        // check we have a dataLayer
        if (!window.dataLayer) {
            window.dataLayer = []
        }

        // INIT
        let allowedList = CCFW.storage.allowed.get() || [] // default to empty array

        // Always allow variables and triggers - https://developers.google.com/tag-manager/web/restrict
        let ccfwTriggers = [
            'evl', 'cl', 'fsl', 'hl', 'jel', 'lcl', 'sdl', 'tl', 'ytl'
        ]

        let ccfwVariables = [
            'k', 'v', 'c', 'ctv', 'e', 'jsm', 'dpg', 'd', 'vis', 'gas', 'f', 'j', 'smm', 'r', 'remm', 'u'
        ]

        allowedList = allowedList.concat(ccfwTriggers, ccfwVariables)

        window.dataLayer.push({
            'gtm.allowlist': allowedList
        });

        // Drop GTM code
        (function (w, d, s, l, i) {
            w[l] = w[l] || []
            w[l].push({
                'gtm.start':
                new Date().getTime(), event: 'gtm.js',
            })
            let f = d.getElementsByTagName(s)[0],
            j = d.createElement(s), dl = l !== 'dataLayer' ? '&l=' + l : ''
            j.async = true
            j.src = 'https://www.googletagmanager.com/gtm.js?id=' +
            encodeURIComponent(i) + dl
            f.parentNode.insertBefore(j, f)
        })(window, document, 'script', 'dataLayer', CCFW.gtmID)

        return true
    } else {
        console.warn('CCFW GTM:', 'The GTM ID wasn\'t assigned or, does not exist.')
        return false
    }
}

const toggle = function (e) {

    if (typeof jQuery === undefined) {
        console.log('jQuery is not defined')
        return false
    }

    e.preventDefault()

    let toggle = jQuery(this)
    let allowed = toggle.data('allowlist')
    let allowList = CCFW.storage.allowed.get() || []
    let pressed = toggle.attr('aria-checked') === 'true'

    toggle.attr('aria-checked', !pressed)

    if (allowed === 'all') {
        allowList = CCFW.toggleAll(pressed)
    }

    if (pressed) {
        allowList = allowList.filter(item => item !== allowed)
        jQuery('#ccfw-' + allowed + '-toggle-off').removeAttr('aria-hidden').show()
        jQuery('#ccfw-' + allowed + '-toggle-on').attr('aria-hidden', 'true').hide()
    } else {
        if (allowList.indexOf(allowed) === -1) {
            allowList.push(allowed)
        }
        jQuery('#ccfw-' + allowed + '-toggle-on').removeAttr('aria-hidden').show()
        jQuery('#ccfw-' + allowed + '-toggle-off'). attr('aria-hidden', 'true'). hide()
    }

    allowList = CCFW.manageAll(allowList, allowed, pressed)

    CCFW.listItem.set(allowList)

    return true
}

export { CCFW, init }
