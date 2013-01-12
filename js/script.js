$(function(){

    $beats = $('#beats');
    var beats = [];
    $.ajax({
        type: 'GET',
        url: '../data/data.json',
        dataType: 'json',
        success: function(data) {
            $.each(data, function(i, beat) {
                beats.push(beat);
            });
        },
        data: {},
        async: false
    });

    $('#beat-tmpl').tmpl(beats).appendTo($beats);

    $beats.isotope({
        itemSelector:'.item',
        layoutMode:'masonry'
    });




    var types = [];
    $.ajax({
        type: 'GET',
        url: '../data/beats.json',
        dataType: 'json',
        success: function(data) {
            $.each(data, function(i, type) {
                types.push(type);
            });
        },
        data: {},
        async: false
    });
    $menu = $('#type-menu');
    $('#menu-tmpl').tmpl({types: types}).appendTo($menu);

});
