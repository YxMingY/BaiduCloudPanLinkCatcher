<?php

namespace yxmingy\crawler;

require_once "Scheduler.php";
require_once "URLManager.php";
require_once "Parser.php";

echo "Input your keyword: ";
$keyword = trim(fgets(STDIN));

if($keyword != "") {
  echo "Your keyword: ".$keyword;
  $scheduler = new Scheduler($keyword);
  
  echo PHP_EOL."initialization...".PHP_EOL;
  $scheduler->init();
  
  echo "start!".PHP_EOL;
  $scheduler->start();
}