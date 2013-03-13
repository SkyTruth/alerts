<?php
// about.php
$data['title'] = "SkyTruth Alerts - About";
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
<p>The SkyTruth alert system is a free service open to the public that provides daily updates of environmentally significant incidents by     geographical area.  You can browse the most recent incident reports on a map or in Google Earth, and you can also subscribe to a personalized     feed of incident reports via RSS or email</p>
<p>The alert feed currently contains reports generated from ongoing SkyTruth investigations, combined with selected reports from the the National Response Center that have been processed by SkyTruth's automated expert system to clean up problem data and add additional SkyTruth commentary and analysis.</p>

<?php 
require ("api/templates/footer.template");
?>