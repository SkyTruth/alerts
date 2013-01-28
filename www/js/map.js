var map;
var geocoder = new google.maps.Geocoder();
var infoWindow = new google.maps.InfoWindow( {content:""} );
var cookiename = 'alerts_map';
var cookie_expire_days = 7;     
var markers = {};
var maxCachedMarkers = 100;

var mapOptions = {
      zoom: 4,
      center: new google.maps.LatLng(37.0625, -95.677068),
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      panControl: true,
      zoomControl: true,
      scaleControl: true,
      scaleControlOptions: {
        position: google.maps.ControlPosition.LOWER_LEFT
      },      
      mapTypeControl: true,
      bounds: null,
      feedParams: { d: '30'}
    };
var currentMapBouds = null;
var markerImages = {}

    
function subscribe (medium) {
    window.location.href= "/subscribe?l=" + map.getBounds().toUrlValue(4) + "#" + medium;
}

function getCurrentMapBounds () {
    return map.getBounds().toUrlValue(4);
}

function zoomToLocation(location) {
    if (geocoder) {
        geocoder.geocode(  { 'address': location }, 
            function(results, status) {
                 if (status == google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                      map.setZoom(11);
                      marker = new google.maps.Marker({
                          position: results[0].geometry.location,
                          map: map
                      });
                      infoWindow.setContent(results[0].formatted_address);
                      infoWindow.open(map, marker);
                    }
                 } else {
                    alert("Unable to find location" + location + ": " + status);
                 }
            }
        );
    }
}

function loadMapCookie (default_bounds)
{
    if (cookiename) {
        // === Look for the cookie ===
        if (document.cookie.length>0) {
            cookieStart = document.cookie.indexOf(cookiename + "=");
            if (cookieStart!=-1) {
                cookieStart += cookiename.length+1; 
                cookieEnd=document.cookie.indexOf(";",cookieStart);
                if (cookieEnd==-1) {
                cookieEnd=document.cookie.length;
                }
                cookietext = document.cookie.substring(cookieStart,cookieEnd);
                // == split the cookie text and create the variables ==
                bits = cookietext.split("|");
                mapOptions.center = new google.maps.LatLng(parseFloat(bits[0]), parseFloat(bits[1]))
                mapOptions.zoom = parseInt(bits[2]);
                mapOptions.maptype = parseInt(bits[3]);

//                mapOptions.bounds = null;
            } 
        }
    }
}

// === Set the cookie before exiting ===
function setMapCookie() {
    if (cookiename) {
        maptype = map.getMapTypeId();
        
        var cookietext = cookiename+"="+map.getCenter().lat()+"|"+map.getCenter().lng()+"|"+map.getZoom()+"|"+maptype;
        if (cookie_expire_days) {
            var exdate=new Date();
            exdate.setDate(exdate.getDate()+cookie_expire_days);
            cookietext += ";expires="+exdate.toGMTString();
        }
        // == write the cookie ==
        document.cookie=cookietext;
    }
}

function showInfoWindow (marker)
{
    infoWindow.close();
    var content = '';
    content = content + '<div class="map_popup">';
    content = content + '<a href="' + marker.feedItem.link + '" target="_top"><strong>';
    content = content + marker.feedItem.incident_datetime.substr(0,10) + ' ' + marker.feedItem.title;
    content = content + '</strong></a><br/>' + marker.feedItem.description;
    content = content + '</div>';
    
    infoWindow.setContent (content);
    infoWindow.open(map,marker);
    if (_gaq)
    {
        _gaq.push(['_trackEvent', 'Map', 'Popup', marker.feedItem.id]);
        _gaq.push(['_trackEvent', 'AlertView', 'Popup', marker.feedItem.id, 5]);
    }
}
function itemClick(id) {
    // open info window for the clicked item 
    marker = markers[id];
    if (marker)
        showInfoWindow (marker);
}

