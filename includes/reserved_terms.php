<?php
require_once './helper-functions.php';

$reserved_terms = mbdb_wp_reserved_terms();
?>
<html>
<head>
<title>Wordpress Reserved Terms</title>
<style>
ul{
    width:400px;
}
li{
    float:left;
    margin:0 10px 10px 0;
    width:175px;
}
li:nth-child(even){
    margin-right:0;
}
</style>
</head>
<body>
<h2>Wordpress Reserved Terms</h2>
<ul id="reserved_terms" >
<?php foreach ($reserved_terms as $term) { ?>
	 <li> <?php echo $term; ?></li>
<?php
}
?>
</ul>
</body>
</html>