<?php
namespace yxmingy\crawler;

class URLManager
{
  private $crawled_url = [];
  private $uncrawled_url = [];
  public function getURLToCrawl():?string
  {
    $count = count($this->uncrawled_url)-1;
    if($count < 0) return null;
    $this->crawled_url[] = $url = $this->uncrawled_url[$count];
    unset($this->uncrawled_url[$count]);
    return $url;
    
  }
  public function addURL(string $url)
  {
    if(!in_array($url,$this->crawled_url) && !in_array($url,$this->uncrawled_url)) {
      $this->uncrawled_url[] = $url;
    }
  }
}