function foyer_setup_slide_classes(){jQuery(foyer_slides_selector).children().addClass("foyer-slide-group-1")}function foyer_setup_display(){jQuery(this).css("cursor","none"),major_refresh_timeout=setTimeout(foyer_display_reload_window,288e5),foyer_loader_intervalObject=window.setInterval(foyer_load_display_data,3e4)}function foyer_load_display_data(){var e,r;jQuery(".foyer-slide-group-1").length?jQuery(".foyer-slide-group-2").length||(r="foyer-slide-group-2",e="foyer-slide-group-1"):(r="foyer-slide-group-1",e="foyer-slide-group-2"),r.length&&jQuery.get(window.location,function(o){if($new_html=jQuery(jQuery.parseHTML(o)),$new_html.find(foyer_channel_selector).attr("class")!==jQuery(foyer_channel_selector).attr("class"))foyer_fader_shutdown_slideshow(foyer_replace_channel,$new_html.find(foyer_channel_selector));else{var s=$new_html.find(foyer_slides_selector).children().addClass(r);1===jQuery(foyer_slides_selector).children().length&&1===$new_html.find(foyer_slides_selector).children().length?(jQuery(foyer_slides_selector).html(s),foyer_fader_activate_first_slide()):(jQuery(foyer_slides_selector).children().last().after(s),jQuery(foyer_slides_selector).find("."+r).first().attrChange(function(o){jQuery(foyer_slides_selector).find("."+r).first().attrChange(function(r){jQuery(foyer_slides_selector).find("."+e).remove()})}))}})}function foyer_replace_channel(e){jQuery(foyer_channel_selector).replaceWith(e),foyer_setup_slide_classes(),foyer_fader_setup_slideshow()}function foyer_display_reload_window(){window.location.reload()}function foyer_fader_setup_slideshow(){foyer_fader_activate_first_slide(),foyer_fader_set_timeout()}function foyer_fader_activate_first_slide(){jQuery(foyer_slide_selector).first().removeClass("next").addClass("active"),jQuery(foyer_slide_selector).first().next().addClass("next")}function foyer_fader_set_timeout(e){var r=parseFloat(jQuery(foyer_slide_selector+".active").data("foyer-slide-duration"));!r>0&&(r=5),setTimeout(foyer_fader_next_slide,1e3*r)}function foyer_fader_next_slide(){var e=jQuery(foyer_slide_selector+".active"),r=jQuery(foyer_slide_selector).length,o=jQuery(foyer_slide_selector).index(e)+1;o>=r&&(o=0);var s=o+1;s>=r&&(s=0),e.removeClass("active"),foyer_fader_shutdown?(foyer_fader_shutdown=!1,foyer_fader_shutdown_callback(foyer_fader_shutdown_callback_options)):(jQuery(foyer_slide_selector).eq(o).removeClass("next").addClass("active"),jQuery(foyer_slide_selector).eq(s).addClass("next"),foyer_fader_set_timeout())}function foyer_fader_shutdown_slideshow(e,r){foyer_fader_shutdown=!0,foyer_fader_shutdown_callback=e,foyer_fader_shutdown_callback_options=r}var foyer_channel_selector=".foyer-channel",foyer_slides_selector=".foyer-slides",foyer_slide_selector=".foyer-slide";jQuery(window).load(function(){foyer_setup_display(),foyer_setup_slide_classes(),foyer_fader_setup_slideshow()}),jQuery(function(){!function(e){var r=window.MutationObserver||window.WebKitMutationObserver||window.MozMutationObserver;e.fn.attrChange=function(e){if(r){var o={subtree:!1,attributes:!0},s=new r(function(r){r.forEach(function(r){s.disconnect(),e.call(r.target,r.attributeName)})});return this.each(function(){s.observe(this,o)})}}}(jQuery)});var foyer_fader_shutdown=!1,foyer_fader_shutdown_callback,foyer_fader_shutdown_callback_options;