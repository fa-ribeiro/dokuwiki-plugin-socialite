<?php
/**
 * DokuWiki Plugin lsb (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Fernando Ribeiro <pinguim.ribeiro@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class syntax_plugin_lsb_button extends DokuWiki_Syntax_Plugin {
    /**
     * @return string Syntax mode type
     */
    public function getType() {
        return 'substition';
    }
    /**
     * @return string Paragraph type
     */
    public function getPType() {
        return 'normal';
    }
    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort() {
        return 999;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~lsb\b.*?~~',$mode,'plugin_lsb_button');
        $this->Lexer->addSpecialPattern('~~LSB\b.*?~~',$mode,'plugin_lsb_button');
    }

    /**
     * Handle matches of the lsb syntax
     *
     * @param string $match The match of the syntax
     * @param int    $state The state of the handler
     * @param int    $pos The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler &$handler){
        $match = strtolower(trim(substr($match, 5, -2))); // strip markup

        // checks if a display mode is passed
        if (substr($match, 0, 1) === ':')  {
            list($display) = explode(' ', $match);
            $display = substr($display, 1);
            $networks = trim(substr($match, strlen($display) + 1 ));
        }

        if (empty($networks)) {
            $networks = strtolower(trim($this->getConf('networks')));
        }

        return array(
            'display'  => $display,
            'networks' => explode(' ', $networks)
            );
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string         $mode      Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer  $renderer  The renderer
     * @param array          $data      The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer &$renderer, $data) {

        if($mode != 'xhtml') return false;

        // validation list of available display modes
        $valid_displays = array('name', 'icon', 'color');
        // validation list of available social networks
        $valid_networks = array('twitter', 'facebook', 'googleplus', 'linkedin', 'pinterest', 'tumblr', 'reddit', 'taringa', 'email');

        if (in_array($data['display'], $valid_displays)) {
            $display = $data['display'];
        } else {
            $display = $this->getConf('display') ;
        }

        $renderer->doc .= '<ul class="lsb">';
        foreach ($data['networks'] as $network) {
            if (in_array($network, $valid_networks)) {
                $renderer->doc .= $this->lsb_button($display, $network);
            }
        }
        $renderer->doc .= '</ul>';

        return true;
    }

    /**
     * Render xhtml output for share buttons
     *
     * @param string    $display    The display mode
     * @param string    $network    The social network to render the button to
     * @return string   Xhtml code for button.
     */
    protected function lsb_button ($display, $network) {
        global $ID;
        global $INFO;

        $url      = rawurlencode(wl($ID,'',true));
        $title    = rawurlencode(($INFO['meta']['title']) ? $INFO['meta']['title'] : $meta);
        $abstract = rawurlencode($INFO['meta']['description']['abstract']);
        $class    = $display . '-' . $network;

        // see: http://builtbyboon.com/blog/simple-social-sharing-buttons
        // see: https://github.com/cferdinandi/social-sharing
        // <a href="https://twitter.com/intent/tweet?text=YOUR-TITLE&url=YOUR-URL&via=TWITTER-HANDLE&hashtags=YOUR,HASH,TAGS">Tweet</a>
        // <a href="https://www.facebook.com/sharer/sharer.php?u=YOUR-URL">Share on Facebook</a>
        // <a href="https://plus.google.com/share?url=YOUR-URL">Plus on googleplus</a>
        // <a href="https://www.linkedin.com/shareArticle?mini=true&url=YOUR-URL&title=YOUR-TITLE&summary=YOUR-SUMMARY&source=YOUR-URL">Share on LinkedIn</a>
        // <a href="https://pinterest.com/pin/create/button/?url=YOUR-URL&description=YOUR-DESCRIPTION&media=YOUR-IMAGE-SRC">Pin on Pinterest</a>
        // <a href="https://vk.com/share.php?url=YOUR-URL&title=YOUR-TITLE&description=YOUR-DESCRIPTION&image=YOUR-IMAGE-SRC&noparse=true">Share on VK</a>
        // <a href="https://www.xing-share.com/app/user?op=share;sc_p=xing-share;url=YOUR-URL">Share on Xing</a>
        // <a href="http://www.tumblr.com/share/link?url=YOUR-URL&description=YOUR-DESCRIPTION">Share on Tumblr</a>
        // <a href="http://www.reddit.com/submit?url=YOUR_URL&title=YOUR_TITLE">Share on Reddit</a>
        // <a href="http://www.taringa.net/widgets/share.php?url=YOUR_URL&body=YOUR-DESCRIPTION">Compartir en Taringa</a>
        // <a href="mailto:?subject=YOUR-TITLE&body=YOUR-SUMMARY">Email</a>

        switch ($network) {
            case 'twitter':
                $name = 'Twitter';
                $href = 'https://twitter.com/intent/tweet?url=' . $url . '&text='. $title;
                if (!empty($this->getConf('twitter_user'))) { $href .= "&via=" . rawurlencode($this->getConf('twitter_user')); }
                break;
            case 'facebook':
                $name = 'Facebook';
                $href = 'http://www.facebook.com/sharer.php?u='. $url;
                break;
            case 'googleplus':
                $name = 'Google+';
                $href = 'https://plus.google.com/share?url='. $url;
                break;
            case 'linkedin':
                $name = 'LinkedIn';
                $href = 'https://www.linkedin.com/shareArticle?url='. $url .'&title=' . $title . '&summary=' . $abstract . '&mini=true&source=' . $url;
                break;
            case 'pinterest':
                $name = 'Pinterest';
                $href = 'https://pinterest.com/pin/create/button/?url='. $url .'&description=' . $title;
                break;
            case 'tumblr':
                $name = 'Tumblr';
                $href = 'http://www.tumblr.com/share/link?url='. $url .'&description=' . $title;
                break;
            case 'reddit':
                $name = 'Reddit';
                $href = 'http://www.reddit.com/submit?url='. $url .'&title=' . $title;
                break;
            case 'taringa':
                $name = 'Taringa';
                $href = 'http://www.taringa.net/widgets/share.php?url='. $url .'&body=' . $title;
                break;
            case 'email':
                $name = 'Email';
                $href = 'mailto:?subject='. $title .'&body=' . $url . ': '. $abstract;
                break;
        }

        $xhtml  = '<li class="lsb-item-' . $class . '">';
        $xhtml .= '<a class="lsb-link-' . $class . '" href="' . $href . '">' . $name . '</a>';
        $xhtml .= '</li>';

        return $xhtml;
    }

}
