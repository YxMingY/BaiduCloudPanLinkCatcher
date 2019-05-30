<?php
namespace yxmingy\crawler;

class Scheduler
{
  private $active = true;
  protected $keyword = "";
  private $parser;
  private $url_manager;
  public function __construct(string $keyword)
  {
    $this->keyword = $keyword;
  }
  public function init()
  {
    $baseurl = "https://m.baidu.com/from=844b/pu=sz%401321_1001/s?word=".\urlencode($this->keyword." 百度云");
    $this->url_manager = new URLManager();
    $this->parser = new Parser($this->url_manager);
    $this->parser->parse($this->download($baseurl),"https://m.baidu.com/from=844b/pu=sz%401321_1001/");
  }
  public function start()
  {
    while($this->active) {
      $url = $this->url_manager->getURLToCrawl();
      if($url === null) {
        $this->parser->print();
        die(base64_decode("MjMz"));
      }
      if(preg_match('/\w$/',$url)) {
        $burl = substr($url,0,strrpos($url,"/")+1);
      } else {
        $burl = $url;
      }
      $this->parser->parse($this->download($url),$burl);
    }
  }
  private function download(string $url):string
  {
    $get = null;
    try{
      $get = file_get_contents($url);
    }catch(Exception $e) {
      
    }
    return $get;
  }
}