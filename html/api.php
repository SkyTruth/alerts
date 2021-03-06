<?php
// api.php
$data['title'] = "SkyTruth Alerts - Application programming Interface";
$data['use_maps'] = true;
$data['meta']['og:title'] = 'SkyTruth Alerts';


$data['meta']['og:type'] = 'non_profit';
$data['meta']['og:url'] = 'http://alerts.skytruth.org';
$data['meta']['og:image'] = 'http://skytruth.org/images/logo-standard.jpg';
$data['meta']['og:site_name'] = 'SkyTruth Alerts';
$data['meta']['og:email'] = 'alerts@skytruth.org';
$data['meta']['og:description'] = 'SkyTruth Alerts delivers real-time updates about environmental incidents in your back yard - or whatever part of the world you know and love. As soon as we know - you know';
$data['meta']['fb:admins'] = '1446720793';

require ("api/templates/header.template");
?>
			<p>The SkyTruth Alerts API publishes GeoRSS and KML feeds of alert reports.  This page describes the API parameters available for filtering the feeds.</p>
			<h2>Feed URLs</h2>
      <div style="clear: both;"></div>
			
			<div class="inset_box">
			<table>
			<tr><th>RSS</th><td>http://alerts.skytruth.org/rss</td></tr>
			
			<tr><th>KML</th><td>http://alerts.skytruth.org/kml</td></tr>
			</table>
			</div>
			<p>Both feeds will return the most recent 100 alerts that match the filter, with the most recent alerts first.  The RSS feed is Atom 2.0 with georss:point elements. </p>
			<h2>Feed Formats</h2>
      <div style="clear: both;"></div>
			<div class="inset_box">
			<dl>
			
			<dt><strong>RSS Feed Format</strong></dt>
			<dd>ths RSS feed is a standard Atom 2.0 feed with a georss:point element containing a lat/long pair</dd>
			
			<dt><strong>KML Feed Format</strong></dt>
			<dd>the KML feed is a KML 2.2 feed with a set of placemarks including HTML description content.</dd>
			</dl>
			</div>
			<h2>Query Parameters</h2>
      <div style="clear: both;"></div>
			<div class="inset_box">
			<dl>
				<dt><b>l</b> (optional)</dt><dd>A lat/long bounding box. This is a comma separated list of lats and longs that define two opposite corners of a bounding box that will filter reports to only those within the box</dd>
				<dt><b>BBOX</b> (optional)</dt><dd>A long/lat bounding box, included for Google Earth compatibility. Works the same as <b>l</b> except that the lat/long pairs are reversed so that longitude comes first</dd>
			
				<dt><b>dates</b> (optional)</dt><dd>a date or a date range.  Supply one date to filter reports to only those that occured after that date.  Supply two dates separated by a comma to include only reports that occrred in that time range.  Date format is YYYY-MM-DD</dd>
				<dt><b>d</b> (optional)</dt><dd>an integer number of days.  Filters reports to include only reports that are less than <b>d</b> days old</dd>
				<dt><b>tag</b> (optional)</dt><dd>a comma delimited list of <a href="tags.htm">tags</a> to filter the feed by</dd>
				<dt><b>n</b> (optional)</dt><dd>maximum number of incidents to return in the result. Default is 50. Maximum allowed is 100.</dd>
			
			</dl>
			</div>
			<h2>Location Examples</h2>
      <div style="clear: both;"></div>
			
			<p>Retrieve an RSS feed containing reports in the vicinity of Shepherdstown, WV</p>
			
			<div class="inset_box">
			http://alerts.skytruth.org/rss?l=39.313401,-78.031616,39.526638,-77.583923
			</div>
			<p>The first pair is the Lat/Long of one corner of the bounding box and the second pair is the Lat/Long of the opposite corner.  It does not patter which pair comes first, but in each pair the LAT is first and the LONG is second.</p>
			<p>Alternatively, you can use a Google Earth style BBOX parameter, which works the same way, except that, confusingly, the LAT and LONG are reversed in each pair, like this:</p>
			<div class="inset_box">
			http://alerts.skytruth.org/rss?BBOX=-78.031616,39.313401,-77.583923,39.526638
			
			</div>
			<h2>Date Examples</h2>
      <div style="clear: both;"></div>
			<p>The feed can be filtered by date to show only reports after a particular date, or only 
			reports within a given date range.  Dates are formatted YYYY-MM-DD.  For example:</p>
			<div class="inset_box">
			http://alerts.skytruth.org/kml?dates=2011-06-01
			</div>
			<p>will give you a KML feed with reports on or after the first of June, 2011.</p>
			<div class="inset_box">
			http://alerts.skytruth.org/kml?dates=2011-06-01,2011-06-03
			</div>
			<p>will give you only reports from the first through the third of June, 2011</p>
			<p>You can also use the "d" parameter to specify that you only want reports new reports that are only a few days old like this</p>
			
			<div class="inset_box">
			http://alerts.skytruth.org/kml?d=5
			</div>
			<p>will give you only reports that are less than 5 days old</p>
			<h2>Tag Examples</h2>
      <div style="clear: both;"></div>
			<p>Alert reports can have tags which can be used to filter a feed as well.  A tag is applied to every report to based on the original source (e.g. NRC, SkyTruth), based on SkyTruth analysis of the reports (BigSpill) and tags already present in reports from other blogs are preserved in the alert feed.  You can specify multiple tags in a comma-separated list, in which case they are ORed together to return reports that match ay one of the tags in the list</p>
			<div class="inset_box">
			http://alerts.skytruth.org/rss?tag=SkyTruth
			http://alerts.skytruth.org/rss?tag=Nrc,BigSpill
			</div>
			<p><a href="/tags.htm">Read more about tags</a></p>

		  <p>There are many tags used in the alerts feed that can be used to filter the feed to only include certain types of reports</p>
		  <h2>Source Tags</h2>
      <div style="clear: both;"></div>
		  <p>Each data source has a tag associated with it that is applied to every incident report that originates from that source.  For instance, reports originating from the <a href="sources.htm#NRC">NRC</a> have the tag 'NRC'.  For a complete list of data sources and their corresponding tags, see the <a href="sources.htm">SkyTruth Alerts Sources</a> page.</p>
		  <h2>SkyTruth Analysis Tags</h2>
      <div style="clear: both;"></div>
		  <p>The SkyTruth automated expert system analyzes each report and assigns certain tags based on the content of the report.
		    <div class="inset_box">
			<table>
			<tr><th align="left" valign="top">BigSpill</th><td>The report indicates an oil spill that is larger than 100 gallons</td></tr>
			<tr><th align="left" valign="top">GeocodeMismatch</th><td>A lat/long was supplied in the report, but it does not match other location information such as a protraction area block id or zipcode</td></tr>
			<tr><th align="left" valign="top">SheenSizeMismatch</th><td>For an oil spill that has a reported spill volume and also has a reported sheen size, this tag indicated that the volume computed from the sheen area is more than twice the size of the reported volume.  The volume derived from the sheen area is computed assuming a uniform distribution over the entire sheen area with a minimum thickness of 1 micron, which is the minimum thickness necessary in otder to produce a visible sheen on open water, according to <a href="http://blog.skytruth.org/2010/04/gulf-oil-spill-rate-must-be-much-higher.html">this analysis</a>.</td></tr>
			</table>
            </div>
		  </p>
		  <h2>Imported Tags</h2>
      <div style="clear: both;"></div>
		  <p>When reports are imported, any tags associated with the report are preserved (for instance tags on imported blog posts)</p>
		  
		  <p>Entries in the alerts feed are consolidated from a number of sources.  Each alert feed entry is tagged based on the original source of the report.</p>
		  <h2>SkyTruth Source Tags</h2>
      <div style="clear: both;"></div>
		  <p>
		    <div class="inset_box">
			<table>
			<tr><th align="left" valign="top">NRC</th><td>reports from the <a href="http://www.nrc.uscg.mil/foia.html">National Response Center</a>.  These are mostly industry self-reports from operators that are required to report accidents and emissions to the NRC</td></tr>
			<tr><th align="left" valign="top">SkyTruth</th><td>Reports from the <a href="http://blog.skytruth.org/search/label/Alerts">SkyTruth blog</a> that have been tagged as <b>Alerts</b></td></tr>
			<tr><th align="left" valign="top">NOAA</th><td>Incidents reported on NOAA's  <a href="http://www.incidentnews.gov/">Incident News</a> site documenting selected oil spills (and other incidents) where NOAA's Office of Response and Restoration (OR&R) provided scientific support for the incident response</td></tr>
			</table>
            </div>
		  </p>
		  <h2>NRC Incident Reports</h2>
      <div style="clear: both;"></div>
          <p>Reports from the NRC are scraped daily from the NRC website.  The full report text is parsed to extract key fields.  Certain reports are eliminated from the feed because they are not "real" reports.  These include reports that are drills, or tests of the system, as well as routine activity reports that do not document a release of materials. </p>
          <p>After extraction, the reports are geo-coded using the best availale geo-location information in the report.  Reports where the location cannot be determined are excluded from the feed.</p>
          <p>Finally the reports are analyzed by the SkyTruth expert system.  Additional fields are added to the report if there is any additional anallysis that can be included.</p>

          
		    <div class="inset_box">
		    <h3>NRC Report Geo-Location Methods in Order of Preference</h3>
      <div style="clear: both;"></div>
			<table>
			<tr><th align="left" valign="top">Explicit Lat/Long</th><td>If a report is found to contain an explicit lattitude and longitude pair, this is always used as the location</td></tr>
			<tr><th align="left" valign="top">Protraction Area and Lease Block</th><td>Offshoe reports commonly have a protraction area (e.g. Mississippi Canyon) and a lease blosk ID number.  If this is present, then the center point of the lease block is used as the location.  Protraction areas and lease blocks are the way the Gulf of Mexico is divided up by <a href="http://www.gomr.boemre.gov/homepg/pubinfo/MapsandSpatialData.html">BOEMRE</a> for management of oil and gas drilling leases in federal waters.</td></tr>
			<tr><th align="left" valign="top">Zip Code</th><td>Many onshore incidents contain a city, state and zip code.  The center point of the zip code is used as the incident location</td></tr>
			</table>
            </div>		  
<?php 
require ("api/templates/footer.template");
?>