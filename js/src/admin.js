var gmap = (function ($, undefined) {
    /**
     * Globals
     */
    this.timer = false;

    /**
     * @param input
     */
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#preview').attr('src', e.target.result);
            };

            reader.readAsDataURL(input.files[0]);
            $('#preview').removeClass('no-img').addClass('preview');
            $('#drop-zone').removeClass('no-img');
        }
    }

    function initDropZone() {
        var dropZoneId = "drop-zone";
        var buttonId = "clickHere";
        var mouseOverClass = "mouse-over";
        var btnSend = $("#" + dropZoneId).find('button[type="submit"]');

        var dropZone = $("#" + dropZoneId);
        var ooleft = dropZone.offset().left;
        var ooright = dropZone.outerWidth() + ooleft;
        var ootop = dropZone.offset().top;
        var oobottom = dropZone.outerHeight() + ootop;
        var inputFile = dropZone.find('input[type="file"]');
        document.getElementById(dropZoneId).addEventListener("dragover", function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.addClass(mouseOverClass);
            var x = e.pageX;
            var y = e.pageY;

            if (!(x < ooleft || x > ooright || y < ootop || y > oobottom)) {
                inputFile.offset({ top: y - 15, left: x - 100 });
            } else {
                inputFile.offset({ top: -400, left: -400 });
            }

        }, true);

        if (buttonId != "") {
            var clickZone = $("#" + buttonId);

            var oleft = clickZone.offset().left;
            var oright = clickZone.outerWidth() + oleft;
            var otop = clickZone.offset().top;
            var obottom = clickZone.outerHeight() + otop;

            $("#" + buttonId).mousemove(function (e) {
                var x = e.pageX;
                var y = e.pageY;
                if (!(x < oleft || x > oright || y < otop || y > obottom)) {
                    inputFile.offset({ top: y - 15, left: x - 160 });
                } else {
                    inputFile.offset({ top: -400, left: -400 });
                }
            });
        }

        $("#" + dropZoneId).find('input[type="file"]').change(function(){
            var inputVal = $(this).val();
            if(inputVal === '') {
                $(btnSend).prop('disabled',true);
            } else {
                $(btnSend).prop('disabled',false);
            }
        });

        document.getElementById(dropZoneId).addEventListener("drop", function (e) {
            $("#" + dropZoneId).removeClass(mouseOverClass);
        }, true);
    }

    /**
     * updateTimer
     * @param ts
     * @param func
     */
    function updateTimer(ts, func) {
        if (this.timer) clearTimeout(this.timer);
        this.timer = setTimeout(func, ts ? ts : 1000);
    }

    /**
     * watch fields
     * @param field
     */
    function watch(field) {
        field.keypress(function () {
            updateTimer('', 'gmap.initMapConfig();');
        }).change(function () {
            updateTimer(100, 'gmap.initMapConfig();');
        });
    }

    /**
     * Retreive lat and lng of the address
     */
    function loadMapConfig(){
        $('.tab-pane').each(function(){
            var self = $(this),
                addr = self.find('.address').val(),
                postc = self.find('.postcode').val(),
                city = self.find('.city').val(),
                country = self.find('.country').val();

            if( addr !== '' && postc !== '' && city !== '' ) {
                var adr = addr +', '+ postc +' '+ city + ', ' + country;

                self.find('.map-col')
                    .gmap3()
                    .latlng({
                        address: adr
                    }).then(function(latlng){
                    self.find(".lat").val(latlng.lat());
                    self.find(".lng").val(latlng.lng());
                });
            }
        });
    }

    return {
        run: function() {
            $('.csspicker').colorpicker();
        },
        addAddress: function() {
            if ($(".map-col").length != 0) {
                watch($('.address'));
                watch($('.city'));
                watch($('.postcode'));

                var defaultLabel = $('span#input-label').text();

                $('.inputfile').each(function() {
                    var label	 = $('span#input-label'),
                        labelVal = label.innerHTML;

                    $(this).on( 'change', function( e )
                    {
                        var fileName = e.target.value;

                        if( fileName != '' ) {
                            label.text(fileName);
                            if($(this).hasClass('inputpdf')) {
                                $(this).next('label').addClass('filled').find('.fa-inverse').toggleClass('fa-upload').toggleClass('fa-file-pdf-o');
                            }
                        }
                        else {
                            label.text(labelVal);
                        }
                    });

                    // Firefox bug fix
                    $(this).on( 'focus', function(){ $(this).addClass( 'has-focus' ); });
                    $(this).on( 'blur', function(){ $(this).removeClass( 'has-focus' ); });
                });

                $("#img").change(function(){
                    readURL(this);
                    if(typeof $('.resetImg') !== 'undefined') {
                        $('.resetImg').removeClass('hide');
                    }
                });

                $('.resetImg').click(function(e){
                    e.preventDefault();
                    $(this).addClass('hide');
                    $("#img").val('');
                    $('#preview').attr('src', '#').addClass('no-img').removeClass('preview');
                    $( 'span#input-label' ).text(defaultLabel);
                    return false;
                });

                initDropZone();
            }
        },
        initMapConfig: function () {
            loadMapConfig();
        }
    };
})(jQuery);