function updateSidebar (displayed_markers) {
    // Sort in descending order by incident_datetime - newest ones first
    displayed_markers = displayed_markers.sort(function(a,b) { return a.feedItem.incident_datetime < b.feedItem.incident_datetime ? 1 : a.feedItem.incident_datetime > b.feedItem.incident_datetime ? -1 : 0; });    
    
    var html = "";
    var n = displayed_markers.length;
    if (n == 0) { html += "There are no recent incidents in the current map view."; }
    else {
        html = '<table class="sidebar_content">';
        var last_dt = "";
        for (i = 0; i < n; i++) {
            var marker = displayed_markers[i];
            dt = marker.feedItem.incident_datetime.substr(0,10);
            if (dt != last_dt){
                html = html + '<tr><td colspan="2">' + dt + '</td></tr>'
                last_dt = dt;
            }
            html = html + '<tr><td><img width="16" height="16" src="' + marker.icon.url  + '"></td><td><a href="javascript:itemClick(\'' + marker.feedItem.id + '\')">' + marker.feedItem.title + '</a></td></tr>';
            
        }
        html += "</table>";
    }
    var side_bar = document.getElementById("side_bar");
    if (side_bar)
        side_bar.innerHTML = html
}

function onMapChangeBounds (new_bounds)
{
    // Update the feed
    var params = jQuery.extend({}, mapOptions.feedParams);
    if (!params.l)
        params.l = new_bounds.toUrlValue(4);
    $.getJSON('./json?', params, onFeedUpdate);
}

function initMap (_cookiename) { 
    cookiename = _cookiename; 

    loadMapCookie ();

    // TODO: Replace map_size with something else - or maybe we don't need it?
    
    
//    if (map_size)
//        map = new google.maps.Map(document.getElementById("map"), {size:map_size});
//    else
    markerImages = {};
    $.each(mapPinStyles, function(source_id, style) {
        var anchor = null;
        if (style.anchor)
            anchor = new google.maps.Point(style.anchor.x, style.anchor.y);
        markerImages[source_id] = new google.maps.MarkerImage (style.icon, null, null, anchor);
    });
    map = new google.maps.Map(document.getElementById("map"), mapOptions);
    
    if (mapOptions.bounds)
        map.fitBounds (mapOptions.bounds);

    currentMapBounds = map.getBounds ();
    
    google.maps.event.addListener(map, 'idle', function() {
        var new_bounds = map.getBounds ();
        if (!new_bounds.equals(currentMapBounds))
        {
            onMapChangeBounds (new_bounds);
            currentMapBounds = new_bounds;
        }
    });    
    google.maps.event.addListener(map, 'click', function() {
        infoWindow.close ();
    });    
    
    if (mapOptions.feedParams.region)
    {
        var kmlLayer = new google.maps.KmlLayer('http://alerts.skytruth.org/region/' + mapOptions.feedParams.region + '.kml');
        kmlLayer.setMap(map);    
    }
}

function flushCachedMarkers (cached_markers)
{
    if (cached_markers.length <= maxCachedMarkers) 
        return;
        
    // sort in ascending order by display_timestamp - oldest first
    cached_markers.sort(function(a,b) { return a.display_timestamp - b.display_timestamp; });    
    
    n = cached_markers.length - maxCachedMarkers;

    for (i = 0; i < n; i++) {
        id = cached_markers[i].feedItem.id;
        marker = markers[id];
        marker.setMap();
        delete markers[id];
    }
    
}

function onFeedUpdate (data){
    var feed = data['feed'];
    
    var items = [];
    
    var timestamp = +new Date();
    
    $.each(feed, function(index, item) {
        var marker = markers[item.id]
        if (!marker)
        {
            marker = new google.maps.Marker({
                position: new google.maps.LatLng(item.lat,item.lng), 
                map: map,
                title: item.incident_datetime.substr(0,10) + ' ' + item.title,
                icon: markerImages[item.source]
            }); 
            marker.feedItem = item;
            google.maps.event.addListener(marker, 'click', function() {
                showInfoWindow(marker);
            });
            markers[item['id']] = marker;
        }
        marker.display_timestamp = timestamp;
    });

    displayed_markers = [];
    cached_markers = [];
    $.each(markers, function (id, marker) {
        if (marker.display_timestamp == timestamp)
            displayed_markers.push(marker);
        else 
            cached_markers.push(marker);                        
    });
    updateSidebar (displayed_markers);
    flushCachedMarkers (cached_markers);
}

