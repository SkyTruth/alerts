<?php
// index.php
require_once ("includes/defaults.php");

$data['title'] = "Map Embed Test Page";

require ("api/templates/header.template");
?>
<h1>Embedded Map Small</h1>
<iframe width="480" height="360" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="/embed?nosidebar=1&brief=1&l=36.4743,-81.178,42.0003,-73.4875"></iframe>
<h1>Embedded Map Full</h1>
<iframe width="960" height="650" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="/embed?l=36.4743,-81.178,42.0003,-73.4875"></iframe>

<?php 
require ("api/templates/footer.template");
?>
