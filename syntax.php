<?php

/**
 * Webcomics Plugin
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Christoph Lang <calbity@gmx.de>
 */

// based on http://wiki.splitbrain.org/plugin:tutorial

// must be run within Dokuwiki
if (! defined('DOKU_INC')) die();

if (! defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_PLUGIN . 'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_webcomics extends DokuWiki_Syntax_Plugin
{

  function getInfo ()
  {
    return array(
      'author' => 'Christoph Lang',
      'email' => 'calbity@gmx.de',
      'date' => '2013-09-17',
      'name' => 'Webcomics Plugin',
      'desc' => 'It displays various Webcomics. Based on Dilbert Plugin.',
      'url' => 'http://www.christophs-blog.de/dokuwiki-plugins/'
    );
  }

  private function _listhd ($type)
  {
    require_once (DOKU_INC . 'inc/HTTPClient.php');

    $urls = array(
      # "GARFIELD" => 'http://feeds.hafcom.nl/garfield.xml',
      # "CYANIDE" => 'http://pipes.yahoo.com/pipes/pipe.run?_id=9b91d1900e14d1caff163aa6fa1b24bd&_render=rss',
      "XKCD" => 'http://xkcd.com/rss.xml',
      "DILBERT" => 'http://pipes.yahoo.com/pipes/pipe.run?_id=1fdc1d7a66bb004a2d9ebfedfb3808e2&_render=rss',
      "SHACKLES" => 'http://feeds2.feedburner.com/virtualshackles',
    );

    if ( ! isset($urls[$type]) ) {
      return "[{$type} - no url given]";
    }

    $ch    = new DokuHTTPClient();
    $piece = $ch->get($urls[$type]);
    $xml   = simplexml_load_string($piece);

    preg_match("/src=\"(.[^\"]*)\"/i", $xml->channel->item->description, $matches);

    if ( ! empty($matches) ) {

      return '<img src="' . $matches[1] . '" alt=""/>';
    }

    return "[{$type} - couldn't parse image tag]";
  }

  function connectTo ($mode)
  {
    $this->Lexer->addSpecialPattern('\[XKCD\]', $mode, 'plugin_webcomics');
    $this->Lexer->addSpecialPattern('\[GARFIELD\]', $mode, 'plugin_webcomics');
    $this->Lexer->addSpecialPattern('\[DILBERT\]', $mode, 'plugin_webcomics');
    $this->Lexer->addSpecialPattern('\[SHACKLES\]', $mode, 'plugin_webcomics');
    $this->Lexer->addSpecialPattern('\[CYANIDE\]', $mode, 'plugin_webcomics');
  }

  function getType ()
  {
    return 'substition';
  }

  function getSort ()
  {
    return 667;
  }

  function handle ($match, $state, $pos, &$handler)
  {
    $match = str_replace(array("[", "]"), array("", ""), $match);
    return array($match, $state, $pos);
  }

  function render ($mode, &$renderer, $data)
  {
    if ($mode == 'xhtml')
    {
      $renderer->doc .= $this->_listhd($data[0]);
      return true;
    }

    return false;
  }
}