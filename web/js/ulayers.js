/* Copyright 2010-2011 Andrew O. Shadoura. All rights reserved.
 This package can be distributed under terms of 2-clause BSD licence,
 or X11 MIT license, whichever suits you better. */

function $$$(id)
{
    return document.getElementById(id);
}

var uLayers = {
    queue:new Array(),
    Drag:{
        map:null
    },
    OSM:{
        url:"http://tile.openstreetmap.org/",

        getTileUrl:function (xyz)
        {
            return uLayers.OSM.url + xyz.z + "/" + xyz.x + "/" + xyz.y + ".png";
        },

        getTile:function (lonlat, zoom)
        {
            var result = {};
            result.x = (Math.floor((lonlat.lon + 180) / 360 * Math.pow(2, zoom)));
            result.y = (Math.floor((1 - Math.log(Math.tan(lonlat.lat * Math.PI / 180) + 1 / Math.cos(lonlat.lat * Math.PI / 180)) / Math.PI) / 2 * Math.pow(2, zoom)));
            result.z = zoom;
            return result;
        },

        getPixelCoords:function (lonlat, zoom)
        {
            var result = {};
            result.x = ((lonlat.lon + 180) / 360 * Math.pow(2, zoom));
            result.y = ((1 - Math.log(Math.tan(lonlat.lat * Math.PI / 180) + 1 / Math.cos(lonlat.lat * Math.PI / 180)) / Math.PI) / 2 * Math.pow(2, zoom));
            return result;
        }
    },

    Map:function (id, provider)
    {
        this.getTile = provider.getTile;
        this.getTileUrl = provider.getTileUrl;
        this.getPixelCoords = provider.getPixelCoords;
        this.origin = {x:0, y:0, z:1};
        this.offset = {x:0, y:0};
        this.dragStart = {x:0, y:0};
        this.dragging = false;
        this.lastmarker = 0;
        this.markerUrl = provider.markerUrl;
        this.markers = [];
        this.container = null;

        this.setOriginXYZ = function (xyz)
        {
            this.origin = xyz;
            this.checkContainer();
            this.updateMap();
        }

        this.setOrigin = function (lonlat, zoom)
        {
            this.origin = this.getTile(lonlat, typeof(zoom) != "undefined" ? zoom : this.origin.z);
            this.checkContainer();
            this.updateMap();
        }

        this.id = id;
        this.map = $$$(id);
        var t = this;
        this.map.onmousedown = function (e)
        {
            uLayers.Drag.startDrag(e, t);
        }
        uLayers.Drag.map = this;

        this.map.style.overflow = "hidden";
        this.map.style.zIndex = 999;
    },

    max:function (a, b)
    {
        if (a > b)
        {
            return a;
        }
        else
        {
            return b;
        }
    }
};

uLayers.Map.prototype.checkContainer = function ()
{
    var s = "ulayers_" + this.map.id + "_zoom_" + this.origin.z;
    var container = $$$(s);
    if (container == null)
    {
        container = document.createElement("div");
        container.id = s;
        container.style.height = "100%";
        container.style.width = "100%";
        container.style.position = "relative";
        container.style.zIndex = 300;
        this.container = container;
        this.map.appendChild(container);
    }
    else
    {
        this.container = container;
        return;
    }
}

uLayers.Drag.startDrag = function (e, o)
{
    document.onmouseup = function (e)
    {
        uLayers.Drag.endDrag(e, o);
    }
    document.onmousemove = function (e)
    {
        uLayers.Drag.drag(e, o);
    }
    o.dragStart.x = e.clientX;
    o.dragStart.y = e.clientY;
    o.dragStart.ox = o.offset.x;
    o.dragStart.oy = o.offset.y;
    o.dragging = true;
    return false;
}

uLayers.Drag.endDrag = function (e, o)
{
    o.dragging = false;
    document.onmouseup = null;
    document.onmousemove = null;
    o.updateMap();
    return false;
}

uLayers.Drag.drag = function (e, o)
{
    if (!o.dragging) return;
    o.offset.x = o.dragStart.ox + e.clientX - o.dragStart.x;
    o.offset.y = o.dragStart.oy + e.clientY - o.dragStart.y;
    o.moveMap();
    //o.updateMap();
    return false;
}

