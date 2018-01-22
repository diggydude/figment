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

    public static function post($params)
    {
    } // post

    public static function delete($messageId)
    {
    } // delete

    public static function load($messageId)
    {
    } // load

    public function __construct($messageId = 0)
    {
    } // __conastruct

    public function transloadImage($uri)
    {
    } // transloadImage

    public function transloadWebPage($uri)
    {
    } // transloadWebPage

    public function quoteMessage($messageId)
    {
    } // quoteMessage

    public function formatCode($code, $language)
    {
    } // formatCode

    public function parseUris()
    {
      $results = array();
      preg_match_all('/http:\/\/(\S*)/', $this->rawText, $matches, PREG_SET_ORDER);
      foreach ($matches as $match) {
        $results[] = $match[0];
      }
      return $results;
    } // parseUris

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
      str_replace($uris, $replace, $this->rawText);
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
    } // getUserIds

    public function logMentions($userIds = array())
    {
    } // logMentions

    public function parseEmoticons()
    {
      $results = array();
      preg_match_all('/(:[0-9A-Za-z]*:)/', $this->rawText, $matches, PREG_SET_ORDER);
      foreach ($matches as $match) {
        $results[] = $match[1];
      }
      return $results;
    } // parseEmoticons

    public function lookupEmoticons($emoticons = array())
    {
    } // lookupEmoticons

    public function checkLink($uri)
    {
      return true;
    } // checkLink

    public function parseRawText()
    {
      return (object) array(
               'uris'      => $this->parseUris(),
               'hashtags'  => $this->parseHashtags(),
               'mentions'  => $this->parseMentions(),
               'emoticons' => $this->parseEmoticons()
             );
    } // parseRawText

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
        return MessageElement::TYPE_YOUTUBE_VIDEO);
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

    public function composeLayout()
    {
    } // composeLayout

  } // Message

?>
