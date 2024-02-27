<?php
header('Content-disposition: attachment; filename=MBMexport.csv'); // . "\n";
header( 'Content-type: text/plain' ); //. "\n\n";
readfile('admin/export.csv');

unlink('admin/export.csv');
