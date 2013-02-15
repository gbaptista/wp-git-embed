<?php

/*
Plugin Name: WP-Git-Embed
Plugin URI: http://wordpress.org/extend/plugins/wp-git-embed/
Description: Embed GitHub files.
Version: 0.2
Author: Guilherme Baptista
Author URI: http://gbaptista.com
License: MIT
*/

if(!class_exists('WP_Git_Embed')) {

  class WP_Git_Embed {

    private static $instance;

    public static function getInstance() {
      if(!isset(self::$instance)) self::$instance = new self;
      return self::$instance;
    }

    private function __construct() {
      add_filter('the_content', array(__CLASS__, 'beforeFilter'), -1);
      add_filter('the_excerpt', array(__CLASS__, 'beforeFilter'), -1);
      add_filter('comment_text', array(__CLASS__, 'beforeFilter'), -1);
    }

    private static function raw($code) {

      if(preg_match('/\{[0-9].*\}/', $code, $lines)) {
        list($s_line, $e_line) = explode(':', preg_replace('/\{|\}/', '', $lines[0]));
        if(empty($e_line)) $e_line = $s_line;
        $code = preg_replace('/\{[0-9].*\}/', '', $code);
      }

      // GitHub (Custom Ruby Immersion) - https://github.com/gbaptista/ruby-immersion
      if(preg_match('/^\[ruby_code:.*\]/', $code)) {
        $path = explode('[ruby_code:', $code);
        $path = explode('/', preg_replace('/\]$/', '', $path[1]));
        if(strlen($path[1]) == 1) $path[1] = '0'.$path[1];
        $file = '[git:https://github.com/gbaptista/ruby-immersion/blob/master/lib/'.$path[0].'/'.$path[1].'.rb]';
      }

      // GitHub (Custom Ruby Immersion Test) - https://github.com/gbaptista/ruby-immersion
      elseif(preg_match('/^\[ruby_test:.*\]/', $code)) {
        $path = explode('[ruby_test:', $code);
        $path = explode('/', preg_replace('/\]$/', '', $path[1]));
        if(strlen($path[1]) == 1) $path[1] = '0'.$path[1];
        $file = '[git:https://github.com/gbaptista/ruby-immersion/blob/master/test/'.$path[0].'/test_'.$path[1].'.rb]';
      }

      // Default
      else $file = $code;

      // GitHub - https://github.com/
      if(preg_match('/:\/\/github.com/', $file)) {
        $source = preg_replace('/^\[git:|\]$/', '', $file);
        if(!empty($s_line)) $source .= "#L$s_line";
        $raw = str_replace('://github.com', '://raw.github.com', $source);
        $raw = str_replace('/blob/', '/', $raw);
      }

      // GitHub Gist - https://gist.github.com/
      elseif(preg_match('/:\/\/gist.github.com/', $file)) {
        $source = preg_replace('/^\[git:|\]$/', '', $file);
        $raw = str_replace('#', '/raw/', $source);
      }

      // Bitbucket - https://bitbucket.org/
      elseif(preg_match('/:\/\/bitbucket.org/', $file)) {
        $source = preg_replace('/^\[git:|\]$/', '', $file);
        $raw = str_replace('/src/', '/raw/', $source);
      }

      if(!empty($raw)) {

        $raw = file_get_contents($raw);

        // GitHub (Custom Ruby Immersion) - https://github.com/gbaptista/ruby-immersion
        if(preg_match('/^\[ruby_code:.*\]/', $code)) {
          $raw = preg_replace("/\# encoding\: utf\-8\n{2,}|^\n{1,}/", '', $raw);
        }

        // GitHub (Custom Ruby Immersion Test) - https://github.com/gbaptista/ruby-immersion
        elseif(preg_match('/^\[ruby_test:.*\]/', $code)) {
          $raw = str_replace("require 'test/unit'", '', $raw);
          $raw = str_replace("require 'include_file'", '', $raw);
          $raw = str_replace("IncludeFile::inject __FILE__", '', $raw);
          $raw = preg_replace("/class LoveTest.* \< Test\:\:Unit\:\:TestCase/", '', $raw);
          $raw = str_replace("def test_with_love", '', $raw);
          $raw = preg_replace("/end$/", '', trim($raw));
          $raw = preg_replace("/end$/", '', trim($raw));
          $raw = preg_replace("/    /", '', trim($raw));
          $raw = preg_replace("/\n{2,}/", "\n\n", trim($raw));
          $raw = preg_replace("/\# encoding\: utf\-8/", '', trim($raw));
          $raw = preg_replace("/^\n{1,}/", '', trim($raw));
        }

        if(!empty($s_line))
          $raw = implode("\n", array_slice(preg_split('/\r\n|\r|\n/', $raw), $s_line-1, ($e_line-$s_line)+1));

        return $raw;
        # return $raw .= "\n\n# $source"; # Todo.

      } else return $code;

    }

    public static function beforeFilter( $content ) {

      if(preg_match_all('/\[git:http.*\]|\[ruby.*:.*\]/', $content, $results))
      {
        foreach($results as $result) {
          foreach($result as $file) $content = str_replace($file, self::raw($file), $content);
        }
      }

      # Escape
      if(preg_match_all('/\[\'git:http.*\]|\[\'ruby.*:.*\]/', $content, $results))
      {
        foreach($results as $result) {
          foreach($result as $file) {
            $content = str_replace($file, str_replace('[\'', '[', $file), $content);
          }
        }
      }

      return $content;

    }

  }

  function WP_Git_Embed() { return WP_Git_Embed::getInstance(); }
  add_action( 'plugins_loaded', 'WP_Git_Embed' );

}