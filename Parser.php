<?php
namespace yxmingy\crawler;

class Parser
{
  private $url_manager;
  private $panurl = [];
  public function __construct(URLManager $m)
  {
    $this->url_manager = $m;
  }
  public function parse(string $text,string $baseurl = "")
{
    //pan.baidu.com/s/1FqvY7RGuiXlAHSgMrP6cpQ&#160;提取码:ru90...<b
   //是url就扔给urlmanager，是pan链接就输出
    preg_match_all('/a href="(.+?)"/',$text,$result);
    preg_match_all('/pan\.baidu\.com\/s\/([\s\S]+?码[:：].{0,8}|[\w-]+?[^\w-])/',$text,$pans);
    $this->panurl = array_merge($this->panurl,$pans[0]);
    
    foreach($result[1] as $url) {
      if(preg_match('/javascript/',$url)) continue;
      $this->url_manager->addURL($url);
    }
  }
  public function print()
  {
    print_r($this->panurl);
  }
}
