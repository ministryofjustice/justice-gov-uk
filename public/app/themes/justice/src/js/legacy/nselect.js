import jQuery from 'jquery'

/*
  replaces select tags with custom versions
  optional arg allows this to run on a separate piece of html (e.g. ajax calls)
*/
export default function nselect(html)
{
    let offset = 0

    if (typeof (html) == 'undefined' || html.length === 0) {
        html = jQuery('body')
    } else {
        offset = jQuery('[id^="nselect"]').length
    }

    jQuery(html).find('select').each(function (i) {
        let _select = jQuery(this)
        if (typeof _select.attr('class') != 'undefined' && _select.attr('class').indexOf('no-nselect') > -1) {
            return
        }

        const pos = i + offset
        const nselect = '#nselect' + pos
        const _nselect_current = jQuery(nselect + ' .current')

        //hide select
        _select.hide()
        //build
        _select.after('<div id="nselect' + pos + '" class="nselect" style="z-index:' + (1000 - pos) + ';" tabindex="0"><div class="current"></div><ul class="inner-list" style="display:none;"></ul></div>')

        // We have DIV and UL elements, declare jQuery objects
        const _nselect = jQuery(nselect)
        const _nselect_ul = jQuery(nselect + ' ul')

        let selected_val = ''
        _select.children('option').each(function (j) {
            const _this = jQuery(this)

            // Migration edit - wrap value in encodeURIComponent to handle special characters and prevent XSS.
            _nselect_ul.append('<li class="option' + j + '"><span>' + _this.html() + '</span><input type="hidden" value="' + encodeURIComponent(_this.val()) + '" /></li>')

            if (_this.is(':selected')) {
                selected_val = _this.html()
            }
        })

        // We have LI elements, declare jQuery object
        const _nselect_li = jQuery(nselect + ' ul li')

        if (selected_val.length === 0) {
            selected_val = jQuery(nselect + ' ul li.option0').html()
        }

        _nselect.children('.current').html(selected_val)
        //events
        _nselect.focus(function () {
            _nselect_ul.show()
        })

        _nselect.blur(function () {
            _nselect_ul.hide()
            _nselect_ul.children('li.hover').removeClass('hover')
            _nselect_ul.children('li.selected').addClass('hover')
        })

        _nselect.click(function () {
            _nselect.focus()
        })

        _nselect.keydown(function (e) {
            switch (e.which) {
                case 13: // enter
                    e.preventDefault()
                    _nselect_ul.children('li.hover').click()
                break
                case 32: // space
                    e.preventDefault()
                    _nselect_ul.toggle()
                break
                case 38: // arrow-up
                    e.preventDefault()
                    if (!_nselect_ul.is(':visible')) {
                        _nselect_ul.show()
                        break
                    }

                    if (_nselect_ul.children('li.hover').length > 0) {
                        let ind = _nselect_ul.children('li.hover').index()
                        if (ind > 0) {
                            jQuery(_nselect_ul.children('li')[ind]).removeClass('hover')
                            jQuery(_nselect_ul.children('li')[ind - 1]).addClass('hover')
                        } else {
                            jQuery(_nselect_ul.children('li')[ind]).removeClass('hover')
                            jQuery(_nselect_ul.children('li')[_nselect_ul.children('li').length - 1]).addClass('hover')
                        }
                    } else {
                        jQuery(_nselect_ul.children('li')[_nselect_ul.children('li').length - 1]).addClass('hover')
                    }
                break
                case 40: // arrow-down
                    e.preventDefault()
                    if (!_nselect_ul.is(':visible')) {
                        _nselect_ul.show()
                        break
                    }
                    if (_nselect_ul.children('li.hover').length > 0) {
                        let ind = _nselect_ul.children('li.hover').index()
                        if (ind < _nselect_ul.children('li').length - 1) {
                            jQuery(_nselect_ul.children('li')[ind]).
                            removeClass('hover')
                            jQuery(_nselect_ul.children('li')[ind + 1]).
                            addClass('hover')
                        } else {
                            jQuery(_nselect_ul.children('li')[ind]).
                            removeClass('hover')
                            jQuery(_nselect_ul.children('li')[0]).addClass('hover')
                        }
                    } else {
                        jQuery(_nselect_ul.children('li')[0]).
                        addClass('hover')
                    }
                break
            }
            if (e.which >= 65 && e.which <= 90) {
                let current = _nselect_ul.children('li.hover').length > 0
                ? _nselect_ul.children('li.hover').index()
                : -1
                let stop = false
                let ii = current

                for (ii + 1; ii < _nselect_ul.children('li').length; ii++) {
                    let _this = _nselect_ul.children('li')[ii]
                    if (jQuery(_this).html().charAt(6).toLowerCase() === String.fromCharCode(e.which).toLowerCase()) {
                        _nselect_ul.show()
                        _nselect_ul.children('li.hover').removeClass('hover')
                        jQuery(_nselect_ul.children('li')[ii]).addClass('hover')
                        stop = true
                        break
                    }
                }

                if (!stop) {
                    let iii = 0
                    for (iii; iii <= current; iii++) {
                        let _this = _nselect_ul.children('li')[iii]
                        if (jQuery(_this).html().charAt(6).toLowerCase() === String.fromCharCode(e.which).toLowerCase()) {
                              _nselect_ul.show()
                              _nselect_ul.children('li.hover').removeClass('hover')
                              jQuery(_nselect_ul.children('li')[iii]).addClass('hover')
                              break
                        }
                    }
                }
            }

            // Scroll to element position
            const ind = _nselect_ul.children('li.hover').index()
            let h = 0
            _nselect_ul.children('li').each(function (i) {
                if (i === ind) {
                    return false
                }
                if (i > 0) {
                    h += jQuery(this).outerHeight()
                }
            })

            _nselect_ul.scrollTop(h)
        })

        _nselect_ul.children('li').hover(function () {
            _nselect_ul.children('li.hover').removeClass('hover')
            jQuery(this).addClass('hover')
        })

        _nselect_li.on('click', function (e) {
            const _this = jQuery(this)
            e.stopPropagation()
            _select.val(_this.children('input').val())
            _nselect_current.html(_this.children('span').html())
            _nselect_ul.hide()
            _select.trigger('change')
            _nselect_ul.children('li.hover').removeClass('hover')
            _nselect_ul.children('li.selected').removeClass('selected')
            _this.addClass('selected').addClass('hover')
        })

        _select.on('change', function () {
            _nselect_current.html(jQuery(this).children('option:selected').html())
        })

        //move 'device-only' class
        if (_select.hasClass('device-only')) {
            _select.removeClass('device-only')
            _nselect.addClass('device-only')
        }
    })
}
