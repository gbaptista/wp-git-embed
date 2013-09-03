<?php

/*
Plugin Name: WP-Git-Embed
Plugin URI: http://wordpress.org/extend/plugins/wp-git-embed/
Description: Embed GitHub, Gist or Bitbucket files.
Version: 0.5
Author: Guilherme Baptista, Willy Bahuaud
Author URI: http://gbaptista.com
License: MIT
*/
DEFINE( 'GITPLUGINURL', trailingslashit( WP_PLUGIN_URL ) . basename( dirname( __FILE__ ) ) );
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
      /**

      */
      $transientname = 'code_wge_' . substr( md5( $code ), 0, 29 ); // I CHANGED TRANSIENT PREFIXE
      if( false === apply_filters( 'cache_embeded_code', ( $raw = get_transient( $transientname ) ) ) ) {
      /**

      */
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

      // Default
      $file = $code;

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

          $raw = preg_replace("/\</", '&lt;', trim($raw));
          $raw = preg_replace("/\>/", '&gt;', trim($raw));

          if(!empty($s_line))
            $raw = implode("\n", array_slice(preg_split('/\r\n|\r|\n/', $raw), $s_line-1, ($e_line-$s_line)+1));
          else {
            $s_line = 1;
          }

          if(!empty($format)) {
             if(preg_match('/^precode.*/', $format)) {
              $format = explode('_', $format);
              $raw = '<pre class="language-'. $format[1] .'"><code class="language-'. $format[1] .'" line="'.$s_line.'">' . $raw . '</code></pre>';
              $links = TRUE;
              $format = 'pre';
            } elseif(preg_match('/^pre.*/', $format)) {
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

            if($format == 'pre' || $format == 'precode')
             $raw .= '<div class="wp-git-embed"><span class="filename">'.$file_name.'</span>';
            else
              $raw .= '<div class="wp-git-embed"><span class="filename">'.$file_name.'</span>';

            if(preg_match('/^http.*:/', $link)) {

              if(empty($source))
                $raw .= '<a class="github" href="' . $link . '" target="_blank">download file</a>';
              else {
                $raw .= '<a class="raw" href="' . $link . '" target="_blank">view raw</a>';
                $raw .= '<a class="github" href="' . $source . '" target="_blank">view file on ';

                if($service == 'github') $raw .= '<strong>GitHub</strong></a>';
                elseif($service == 'gist') $raw .= '<strong>GitHub Gist</strong></a>';
                elseif($service == 'bitbucket') $raw .= ' <strong>Bitbucket</strong></a>';
              }
              
            }

            $raw .= '</div>';
            
          }
          set_transient( $transientname, htmlspecialchars( $raw ) ); // I REMOVED TIME ARGUMENT
        
        return $raw;
        # return $raw .= "\n\n# $source"; # Todo.

      /**

      */
      } else return $code;
      } else {
          return htmlspecialchars_decode( $raw );
      }

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

  function enqueue_prismjs(){
      wp_register_script( 'prismjs', GITPLUGINURL.'/js/prism.js', array('jquery'), '0.9', true );
      wp_register_style( 'prismcss', GITPLUGINURL.'/css/' . apply_filters( 'prism_css', 'prism-okaidia' ) . '.css', false, '0.95', 'all' );      
      wp_enqueue_style( 'prismcss' );
      wp_enqueue_script( 'jquery' );
      wp_enqueue_script( 'prismjs' );
    }
  add_action( 'wp_enqueue_scripts', 'enqueue_prismjs' );
}

add_action('emptying_code_transient', 'drop_wge_transients');

/**
false cron :-)
*/
function transient_cleaning() {
  if ( ! wp_next_scheduled( 'emptying_code_transient' ) ) {
    wp_schedule_event( time(), apply_filters( 'emptying_code_transient', 'weekly' ), 'emptying_code_transient' );
  }
}
add_action('wp', 'transient_cleaning');

function drop_wge_transients() {
  global $wpdb;
  $wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_code_wge_%'") );
}