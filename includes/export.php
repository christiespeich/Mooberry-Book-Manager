<?php
header('Content-disposition: attachment; filename=MBMexport.txt'); // . "\n";
header( 'Content-type: text/plain' ); //. "\n\n";
readfile('admin/export.txt');