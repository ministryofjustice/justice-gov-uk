import { Storage } from './ccfw-storage'
import { CCFW } from './ccfw-gtm'

(function ($) {
    'use strict'

    /**
     * Some spiders and webcrawlers are causing errors in Sentry because they are not loading jQuery
     * In a nutshell, if jQuery isn't available here, we cannot run.
     */
    if (typeof $ === undefined) {
        return false
    }

    /**
     * We cannot run if Storage.disabled is set.
     * Run a test to check the Storage engine, and return early if disabled.
     */
    if (Storage.hasOwnProperty('disabled')){
        return false
    }

    /**
     *  Define handlers for when the html/DOM is ready.
     */
    $(function ($) {
        // This is used so much make sure all modules use it to save calls to DOM
        const cacheMainElements = {
            init: function () {
                this.$el = $('#ccfw-page-banner')
                this.$notEl = $('#ccfw-page-banner ~ *') // everything after the cookie popup = the whole page
                this.$body = $('body')
                this.$html = $('html')
            },
        }
        /**
         *  Helper functions for shared tasks
         * */
        const utilities = {
            init: function () {
                this.cacheDom()
                this.bindEvents()
            },
            cacheDom: function () {
                this.$el = cacheMainElements.$el
                this.$notEl = cacheMainElements.$notEl
                this.$settingsModal = this.$el.find('#cookie-popup')
                this.$body = cacheMainElements.$body
                this.$html = cacheMainElements.$html
                this.$cookieSettingsButton = this.$body.find('#js-ccfw-settings-button')
            },
            bindEvents: function () {
                this.$cookieSettingsButton.on('click', this.showBanner.bind(this))
            },
            showBanner: function () {
                this.$el.show()
                this.$cookieSettingsButton.hide()

                const banner = $('#ccfw-page-banner');
                // If the cookie banner exists, scroll to the top
                if (banner.length > 0) {
                    $([document.documentElement, document.body]).animate({
                        scrollTop: banner.offset().top
                    }, 200)
                }
            },
            hideBanner: function () {
                this.$el.hide()
                this.$cookieSettingsButton.show()
            },
            hideSettingsModal: function () {
                this.$settingsModal.hide()
                this.$body.removeClass('ccfw-modal-open')
                this.$el.removeClass('ccfw-cookie-banner-open')
                this.$html.removeClass('ccfw-cookie-banner-open')
                this.$body.removeClass('ccfw-cookie-banner-open')
                this.$notEl.removeAttr('aria-hidden')
            },
            showSettingsModal: function () {
                this.$settingsModal.show()
                this.$body.addClass('ccfw-modal-open')
                this.$el.addClass('ccfw-cookie-banner-open')
                this.$html.addClass('ccfw-cookie-banner-open')
                this.$body.addClass('ccfw-cookie-banner-open')
                this.$notEl.attr('aria-hidden', 'true')

                settingsModal.trapSettingsFocus()
                this.$el.scrollTop(0)

                // get allowed
                let allowList = CCFW.storage.allowed.get() || []
                CCFW.jq.toggles.each(function (key, element) {
                    let allowed = $(element).data('allowlist')

                    if (allowList.indexOf(allowed) !== -1) {
                        $(element).attr('aria-checked', true)
                        $('#ccfw-' + allowed + '-toggle-on').removeAttr('aria-hidden').show()
                        $('#ccfw-' + allowed + '-toggle-off').attr('aria-hidden', 'true').hide()
                    } else {
                        $(element).attr('aria-checked', false)
                        $('#ccfw-' + allowed + '-toggle-off').removeAttr('aria-hidden').show()
                        $('#ccfw-' + allowed + '-toggle-on').attr('aria-hidden', 'true').hide()
                    }
                })
            }
        }

        /**
         *  Banner management and control
         * */
        const banner = {
            init: function () {
                this.cacheDom()
                this.bindEvents()
                this.bannerDisplay()
            },
            cacheDom: function () {
                this.$el = cacheMainElements.$el
                this.$buttonAccept = this.$el.find('#cookie-accept')
                this.$buttonDecline = this.$el.find('#cookie-decline')
                this.$buttonInfo = this.$el.find('#cookie-more-info')
            },
            bindEvents: function () {
                this.$buttonAccept.on('click', this.acceptAllButton.bind(this))
                this.$buttonDecline.on('click', this.declineAllButton.bind(this))
                this.$buttonInfo.on('click', this.chooseCookieSettingsButton.bind(this))
            },
            bannerDisplay: function () {
                if (!CCFW.storage.bannerHidden.get()) {
                    utilities.showBanner()
                } else {
                    utilities.hideBanner()
                }
            },
            acceptAllButton: function () {
                CCFW.listItem.set(
                    CCFW.toggleAll(false)
                )
                CCFW.storage.time.set()
                CCFW.storage.bannerHidden.set(true)
                utilities.hideBanner()
                window.location.reload(false)
                return false
            },
            declineAllButton: function () {
                CCFW.listItem.set(
                    CCFW.toggleAll(true)
                )
                CCFW.storage.time.set()
                CCFW.storage.bannerHidden.set(true)
                utilities.hideBanner()
                clearOurCookies(CCFW.storage.allowed.get())
                window.location.reload(false)
                return false
            },
            chooseCookieSettingsButton: function () {
                utilities.showSettingsModal()
            }
        }

        const settingsModal = {
            init: function () {
                this.cacheDom()
                this.bindEvents()
            },
            cacheDom: function () {
                this.$el = cacheMainElements.$el
                this.$settingsModal = this.$el.find('#cookie-popup')
                this.$buttonAccept = this.$settingsModal.find('#cookie-accept')
                this.$buttonDecline = this.$settingsModal.find('#cookie-decline')
                this.$buttonInfo = this.$settingsModal.find('#cookie-more-info')
                this.$buttonSave = this.$settingsModal.find('#cookie-save-preferences')
                this.$buttonSaveTop = this.$settingsModal.find('#cookie-save-preferences-top')
                this.$buttonModalClose = this.$settingsModal.find('#ccfw-modal-close')
                this.$body = cacheMainElements.$body
            },
            bindEvents: function () {
                this.$buttonModalClose.on('click', this.modalDisplay.bind(this))
                this.$buttonInfo.on('click', this.trapSettingsFocus.bind(this))
                this.$buttonSave.on('click',this.savePreferences.bind(this))
                this.$buttonSaveTop.on('click',this.savePreferences.bind(this))
            },
            modalDisplay: function () {
                utilities.hideSettingsModal()
            },
            trapSettingsFocus: function () {
                this.$settingsModal.focus()
                let focusable = $(
                    '#cookie-popup a[href], #cookie-popup details, #cookie-popup button, #cookie-popup input[type="checkbox"]',
                )
                let first = focusable[0]
                let last = focusable[focusable.length - 1]

                this.$el.on('keydown', function (e) {
                  // Close banner if user presses escape key
                    if (e.key === 'Escape') {
                        utilities.hideSettingsModal()
                    }

                    if (e.key !== 'Tab') {
                        return
                    }

                    if (e.shiftKey) { /* shift + tab */
                        if (document.activeElement === first) {
                            last.focus()
                            e.preventDefault()
                        }
                    } else /* tab */ {
                        if (document.activeElement === last) {
                            first.focus()
                            e.preventDefault()
                        }
                    }
                })
            },
            savePreferences: function () {
                CCFW.storage.bannerHidden.set('true')
                CCFW.storage.time.set()
                utilities.hideBanner()
                utilities.hideSettingsModal()
                clearOurCookies(CCFW.storage.allowed.get())
                window.location.reload(false)
                return false
            }
        }

        cacheMainElements.init()
        utilities.init()
        banner.init()
        settingsModal.init()
        CCFW.manageAll(CCFW.storage.allowed.get(), 'init', true)
    })
})(jQuery)

