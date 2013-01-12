$(function(){

    $beats = $('#beats');
    var beats = [], data_size, first_load = 6, interval;
    var LOAD_INTERVAL = 2 * 1000;

    var fakeAddItems = function() {
        var itemsToLoad = 1 + Math.floor(Math.random() * 3);
        var b = beats.slice(first_load, first_load + itemsToLoad);
        $('#beat-tmpl').tmpl(b)
                       .prependTo($beats);

        first_load += itemsToLoad;

        $beats.isotope( 'reloadItems' ).isotope({ sortBy: 'original-order' });

        if (first_load > data_size - 1)
            clearInterval(interval)

    };

    $.ajax({
        type: 'GET',
        url: '/data/data.json',
        dataType: 'json',
        success: function(data) {
            data_size = data.length;
            $.each(data, function(i, beat) {
                // delete this
                beat.importance = 1 + Math.floor(Math.random()*3)
                beats.push(beat);
            });
        },
        data: {},
        async: false
    });

    $('#beat-tmpl').tmpl(beats.slice(0, first_load)).appendTo($beats);

    $beats.isotope({
        itemSelector : '.item',
        layoutMode: 'masonry'
    });

    window.setInterval(fakeAddItems, LOAD_INTERVAL);




});
