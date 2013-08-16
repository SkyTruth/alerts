<?php
// index.php

require_once('includes/util.php');
require_once('includes/feed_common.php');

$parsed_params = parse_feed_params($_REQUEST);

$data['title'] = "SkyTruth Alerts: Daily reports of enviromental incidents in your back yard";
$data['use_maps'] = true;
$data['meta']['og:title'] = 'SkyTruth Alerts';


$data['meta']['og:type'] = 'non_profit';
$data['meta']['og:url'] = 'http://alerts.skytruth.org';
$data['meta']['og:image'] = 'http://skytruth.org/images/logo-standard.jpg';
$data['meta']['og:site_name'] = 'SkyTruth Alerts';
$data['meta']['og:email'] = 'alerts@skytruth.org';
$data['meta']['og:description'] = 'SkyTruth Alerts delivers real-time updates about environmental incidents in your back yard - or whatever part of the world you know and love. As soon as we know - you know';
$data['meta']['fb:admins'] = '1446720793';

$data['map_bounds'] = $parsed_params['bounds'];
$data['rss_url'] = make_url('/rss');


require ("api/templates/header.template");
?>
    <table class="alert_content" border="0" cellpadding="0" cellspacing="0"> 
      <tr>
		<td colspan="2" align="center">
	SkyTruth Alerts delivers <b>real-time updates</b> about environmental incidents in <b>your back yard</b> (or whatever part of the world you know and love).<br/> <span style="font-weight: bold; font-size: 15px">As soon as WE know - YOU know</span>
        </td>
      </tr>
      <tr>
        <td align="center" style="vertical-align: bottom;">
        <div class="inset_box3">
        Want to be notified when something new is posted? Zoom in on the map to show your region of interest and then<br/>
        <a href="javascript:subscribe('RSS')" style="font-weight: bold; font-size: 15px;">Subscribe</a><br/> and you'll know as soon as we do.</div>
        </td>
        <td style="vertical-align: bottom;">        
            <div style="vertical-align: bottom; text-align: right; float: right;">
            <table cellpadding=0 cellspacing=0 border=0><tr><td style="vertical-align: bottom;">
            <a href="javascript:embed()" title="Embed this map in your website"><img border="0" width="24" height="24" src="http://www.skytruth.org/images/embed-icon.png"></a>
            
