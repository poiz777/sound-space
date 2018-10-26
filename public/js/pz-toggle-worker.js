(function($){
    $(document).ready(function(e){
        let playlistComboCheckBox   = $('.pz-combo-cb input[type=checkbox]');
        let playlistCheckBoxGroup   = $('.pz-single-cb input[type=checkbox]');

        playlistComboCheckBox.on("click", function(e){
            playlistCheckBoxGroup.each(function(cb){
                const main  = $(this);
                if( main.prop('checked') ){
                    main.prop('checked', false);
                }else{
                    main.prop('checked', true);
                }
            });
        });

        console.log(playlistComboCheckBox);
        console.log(playlistCheckBoxGroup);
        console.log("Django!!!!");
    });
})(jQuery);