uLayers.Map.prototype.moveMap = function (x, y)
{
    var c = this.container;
    c.style.left = this.offset.x + "px";
    c.style.top = this.offset.y + "px";
}

uLayers.Map.prototype.checkTile = function (xyz, offset)
{
    offset = typeof(offset) != "undefined" ? offset : {x:0, y:0};
    var w = {};
    w.x = xyz.x + offset.x;
    w.y = xyz.y + offset.y;
    w.z = xyz.z;
    var s = "ulayers_" + this.id + "_tile_" + w.x + "_" + w.y + "_" + w.z;
    var o = $$$(s);
    if (o == null)
    {
        uLayers.queue.push({w:w, caller:this, offset:offset});
        setTimeout(uLayers.addTile, 100);
    }
    ;
}

uLayers.Map.prototype.recalcMarker = function (markerId)
{
    var s = "ulayers_" + this.id + "_marker_" + markerId;
    var o = $$$(s);
    var marker = this.markers[markerId];
    var offset = this.getPixelCoords(marker.lonlat, this.origin.z);
    offset.x -= this.origin.x;
    offset.y -= this.origin.y;
    o.style.left = Math.floor((this.map.clientWidth / 2) + (offset.x * 256)) + "px";
    o.style.top = Math.floor((this.map.clientHeight / 2) + (offset.y * 256)) + "px";
}

uLayers.Map.prototype.addMarker = function (lonlat, markerUrl)
{
    var markerId = this.lastmarker++;
    this.markers[markerId] = {
        lonlat:lonlat,
        markerUrl:markerUrl
    };
    var offset = this.getPixelCoords(lonlat, this.origin.z);
    offset.x -= this.origin.x;
    offset.y -= this.origin.y;
    var s = "ulayers_" + this.id + "_marker_" + markerId;
    var o = new Image();
    o.id = s;
    o.name = s;
    if (markerUrl == null)
    {
        markerUrl = this.markerUrl;
    }
    o.src = markerUrl;
    o.style.zIndex = 9999;
    o.style.position = "absolute";
    o.style.overflow = "hidden";
    o.style.left = Math.floor((this.map.clientWidth / 2) + (offset.x * 256)) + "px";
    o.style.top = Math.floor((this.map.clientHeight / 2) + (offset.y * 256)) + "px";
    o.onmousedown = function (e)
    {
        return false;
    }
    this.container.appendChild(o);
    return markerId;
}

uLayers.addTile = function ()
{
    var z = uLayers.queue.shift();
    var w = z.w;
    var caller = z.caller;
    var offset = z.offset;
    var s = "ulayers_" + caller.id + "_tile_" + w.x + "_" + w.y + "_" + w.z;
    var o = $$$(s);
    if (o == null)
    {
        o = new Image();
    }
    else
    {
        return;
    }
    o.id = s;
    o.name = s;
    o.src = caller.getTileUrl(w);
    o.style.zIndex = 1;
    o.style.position = "absolute";
    o.style.overflow = "hidden";
    o.style.left = Math.floor((caller.map.clientWidth / 2) + (offset.x * 256)) + "px";
    o.style.top = Math.floor((caller.map.clientHeight / 2) + (offset.y * 256)) + "px";
    o.onmousedown = function (e)
    {
        return false;
    }
    caller.container.appendChild(o);
}

uLayers.Map.prototype.updateMap = function ()
{
    /* 256: tile height and width , move to constants */
    var t = Math.floor(uLayers.max(this.map.clientHeight / 256 + 1, this.map.clientWidth / 256 + 1) * 1.5) + 1;

    var i, j;

    var w = {};
    w.x = -Math.floor(this.offset.x / 256);
    w.y = -Math.floor(this.offset.y / 256);

    for (i = 0; i < t; i++)
    {
        for (j = 0; j < t; j++)
        {
            this.checkTile(this.origin, {x:i + w.x, y:j + w.y});
            this.checkTile(this.origin, {x:i + w.x, y:-j + w.y});
            this.checkTile(this.origin, {x:-i + w.x, y:j + w.y});
            this.checkTile(this.origin, {x:-i + w.x, y:-j + w.y});
        }
    }
}
