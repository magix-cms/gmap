const gmap = (($, undefined) => {
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

    return {
        run: function() {
            $('.csspicker').colorpicker();
        },
        addAddress: function() {
            if ($(".map-col").length != 0) {
                var defaultLabel = $('span#input-label').text();

                $('.inputfile').each(function() {
                    var label	 = $('span#input-label'),
                        labelVal = label.innerHTML;

                    $(this).on( 'change', function( e ) {
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
        }
    };
})(jQuery);

class GoogleMap {
    /**
     * @param {Element} tab
     * @param {Object} Libraries
     */
    constructor(tab, Libraries) {
        this.g = Libraries;
        this.timer = null;
        this.tab = tab;
        this.addr = this.tab.querySelector('.address');
        this.postc = this.tab.querySelector('.postcode');
        this.city = this.tab.querySelector('.city');
        this.country = this.tab.querySelector('.country');
        this.lat = this.tab.querySelector('.lat');
        this.lng = this.tab.querySelector('.lng');
        this.init();
    }

    setLatLng() {
        let GM = this;
        let address = GM.addr.value +', '+ GM.postc.value +' '+ GM.city.value + ', ' + GM.country.value;
        let geocoder = new GM.g.Geocoder();
        geocoder.geocode( { 'address' : address }, ( results, status ) => {
            if( status === google.maps.GeocoderStatus.OK ) {
                GM.lat.value = results[0].geometry.location.lat();
                GM.lng.value = results[0].geometry.location.lng();
            }
        });
    }

    /**
     * @param {function} callback
     * @param {int} timeout
     */
    updateTimer(callback,timeout) {
        let GM = this;
        if (this.timer) clearTimeout(this.timer);
        this.timer = setTimeout(callback, timeout ? timeout : 1000);
    }

    /**
     * @param {HTMLInputElement} field
     */
    watch(field) {
        let GM = this;
        field.addEventListener('keyup',() => {
            GM.updateTimer(() => {
                GM.setLatLng();
            });
        });
        field.addEventListener('focusout',() => {
            GM.updateTimer(() => {
                GM.setLatLng();
            },100);
        });
        field.addEventListener('change',() => {
            GM.updateTimer(() => {
                GM.setLatLng();
            },100);
        });
    }

    init() {
        let GM = this;
        GM.watch(GM.addr);
        GM.watch(GM.postc);
        GM.watch(GM.city);
    }
}

async function initMap() {
    const { Geocoder } = await google.maps.importLibrary("geocoding");

    let tabs = document.querySelectorAll('.tab-pane');
    tabs.forEach((tab) => {
        tab.GM = new GoogleMap(tab, {Geocoder: Geocoder});
    });
}

(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
    key: configMap.api_key,
    v: "weekly",
    lang: configMap.lang
    // Use the 'v' parameter to indicate the version to use (weekly, beta, alpha, etc.).
    // Add other bootstrap parameters as needed, using camel case.
});

initMap();