function clearOurCookies(allowList)
{
    if (allowList === null) {
        allowList = []
    }

    // Function to clear our cookies if consent withdrawn
    if (!allowList.includes('ua')) {
        //Google Analytics
        killCookieAndRelated('_ga')
        killCookieAndRelated('_ga_')
        killCookie('_gid')
        killCookieAndRelated('_gat')
    }

    if (!allowList.includes('html')) {
        //Facebook cookies
        killCookie('fr')
        killCookie('tr')
        killCookie('_fbc')
        killCookie('_fbp')
        killCookie('PSUK_source')
    }

    if (!allowList.includes('gclidw')) {
        //Google conversion linker cookies
        killCookie('_gcl_au')
        killCookie('_gcl_dc')
        killCookie('_gcl_aw')
    }
}

function killCookieAndRelated(name)
{
    //function for killing all cookies which start with <name>
    // e.g. _ga will kill of _ga and _ga_123ABC
    killCookie(name)
    const cookies = document.cookie.split(';') // array of cookies
    for (var i = 0; i < cookies.length; i++) {
        let cookie = cookies[i].trim()

        if (!cookie) {
            continue
        }

        let eqPos = cookie.indexOf('=')
        let full_name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie
        if (full_name.substring(0, name.length) === name) {
            killCookie(full_name)
        }
    }
}

function killCookie(name)
{
    let expires_path = 'expires=Sun, 01 May 1707 00:00:00 UTC; path=/;'
    let domain = location.host.split('.')

    // multisite compliant; targets domains
    document.cookie = name + '=; ' + expires_path
    document.cookie = name + '=; ' + expires_path + ' domain=' + location.host
    document.cookie = name + '=; ' + expires_path + ' domain=.' + location.host

    if (domain.length >= 3) {
        domain[0] = ''
        domain = domain.join('.')
        document.cookie = name + '=; ' + expires_path + ' domain=' + domain
    }
}
