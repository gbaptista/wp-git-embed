<?php

/*
Plugin Name: WP-Git-Embed
Plugin URI: http://wordpress.org/extend/plugins/wp-git-embed/
Description: Embed GitHub, Gist or Bitbucket files.
Version: 0.4
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

      if(preg_match('/:.*@.*]/', $code, $format)) {

        $format = explode('@', $format[0]);
        $format = str_replace(':', '', $format[0]);

        $code = str_replace($format.'@', '', $code);

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
        $service = 'github';
      }

      // GitHub Gist - https://gist.github.com/
      elseif(preg_match('/:\/\/gist.github.com/', $file)) {
        $source = preg_replace('/^\[git:|\]$/', '', $file);
        $raw = str_replace('#', '/raw/', $source);
        $service = 'gist';
      }

      // Bitbucket - https://bitbucket.org/
      elseif(preg_match('/:\/\/bitbucket.org/', $file)) {
        $source = preg_replace('/^\[git:|\]$/', '', $file);
        $raw = str_replace('/src/', '/raw/', $source);
        $service = 'bitbucket';
      } else {

        $source = NULL;
        $raw = preg_replace('/^\[file:|\]$/', '', $file);
        $service = NULL;

      }

      if(!empty($raw)) {

        $link = $raw;

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
        else {
          $s_line = 1;
        }

        if(!empty($format)) {
          if(preg_match('/^pre.*/', $format)) {
            $format = explode('_', $format);
            $raw = '<pre lang="'. $format[1] .'" line="'.$s_line.'">' . $raw . '</pre>';
            $links = TRUE;
            $format = 'pre';
          } elseif(preg_match('/^sourcecode.*/', $format)) {
            $format = explode('_', $format);
            $raw = '[sourcecode language="'.$format[1].'"]' . $raw . '[/sourcecode]';
            $links = TRUE;
            $format = 'sourcecode';
          } else {
            $links = FALSE;
          }
        } else $links = FALSE;

        if($links) {

          $file_name = preg_replace('/#.*/', '', end(preg_split('/\/|\\\/', $link)));
          $file_name = preg_replace('/\?.*/', '', $file_name);

          //echo $source . '<br />' . $link; exit;

          if($format == 'pre')
           $raw .= '<div class="wp-git-embed" style="margin-bottom:10px; background-color:#def; border:1px solid #CCC; text-align:right; width:99%; margin-top:-21px; font-size:11px; font-style:italic;"><span style="display:inline-block; padding:4px;">'.$file_name.'</span>';
          else
            $raw .= '<div class="wp-git-embed" style="margin-bottom:10px; border:1px solid #CCC; text-align:right; width:99%; margin-top:-13px; font-size:11px; font-style:italic;"><span style="display:inline-block; padding:4px;">'.$file_name.'</span>';

          if(preg_match('/^http.*:/', $link)) {

            if(empty($source))
              $raw .= '<a style="display:inline-block; padding:4px 6px;" href="' . $link . '" target="_blank">download file</a>';
            else {
              $raw .= '<a style="display:inline-block; padding:4px 6px;" href="' . $link . '" target="_blank">view raw</a>';
              $raw .= '<a style="display:inline-block; padding:4px 6px; float:left;" href="' . $source . '" target="_blank">view file on ';

              if($service == 'github') $raw .= '<strong>GitHub</strong></a>';
              elseif($service == 'gist') $raw .= '<strong>GitHub Gist</strong></a>';
              elseif($service == 'bitbucket') $raw .= ' <strong>Bitbucket</strong></a>';
            }
            
          }

          $raw .= '</div>';

        }

        return $raw;
        # return $raw .= "\n\n# $source"; # Todo.

      } else return $code;

    }

    public static function beforeFilter( $content ) {

      if(preg_match_all('/\[git:.*http.*\]|\[file:.*\]|\[ruby.*:.*\]/', $content, $results))
      {
        foreach($results as $result) {
          foreach($result as $file) $content = str_replace($file, self::raw($file), $content);
        }
      }

      # Escape
      if(preg_match_all('/\[\'git.*\]|\[\'file:.*\]|\[\'ruby.*:.*\]/', $content, $results))
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