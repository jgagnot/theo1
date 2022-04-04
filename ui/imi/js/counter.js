/*

 <div class="container text-center" id="container">
    <span class="timer count-number" data-from="900" data-to="100" data-speed="9000">0</span>
 </div>

 <!--
 Les data à passer en attributs
 from: 0,               // nombre initial
 to: 0,                 // nombre final
 speed: 1000,           // temps d'exécution (en ms)
 refreshInterval: 100,  // pas d'incrémentation (si le temps d'exécution est précisé, il prend le dessus sur ce pas)
 decimals: 0,           // nombre de décimales
 onUpdate: null,        // méthode appelée après chaque update
 onComplete: null       // méthode appelée lorsque le compteur a terminé
 -->

 <script>
   $(".count-number").counter({
    from: 0,               // nombre initial
    to: 0,                 // nombre final
    speed: 1000,           // temps d'exécution (en ms)
    refreshInterval: 100,  // pas d'incrémentation (si le temps d'exécution est précisé, il prend le dessus sur ce pas)
    decimals: 0,           // nombre de décimales
    onUpdate: null,        // méthode appelée après chaque update
    onComplete: null       // méthode appelée lorsque le compteur a terminé
   });
 </script>

 */

(function ($) {
    $.fn.countTo = function (options) {
        options = options || {};

        return $(this).each(function () {
            var settings = $.extend({}, $.fn.countTo.defaults, {
                from:            $(this).data('from'),
                to:              $(this).data('to'),
                speed:           $(this).data('speed'),
                refreshInterval: $(this).data('refresh-interval'),
                decimals:        $(this).data('decimals')
            }, options);

            var loops = Math.ceil(settings.speed / settings.refreshInterval),
                increment = (settings.to - settings.from) / loops;

            var self = this,
                $self = $(this),
                loopCount = 0,
                value = settings.from,
                data = $self.data('countTo') || {};

            $self.data('countTo', data);

            if (data.interval) {
                clearInterval(data.interval);
            }
            data.interval = setInterval(updateTimer, settings.refreshInterval);

            render(value);

            function updateTimer() {
                value += increment;
                loopCount++;

                render(value);

                if (typeof(settings.onUpdate) == 'function') {
                    settings.onUpdate.call(self, value);
                }

                if (loopCount >= loops) {
                    // remove the interval
                    $self.removeData('countTo');
                    clearInterval(data.interval);
                    value = settings.to;

                    if (typeof(settings.onComplete) == 'function') {
                        settings.onComplete.call(self, value);
                    }
                }
            }

            function render(value) {
                var formattedValue = settings.formatter.call(self, value, settings);
                $self.html(formattedValue);
            }
        });
    };

    $.fn.countTo.defaults = {
        from: 0,
        to: 0,
        speed: 1000,
        refreshInterval: 100,
        decimals: 0,
        formatter: formatter,
        onUpdate: null,
        onComplete: null
    };

    function formatter(value, settings) {
        return value.toFixed(settings.decimals);
    }
}(jQuery));

$('.count-number').data('countToOptions', {
    formatter: function (value, options) {
        return value.toFixed(options.decimals).replace(/\B(?=(?:\d{3})+(?!\d))/g, ' ');
    }
});

$.fn.counter = function(options) {
    var $this = $(this);
    options = $.extend({}, options || {}, $this.data('countToOptions') || {});
    $this.countTo(options);
};