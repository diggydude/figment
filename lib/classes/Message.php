<?php

  require_once(__DIR__ . '/MessageElement.php');

  class Message
  {

    protected

      $messageId,
      $postedBy,
      $postedAt,
      $rawText,
      $formatted,
      $inReplyTo,
      $replies,
      $reposts,
      $hashtags,
      $mentions,
      $likes,
      $dislikes,
      $views,
      $clicks,
      $elements;

    public function delete($messageId)
    {
    } // delete

    public function load($messageId)
    {
    } // load

    public function save()
    {
    } // save

    public function __construct($messageId = 0)
    {
    } // __conastruct

    ///////////////////////////////////////////////////////////////////////////
    // Process URIs in raw message text ///////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    public function parseUris()
    {
      $results = array();
      preg_match_all('/http:\/\/[\S]*/', $this->rawText, $matches, PREG_SET_ORDER);
      foreach ($matches as $match) {
        $results[] = $match[0];
      }
      return $results;
    } // parseUris

    public function uriIsQuote($uri)
    {
      return (stripos($uri, config('baseUri') . '/message/') === 0);
    } // uriIsQuote

    public function uriIsYoutubeVideo($uri)
    {
      return (stripos($uri, 'https://www.youtube.com/watch?v=') === 0);
    } // uriIsYoutubeVideo

    public function getContentType($uri)
    {
      if ($this->uriIsQuote()) {
        return MessageElement::TYPE_QUOTED_MESSAGE;
      }
      if ($this->uriIsYoutubeVideo() {
        return MessageElement::TYPE_YOUTUBE_VIDEO;
      }
      if (($curl = curl_init($uri)) === false) {
        throw new Exception(__METHOD__ . ' > ' . curl_error($curl));
      }
      $err = curl_setopt_array($curl,
               array(
                 CURLOPT_RETURNTRANSFER => true,
                 CURLOPT_CUSTOMREQUEST  => 'HEAD',
                 CURLOPT_HEADER         => 1,
                 CURLOPT_NOBODY         => true
               )
             );
      if ($err === false) {
        throw new Exception(__METHOD__ . ' > ' . curl_error($curl));
      }
      if (($content = curl_exec($curl)) === false) {
        throw new Exception(__METHOD__ . ' > ' . curl_error($curl));
      }
      foreach (file($content) as $line) {
        if (stripos($line, 'content-type') === 0) {
          if (stripos($line, 'html') !== false) {
            return MessageElement::TYPE_WEB_PAGE;
          }
          if (stripos($line, 'image') !== false) {
            return MessageElement::TYPE_REMOTE_IMAGE;
          }
        }
      }
      return null;
    } // getContentType

    public function replaceUris($uris)
    {
      $uris = array_unique(array_filter($uris));
      if (empty($uris)) {
        return;
      }
      $hashes  = array();
      $replace = array();
      $baseUri = config('baseUri');
      foreach ($uris as $uri) {
        $hash      = md5($this->postedBy . microtime() . $uri);
        $hashes[]  = (object) array('uri'  => $uri, 'hash' => $hash);
        $replace[] = "<a href=\"" . $baseUri . "/redirect/" . $hash . "\" target=\"_blank\">" . $uri . "</a>";
      }
      $this->rawText = str_replace($uris, $replace, $this->rawText);
      return $hashes;
    } // replaceUris

    public function storeShortUris($hashes)
    {
      $pdo    = Db::connect('figment');
      $values = array();
      $msgId  = intval($this->messageId);
      foreach ($hashes as $item) {
        $values[] = "(" . $pdo->quote($item->uri, PDO::PARAM_STR) . "," . $pdo->quote($item->hash, PDO::PARAM_STR) . "," . $msgId . ")";
      }
      $sql = "INSERT IGNORE INTO `figment_redirect` (`uri`, `short_uri`, `in_message`) VALUES " . implode(",", $values);
      $pdo->query($sql);
    } // storeShortUris

    ///////////////////////////////////////////////////////////////////////////
    // Process hashtags in raw message text ///////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    public function parseHashtags()
    {
      $results = array();
      preg_match_all('/\s#([0-9A-Za-z]*)/', $this->rawText, $matches, PREG_SET_ORDER);
      foreach ($matches as $match) {
        $results[] = $match[1];
      }
      return $results;
    } // parseHashtags

    public function lookupHashtags($hashtags = array())
    {
      $tags = array_unique(array_filter($hashtags));
      if (empty($tags)) {
        return array();
      }
      $pdo     = Db::connect('figment');
      $quoted  = array();
      $results = array();
      foreach ($tags as $tag) {
        $quoted[] = $pdo->quote($tag, PDO::PARAM_STR);
      }
      $sql  = "INSERT IGNORE INTO `figment_hashtag` (`content`) VALUES (" . implode("),(" . $quoted) . ")";
      $stmt = $pdo->query($sql);
      $sql  = "SELECT `hashtag_id`, `content` FROM `figment_hashtag` WHERE `content` IN (" . implode(",", $quoted) . ")";
      $stmt = $pdo->query($sql);
      while ($row = $stmt->fetch()) {
        $results[$row->hashtag_id] = $row->content;
      }
      return $results;
    } // getHashTagId

    public function replaceHashtags($hashtags = array())
    {
      $serach  = array();
      $replace = array();
      foreach ($hashtags as $tag) {
        $search[]  = "#" . $tag;
        $replace[] = "<a href=\"" . config('baseUri') . "/hashtag/" . $tag . "\">#" . $tag . "</a>";
      }
      $this->rawText = str_replace($search, $replace, $this->rawText);
    } // replaceHashtags

    public function logHashtagUsage($hashtagIds = array())
    {
      $ids = array_unique(array_filter($hashtagIds));
      if (empty($ids)) {
        return;
      }
      $pdo    = Db::connect('figment');
      $msgId  = intval($this->messageId);
      $values = array();
      foreach ($ids as $id) {
        $values[] = "(" . $msgId . "," . intval($id) . ")";
      }
      $sql  = "INSERT INTO `figment_tagged` (`message`, `hashtag`) VALUES " . implode(",", $values);
      $pdo->query($sql);
    } // logHashtagUsage

    ///////////////////////////////////////////////////////////////////////////
    // Process mentions in raw message text ///////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    public function parseMentions()
    {
      $results = array();
      preg_match_all('/\s@([0-9A-Za-z]*)/', $this->rawText, $matches, PREG_SET_ORDER);
      foreach ($matches as $match) {
        $results[] = $match[1];
      }
      return $results;
    } // parseMentions

    public function getUserIds($usernames = array())
    {
      $usernames = array_unique(array_filter($usernames));
      if (empty($usernames)) {
        return array();
      }
      $pdo    = Db::connect('figment');
      $quoted = array();
      foreach ($usernames as $name) {
        $quoted[] = $pdo->quote($name, PDO::PARAM_STR);
      }
      $sql     = "SELECT `user_id`, `username` FROM `figment_user` WHERE `username` IN (" . implode(",", $quoted)  . ")";
      $stmt    = $pdo->query($sql);
      $results = array();
      while ($row = $stmt->fetch()) {
        $results[$row->user_id] = $row->username;
      }
      return $results;
    } // getUserIds

    public function replaceMentions($usernames = array())
    {
      $search  = array();
      $replace = array();
      foreach ($usernames as $name) {
        $search[]  = "@" . $name;
        $replace[] = "<a href=\"" . config('baseUri') . "/profile/" . $name . "\">@" . $name . "</a>";
      }
      $this->rawText = str_replace($search, $replace, $this->rawText);
    } // replaceMentions

    public function logMentions($userIds = array())
    {
      $userIds = array_unique(array_filter($userIds));
      if (empty($usernames)) {
        return;
      }
      $pdo    = Db::connect('figment');
      $msgId  = intval($this->messageId);
      $values = array();
      foreach ($userIds as $id) {
        $values[] = "(" . $msgId . "," . intval($id) . ")";
      }
      $sql = "INSERT INTO `figment_mention` (`mentioned_in`, `mentioned`) VALUES " . implode(", ", $values);
      $pdo->query($sql);
    } // logMentions

    ///////////////////////////////////////////////////////////////////////////
    // Process emoticons in raw message text //////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    public function parseEmoticons()
    {
      $results = array();
      preg_match_all('/:([0-9A-Za-z]*):/', $this->rawText, $matches, PREG_SET_ORDER);
      foreach ($matches as $match) {
        $results[] = $match[1];
      }
      return $results;
    } // parseEmoticons

    public function lookupEmoticons($emoticons = array())
    {
      $emoticons = array_unique(array_filter($emoticons));
      if (empty($emoticons)) {
        return array();
      }
      $pdo    = Db::connect('figment');
      $quoted = array();
      foreach ($emoticons as $code) {
        $quoted[] = $pdo->quote($code, PDO::PARAM_STR);
      }
      $sql     = "SELECT `code`, `filename` FROM `figment_emoticon` WHERE `code` IN (" . implode(", ", $quoted) . ")";
      $stmt    = $pdo->query($sql);
      $results = array();
      while ($row = $stmt->fetch()) {
        $results[$row->code] = $row->filename;
      }
      return $results;
    } // lookupEmoticons

    public function replaceEmoticons($emoticons = array())
    {
      $search  = array();
      $replace = array();
      foreach ($emoticons as $code => $filename) {
        $search[]  = ":" . $code . ":";
        $replace[] = "<img src=\"" . config('emoticonDir') . "/" . $filename . "\" alt=\"" . $code . "\" />";
      }
      $this->rawText = str_replace($search, $replace, $this->rawText);
    } // replaceEmoticons

    ///////////////////////////////////////////////////////////////////////////
    // Process elements from remote websites //////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    public function transloadImage($uri)
    {
      $tmpFilename = config('tmpDir') . basename($uri);
      if (($curl = curl_init($uri)) === false) {
        throw new Exception(__METHOD__ . ' > ' . curl_error($curl));
      }
      $err = curl_setopt_array($curl,
               array(
                 // CURLOPT_RETURNTRANSFER => true,
                 CURLOPT_FILE           => $tmpFilename
               )
             );
      if ($err === false) {
        throw new Exception(__METHOD__ . ' > ' . curl_error($curl));
      }
      // if (($content = curl_exec($curl)) === false) {
      if (curl_exec($curl) === false) {
        throw new Exception(__METHOD__ . ' > ' . curl_error($curl));
      }
      $hash = md5_file($tmpFilename);
      rename($tmpFilename, config('uploadDir') . $hash);
      return $hash;
    } // transloadImage

    public function fetchWebPage($uri)
    {
      if (($curl = curl_init($uri)) === false) {
        throw new Exception(__METHOD__ . ' > ' . curl_error($curl));
      }
      $err = curl_setopt_array($curl,
               array(
                 CURLOPT_RETURNTRANSFER => true
               )
             );
      if ($err === false) {
        throw new Exception(__METHOD__ . ' > ' . curl_error($curl));
      }
      if (($content = curl_exec($curl)) === false) {
        throw new Exception(__METHOD__ . ' > ' . curl_error($curl));
      }
      return $content;
    } // fetchWebPage

    public function getWebPageProperties($html)
    {
      $dom = new DOMDocument();
      if ($dom->loadHTML($html) === false) {
        throw new Exception(__METHOD__ . ' > Failed to load HTML.');
      }
      $xpath = new DOMXPath)$dom);
      $title = trim($xpath->query('//head/title[1]/text()'));
      if (strlen($title) == 0) {
        $title = trim($xpath->query('//body/h1[1]/text()'));
      }
      if (strlen($title) == 0) {
        $title = "Untitled Web Page";
      }
      $summary = trim($xpath->query('//head/meta[@name="description"][1]/@value'));
      if (strlen($summary) == 0) {
        $summary = trim($xpath->query('//body/p[1]/text()'));
      }
      if (strlen($summary) == 0) {
        $summary = "No summary available.";
      }
      if (strlen($summary) > 256) {
        $summary = substr($summary, 0, 256) . "...";
      }
      $leadImgSrc  = $xpath->query('//body/img[1]/@src');
      $leadImgHash = $this->transloadImage($leadImgSrc);
      return (object) array(
               'title'     => $title,
               'summary'   => $summary,
               'leadImage' => $leadImgHash
             );
    } // getWebPageProperties

    ///////////////////////////////////////////////////////////////////////////
    // Process elements in POST request ///////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    public function getQuotedMessage($messageId)
    {
      $pdo  = Db::connect('figment');
      $sql  = "SELECT `formatted` FROM `figment_message` WHERE `message_id` = " . intval($messageId);
      $stmt = $pdo->query($sql);
      $row  = $stmt->fetch();
      return $row->formatted;
    } // getQuotedMessage

    public function logRepost($messageId)
    {
      $pdo = Db::connect('figment');
      $sql = "INSERT INTO `figment_repost` (`original`, `reposted_in`) VALUES (" . intval($messageId) . ", " . intval($this->messageId) . ")";
      $pdo->query($sql);
    } // logRepost

    public function formatCodeSnippet($code, $language)
    {
      $geshi = new GeSHi($code, $language);
      $geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
      return $geshi->parse_code();
    } // formatCodeSnippet

    ///////////////////////////////////////////////////////////////////////////
    // Process POST data //////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    public function parseRawText()
    {
      return (object) array(
               'uris'      => $this->parseUris(),
               'hashtags'  => $this->parseHashtags(),
               'mentions'  => $this->parseMentions(),
               'emoticons' => $this->parseEmoticons()
             );
    } // parseRawText

    public function addElement($element)
    {
      if (!($element instanceof MessageElement)) {
        throw new Exception(__METHOD__ . ' > Argument is not a MessageElement instance.');
      }
      $this->elements[] = $element;
    } // addElement

    public function sortElementsByWeight()
    {
      $_typeSort = function($a, $b)
                   {
                     if ($a->type == $b->type) {
                       return 0;
                     }
                     return ($a->type < $b->type) ? -1 : 1;
                   };
      usort($this->elements, $_typeSort);
    } // sortElementsByWeight

    ///////////////////////////////////////////////////////////////////////////
    // Compose HTML for message display ///////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    public function renderImage($filename, $width, $height)
    {
    } // renderImage

    public function composeImagesLayout($elements = array())
    {
    } // composeImagesLayout

    public function composeWebPageLayout($element)
    {
    } // composeWebPageLayout

    public function composeVideoLayout($element)
    {
    } // composeVideoLayout

    public function composeLayout()
    {
    } // composeLayout

  } // Message

?>
