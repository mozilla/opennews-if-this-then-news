Array.prototype.shuffle = function() {
    var i = this.length, j, tempi, tempj;
    if ( i == 0 ) return false;
    while ( --i ) {
        j       = Math.floor( Math.random() * ( i + 1 ) );
        tempi   = this[i];
        tempj   = this[j];
        this[i] = tempj;
        this[j] = tempi;
    }
    return this;
};


$(function(){

    $beats = $('#beats');
    var beats = [], data_size, first_load = 6, interval;
    var LOAD_INTERVAL = 10 * 1000;

    var fakeAddItems = function() {
        var itemsToLoad = 1 + Math.floor(Math.random() * 3);
        var b = beats.slice(first_load, first_load + itemsToLoad);
        console.log(b);
        $('#beat-tmpl').tmpl(b)
                       .hide().prependTo($beats).fadeIn();

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
            $.each(data.shuffle(), function(i, beat) {
                beats.push(beat);
            });
        },
        data: {},
        async: false
    });

    $('#beat-tmpl').tmpl(beats.slice(0, first_load)).hide().appendTo($beats).fadeIn();

    $beats.isotope({
        itemSelector : '.item',
        layoutMode: 'masonry'
    });

    interval = window.setInterval(fakeAddItems, LOAD_INTERVAL);




});
