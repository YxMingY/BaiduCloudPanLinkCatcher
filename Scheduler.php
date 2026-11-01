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
    // User-Agent轮换数组
    $userAgents = [
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
      'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
      'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
      'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/605.1.15',
      'Mozilla/5.0 (iPad; CPU OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/605.1.15'
    ];
    
    // 随机选择一个User-Agent
    $userAgent = $userAgents[array_rand($userAgents)];
    
    // Cookie管理
    static $cookie = ''; // 使用静态变量保存cookie，以便在多次请求间保持会话
    
    $context = stream_context_create([
      'http' => [
        'method' => 'GET',
        'header' => [
          "User-Agent: $userAgent",
          "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
          "Accept-Language: zh-CN,zh;q=0.9,en;q=0.8",
          "Accept-Encoding: gzip, deflate",
          "Connection: close",
          "Upgrade-Insecure-Requests: 1"
        ],
        'timeout' => 15,
        'follow_location' => true,
        'max_redirects' => 3
      ]
    ]);
    
    // 如果有cookie，添加到请求头
    if (!empty($cookie)) {
      $existing_headers = stream_context_get_options($context)['http']['header'];
      $existing_headers[] = "Cookie: $cookie";
      stream_context_set_option($context, 'http', 'header', $existing_headers);
    }
    
    $get = null;
    try{
      $result = file_get_contents($url, false, $context);
      
      // 尝试从响应头中提取Set-Cookie
      if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
          if (stripos($header, 'Set-Cookie:') === 0) {
            $setCookie = trim(substr($header, 11));
            $cookie = $this->parseCookie($setCookie, $cookie);
          }
        }
      }
      
      $get = $result;
    }catch(Exception $e) {
      
    }
    return $get;
  }
  
  /**
   * 解析并合并Cookie
   */
  private function parseCookie(string $setCookie, string $existingCookie): string 
  {
    // 简单解析Set-Cookie头部，提取实际的cookie值
    $cookieParts = explode(';', $setCookie);
    $newCookie = trim($cookieParts[0]);
    
    // 合并到现有cookie中
    $cookiePairs = [];
    if (!empty($existingCookie)) {
      $existingPairs = explode('; ', $existingCookie);
      foreach ($existingPairs as $pair) {
        if (strpos($pair, '=') !== false) {
          list($name, $value) = explode('=', $pair, 2);
          $cookiePairs[trim($name)] = trim($value);
        }
      }
    }
    
    if (strpos($newCookie, '=') !== false) {
      list($name, $value) = explode('=', $newCookie, 2);
      $cookiePairs[trim($name)] = trim($value);
    }
    
    $cookieString = '';
    foreach ($cookiePairs as $name => $value) {
      if (!empty($cookieString)) {
        $cookieString .= '; ';
      }
      $cookieString .= "$name=$value";
    }
    
    return $cookieString;
  }
}