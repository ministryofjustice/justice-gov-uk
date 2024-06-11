import { CCFW, init } from './ccfw-gtm'
import { frontend } from './ccfw-frontend'

/** start GTM **/
const gtmIsReady = init();

(function ($) {
    /** In a nutshell, if jQuery isn't available here, we cannot run. **/
    if (typeof $ === undefined) {
        return
    }

    $(function () {
        if (gtmIsReady) {
            frontend.init($)
            /** cache available allowlist identifiers and set up listener **/
            CCFW.cache()

            /** set up toggle listeners **/
            CCFW.listen.toggles()

            /** A button to save cookies at the top **/
            CCFW.patch.popup.button.save()

            /** house cleaning; check if one year has passed **/
            CCFW.maybeExpired()
        } else {
            console.log('CCFW: GTM was not initialised.')
        }
    })
})(jQuery)
