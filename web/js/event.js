$(document).ready(function() {
    joindin.getTaggedPhotos();
});

//joindin object created to hold function to avoid
//unnecessary vars in global scope
var joindin = {
    /**
     * Calls event controller to retrieve machine-tagged photo
     * data in JSON format.  If photos are present, HTML elements
     * are constructed and injected into the DOM
     *
     */
    getTaggedPhotos:function () {
        var src = './photos/' + event.slug;
        $.getJSON(src,
            function(result) {
                var list = $('<ul />');
                var photos = result.photos.photo;
                for(var i in photos) {
                    var farm_id= photos[i].farm;
                    var server_id = photos[i].server;
                    var photo_id = photos[i].id;
                    var owner_id = photos[i].owner;
                    var secret = photos[i].secret;
                    var photo = '<img src="http://farm'+farm_id+'.staticflickr.com/'+server_id+'/'+photo_id+'_'+secret+'_t.jpg" />';
                    var link = '<a href="https://www.flickr.com/photos/'+owner_id+'/'+photo_id+'" target="_blank">'+photo+'</a>';
                    list.append('<li>'+link+'</li>');
                }
                $('#thumbnails').html(list);
            }
        );
    }
};