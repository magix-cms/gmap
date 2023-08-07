<div id="block-gmap" class="gmap block">
    <div class="map">
        <div>
            <div id="map_adress" class="gmap3"></div>
        </div>
        <div id="gmap-address" class="open">
            <div id="searchdir" class="collapse">
                <form class="form-search">
                    <div class="input-group">
                        <input type="text" class="form-control" id="getadress" name="getadress" placeholder="{#gmap_adress#}" value="" />
                        <div class="input-group-btn">
                            <button class="btn btn-default subdirection" type="submit">
                                <i class="material-icons ico ico-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="alert alert-primary" itemscope itemtype="http://data-vocabulary.org/Organization">
                <a id="showform" class="btn btn-lg pull-right collapsed hidden-ph hidden-xs" type="button" data-toggle="collapse" data-target="#searchdir" aria-expanded="false" aria-controls="searchdir">
                    <span class="open"><i class="material-icons ico ico-directions"></i></span>
                    <span class="close"><i class="material-icons ico ico-close"></i></span>
                </a>
                {strip}<a id="openapp" class="btn btn-lg pull-right visible-ph visible-xs"
                    {if $mOS === 'IOS'} href="http://maps.apple.com/maps?ll={$addresses[0].lat_address},{$addresses[0].lng_address}&q={$addresses[0].address_address|escape:'url'}%2C%20{$addresses[0].city_address|escape:'url'}%2C%20{$addresses[0].country_address|escape:'url'}"
                    {else} href="geo:{$addresses[0].lat_address},{$addresses[0].lng_address}?q={$addresses[0].address_address|escape:'url'}%2C%20{$addresses[0].city_address|escape:'url'}%2C%20{$addresses[0].country_address|escape:'url'}"{/if} target="_blank">
                        <i class="material-icons ico ico-directions"></i>
                    </a>{/strip}
                <button class="btn btn-default btn-box hidepanel open">
                    <span class="show-less ver"><i class="material-icons ico ico-keyboard_arrow_up"></i></span>
                    <span class="show-more ver"><i class="material-icons ico ico-keyboard_arrow_down"></i></span>
                    <span class="show-less hor"><i class="material-icons ico ico-keyboard_arrow_left"></i></span>
                    <span class="show-more hor"><i class="material-icons ico ico-keyboard_arrow_right"></i></span>
                </button>
                <meta itemprop="name" content="{$addresses[0].company_address}" />
                <div id="address" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
                    <span class="fa fa-map-marker"></span>
                    <span class="address" itemprop="streetAddress">{$addresses[0].address_address}</span>,
                    <span itemprop="addressLocality">
                        <span class="city">{$addresses[0].postcode_address} {$addresses[0].city_address}</span>, <span class="country">{$addresses[0].country_address}</span>
                    </span>
                    <div itemprop="address" itemscope itemtype="http://schema.org/GeoCoordinates">
                        <meta itemprop="latitude" content="{$addresses[0].lat_address}" />
                        <meta itemprop="longitude" content="{$addresses[0].lng_address}" />
                    </div>
                </div>
            </div>
            <div id="r-directions"></div>
        </div>
    </div>
</div>