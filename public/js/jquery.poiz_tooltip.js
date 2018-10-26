;
(function ($) {

    $.fn.cs_tooltip = function(options){
        var defaults = {
            hover_duration:3000,
            bg_width:200,
            bg_height:305,
            hover_alpha:1,
            easing:"easeInOutSine",
            style_object: null,
            tooltip_resource_attribute:"title",
            use_tooltip:true,
            add_click_evt:false

        };
        var main        =   $(this);
        var config      =   $.extend({}, defaults, options);
        var title_attr  =   null;
        var relX        =   null;
        var relY        =   null;
        //console.log(main);

        /**********************************************************************/

        var cTip = {
            init: function(){
                main.hover(this.overFX, this.outFX);
                main.mousemove(this.mouseMoveAction);
                if(config.add_click_evt){
                    main.click( this.outFX);

                }
            },

            mouseMoveAction:function(e){
                var lbl_div = $("div.label_div");
                relX        = e.pageX-( lbl_div.outerWidth()/2);
                //relX = e.pageX+10;
                relY = e.pageY+15;

                lbl_div.css({
                    top:relY + "px",
                    left:relX + "px"
                });
            },

            overFX: function(e){
                var dis     = $(this);
                if( !dis.attr('data-blind')){ //(title_attr  = $(dis).attr(config.tooltip_resource_attribute) )
                    title_attr  = $(dis).attr(config.tooltip_resource_attribute);
                    var lbl_div = $('div.label_div');
                    relX        = e.pageX-( lbl_div.outerWidth()/2);
                    //relX        = e.pageX+10;
                    relY        = e.pageY+15;
                    title_attr  = title_attr.replace("\n", "<br />");

                    dis.data('title', title_attr );
                    dis.attr('title', '');

                    $('.label_div').remove();
                    $("<div />", {
                        html: dis.data('title'),
                        "class": "label_div"
                    }).appendTo("body");

                    if( dis.attr('pix_data') ){
                        $("<img />", {
                            "src": dis.attr('pix_data'),
                            "class": "tooltip_pix"
                        }).css({
                                "float":"left",
                                "clear":"both",
                                width:"50px"
                            }).appendTo("div.label_div");
                    }
                    if(config.style_object != null){
                        $('div.label_div').css(
                            config.style_object
                        );
                    }else{
                        $("div.label_div").css({
                            padding:"10px",
                            background:"rgba(10, 18, 200, 0.5)",
                            borderRadius:"5px",
                            textAlign:"center"
                        })
                    }

                    $("div.label_div").css({
                        position:"absolute",
                        top:relY + "px",
                        left:relX + "px",
                        zIndex:999999,
                        backgroundPosition: "50% 50%"
                    }).hide();
                    $("div.label_div").fadeIn({"duration":250, "easing":"easeInOutSine"});
                }
            },

            outFX: function(evt){
                var dis = $(this);
                if( !dis.attr('data-blind')){ //(title_attr  = $(dis).attr(config.tooltip_resource_attribute) )
                    //dis.attr('title', dis.data('title'));
                    $("div.label_div").fadeOut({"duration":150, "easing":"easeInOutSine"});
                }
            }

        };
        cTip.init();

    };

}(jQuery) );

/**
 * string(6) "logout"
 object(JRegistry)#315 (1) {
  ["data":protected]=>
  object(stdClass)#316 (16) {
    ["pretext"]=>
    string(0) ""
    ["posttext"]=>
    string(0) ""
    ["login"]=>
    string(3) "133"
    ["logout"]=>
    string(3) "101"
    ["greeting"]=>
    string(1) "0"
    ["name"]=>
    string(1) "0"
    ["usesecure"]=>
    string(1) "0"
    ["usetext"]=>
    string(1) "0"
    ["layout"]=>
    string(9) "_:default"
    ["moduleclass_sfx"]=>
    string(0) ""
    ["cache"]=>
    string(1) "0"
    ["module_tag"]=>
    string(3) "div"
    ["bootstrap_size"]=>
    string(1) "0"
    ["header_tag"]=>
    string(2) "h3"
    ["header_class"]=>
    string(0) ""
    ["style"]=>
    string(1) "0"
  }
}
 */