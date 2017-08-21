/* ========================================================================
 * Bootstrap: popover.js v3.2.0
 * http://getbootstrap.com/javascript/#popovers
 * ========================================================================
 * Copyright 2011-2014 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */


+function ($) {
  'use strict';

  // POPOVER PUBLIC CLASS DEFINITION
  // ===============================

  var Tutpop = function (element, options) {
    this.init('popover', element, options)
  }

  if (!$.fn.tooltip) throw new Error('Tutpop requires tooltip.js')

  Tutpop.VERSION  = '3.2.0'

  Tutpop.DEFAULTS = $.extend({}, $.fn.tooltip.Constructor.DEFAULTS, {
    placement: 'right',
    trigger: 'manual',
    container: 'body',
    content: '',
    animate: true,
    template: '<div class="popover tutpop" role="tooltip"><div class="arrow"></div><h3 class="popover-title"><i class="icon-support"></i> <span></span></h3><div class="popover-content"><span></span><div class="buttons"><button type="button" class="btn btn-close btn-default btn-sm">チュートリアルを閉じる</button></div></div></div>'
  })


  // NOTE: POPOVER EXTENDS tooltip.js
  // ================================

  Tutpop.prototype = $.extend({}, $.fn.tooltip.Constructor.prototype)

  Tutpop.prototype.constructor = Tutpop

  Tutpop.prototype.getDefaults = function () {
    return Tutpop.DEFAULTS
  }

  Tutpop.prototype.setContent = function () {
    var $el     = this.$element
    var $tip    = this.tip()
    var title   = this.getTitle()
    var content = this.getContent()

    $tip.find('.popover-title span')[this.options.html ? 'html' : 'text'](title)
    $tip.find('.popover-content span').empty()[ // we use append for html objects to maintain js events
      this.options.html ? (typeof content == 'string' ? 'html' : 'append') : 'text'
    ](content)
    $tip.find('.btn-close').click(function() {
      $el.trigger($.Event('close.tutpop'))
    })

    $tip.removeClass('fade top bottom left right in')

    if (this.options.animate)
      $tip.addClass('animate')

    // IE8 doesn't accept hiding via the `:empty` pseudo selector, we have to do
    // this manually by checking the contents.
    if (!$tip.find('.popover-title').html()) $tip.find('.popover-title').hide()
  }

  Tutpop.prototype.hasContent = function () {
    return this.getTitle() || this.getContent()
  }

  Tutpop.prototype.getContent = function () {
    var $e = this.$element
    var o  = this.options

    return $e.attr('data-content')
      || (typeof o.content == 'function' ?
            o.content.call($e[0]) :
            o.content)
  }

  Tutpop.prototype.arrow = function () {
    return (this.$arrow = this.$arrow || this.tip().find('.arrow'))
  }

  Tutpop.prototype.tip = function () {
    if (!this.$tip) this.$tip = $(this.options.template)
    return this.$tip
  }


  // POPOVER PLUGIN DEFINITION
  // =========================

  function Plugin(option) {
    return this.each(function () {
      var $this   = $(this)
      var data    = $this.data('tutpop')
      var options = typeof option == 'object' && option

      if (!data && option == 'destroy') return
      if (!data) $this.data('tutpop', (data = new Tutpop(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  var old = $.fn.tutpop

  $.fn.tutpop             = Plugin
  $.fn.tutpop.Constructor = Tutpop


  // POPOVER NO CONFLICT
  // ===================

  $.fn.tutpop.noConflict = function () {
    $.fn.tutpop = old
    return this
  }

}(jQuery);