<script type="text/javascript">
function show_hide(div_id) {
    var div = document.getElementById(div_id);
    if (div)
    {
        var display = div.style.display;
        if (display == "none")
            display = "block";
        else
            display = "none";
        div.style.display = display;
    }
}
function embed () {
    var b = map.getBounds();
    var l = b.getSouthWest().toUrlValue(4) + ',' +  b.getNorthEast().toUrlValue(4);
    var iframe_html_small = '<iframe width="480" height="360" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="<?php echo get_base_url() ?>/embed?nosidebar=1&brief=1&l=' + l + '"></iframe>';
    var iframe_html_large = '<iframe width="960" height="650" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="<?php echo get_base_url() ?>/embed?l=' + l + '"></iframe>';
    
    var embed_code_small = document.getElementById("embed_code_small");
    if (embed_code_small)
        embed_code_small.value = iframe_html_small;
    var embed_code_large = document.getElementById("embed_code_large");
    if (embed_code_large)
        embed_code_large.value = iframe_html_large;
    show_hide ("embed_popup");
}
function rss () {
    var rss_url = encodeURI("<?php echo $data['rss_url']?>" + "?l=" + getCurrentMapBounds () + "#rss");
    var rss_div_template = '<a href="http://add.my.yahoo.com/rss?url={RSS_URL}"><img src="http://us.i1.yimg.com/us.yimg.com/i/us/my/addtomyyahoo4.gif" width="91" height="17" alt="addtomyyahoo4"></a> \
<a class="img" href="http://www.newsgator.com/ngs/subscriber/subext.aspx?url={RSS_URL}"><img src="http://www.newsgator.com/images/ngsub1.gif" alt="Subscribe in NewsGator Online"></a> \
<a href="http://feeds.my.aol.com/add.jsp?url={RSS_URL}"><img src="http://o.aolcdn.com/myfeeds/html/vis/myaol_cta1.gif" alt="Add to My AOL" border="0"></a> \
<a class="img" href="http://www.bloglines.com/sub/{RSS_URL}"><img src="http://www.bloglines.com/images/sub_modern5.gif" alt="Subscribe with Bloglines"></a> \
<br/> \
<a href="http://www.netvibes.com/subscribe.php?url={RSS_URL}"><img src="http://www.netvibes.com/img/add2netvibes.gif" alt="Add to netvibes"></a> \
<a href="http://fusion.google.com/add?feedurl={RSS_URL}"><img src="http://buttons.googlesyndication.com/fusion/add.gif" width="104" height="17" alt="Add to Google"></a> \
<a href="http://www.pageflakes.com/subscribe.aspx?url={RSS_URL}"><img src="http://www.pageflakes.com/subscribe2.gif" border="0"></a> \
<a href="{RSS_URL}"><img src="http://feedburner.google.com/fb/lib/images/icons/feed-icon-12x12-orange.gif" alt="original feed"></a>\
<a href="{RSS_URL}">View Feed XML</a>'; 

    var rss_url_div =  document.getElementById("rss_url");
    if (rss_url_div)
        rss_url_div.innerHTML = rss_div_template.replace (/{RSS_URL}/g, rss_url);
   
    show_hide ("rss_popup");
}
</script>
            <div id="embed_popup" style="display: none; position: absolute; z-index: 7; background-color: white; border: 1px solid #CCC; padding:10px; text-align:left;">
            Copy the HTML and paste into your website<br/>
            <b>Small map with no sidebar:</b><br/>
            <input id="embed_code_small" type="text" size="50"/><br/>
            <b>Large map with sidebar:</b><br/>
            <input id="embed_code_large" type="text" size="50"/><br/>
            <a href="javascript:embed()">Close</a>
            </div>

            <div id="rss_popup" style="display: none; position: absolute; z-index: 7; background-color: white; border: 1px solid #CCC; padding:10px; text-align:left;">
            RSS news feed for the current map extent:<br/>
            <div id="rss_url"></div>
            <a href="javascript:rss()">Close</a>
            </div>
            
            
            <a href="javascript:rss()" title="Subscribe to updates via RSS"><img border="0" width="24" height="24" src="http://www.skytruth.org/images/feed-icon-28x28.png"></a>&nbsp;<a href="javascript:subscribe('EMAIL')" title="Subscribe to updates via Email"><img border="0" width="24" height="24" src="http://www.skytruth.org/images/mail-icon-28x28.png"></a> <a href="<?php echo make_url('/ge/recent.kml')?>" title="View in Google Earth"><img border="0" width="24" height="24" src="http://www.skytruth.org/images/GoogleEarth-kml3.gif"></a>&nbsp;
            </td><td style="vertical-align: bottom;">
            <form action="#" onsubmit="zoomToLocation(this.location.value); return false">
              <b>Go To</b>: <input type="text" size="30" name="location" value="" />&nbsp;<input type="submit" value="Go!" />
            </form>
            </td>
            </tr></table>
            </div>   
		</td>
	  </tr>
      <tr> 
        <td width="260" valign="top" class = "sidebar"> 
           <div id="side_bar" style="overflow: auto; width: 260px; height:650px">Loading...</div> 
        </td> 
        <td width = 700 class="map"> 
           <div id="map" style="width: 700px; height: 650px"></div> 
        </td> 
      </tr> 
 
    </table> 

<script type="text/javascript"> 
//<![CDATA[
    mapOptions.feedParams.channel = 'local';
    <?php echo 'mapOptions.feedParams.show = "'.$parsed_params['show'].'";'."\n"; ?>
<?php if (!empty ($data['map_bounds'])) { ?>
    mapOptions.bounds = new google.maps.LatLngBounds (new google.maps.LatLng(<?php echo $data['map_bounds'][0][0] ?>, <?php echo $data['map_bounds'][0][1] ?> ), 
        new google.maps.LatLng(<?php echo $data['map_bounds'][1][0] ?>, <?php echo $data['map_bounds'][1][1] ?>));
<?php } ?>
    initMap ('alerts_map');

//    var kml = new GGeoXml("<?php echo make_url('/ge/recent.kml', array('nologo'=>1, 'channel'=>'local'))?>");
//    map.addOverlay(kml);
//]]>
</script> 

<?php 
require ("api/templates/footer.template");
?>

