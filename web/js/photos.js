//joindin object created to hold function to avoid
//unnecessary vars in global scope
var photos = {
    /**
     * Calls event controller to retrieve machine-tagged photo
     * data in JSON format.  If photos are present, HTML elements
     * are constructed and injected into the DOM
     *
     */
    getTaggedPhotos:function (data) {
        var list = '';
        if (data.stat != 'fail') {
            data.photos.photo.forEach(function(photo) {
                var farm_id = photo.farm;
                var server_id = photo.server;
                var photo_id = photo.id;
                var owner_id = photo.owner;
                var secret = photo.secret;
                var title = photo.title;
                var photo = '<img src="http://farm'+farm_id+'.staticflickr.com/'+server_id+'/'+photo_id+'_'+secret+'_t.jpg" alt="'+title+'"/>';
                var link = '<a href="https://www.flickr.com/photos/'+owner_id+'/'+photo_id+'" target="_blank">'+photo+'</a>';
                list += '<li>'+link+'</li>';
            });
            document.getElementById('thumbnails').innerHTML = list;
        }
    }
};