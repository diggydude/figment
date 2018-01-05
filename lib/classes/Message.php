<?php

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
      $inlineImages,
      $linkedWebPages,
      $youtubeVideos,
      $quotedMessages,
      $codeSnippet;

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

    public function addImage($filename)
    {
    } // filename

    public function addYoutubeVideo($uri)
    {
    } // addYoutubeVideo

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

    public function composeLayout()
    {
    } // composeLayout

  } // Message

?>
