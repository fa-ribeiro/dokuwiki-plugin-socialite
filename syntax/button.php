<?php
/**
 * DokuWiki Plugin socialite (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Fernando Ribeiro <fribeiro@fribeiro.org>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class syntax_plugin_socialite_button extends DokuWiki_Syntax_Plugin {
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
        $this->Lexer->addSpecialPattern('~~socialite\b.*?~~',$mode,'plugin_socialite_button');
    }

    /**
     * Handle matches of the socialite syntax
     *
     * @param string $match The match of the syntax
     * @param int    $state The state of the handler
     * @param int    $pos The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler){
        $match = strtolower(trim(substr($match, 11, -2))); // strip markup

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
    public function render($mode, Doku_Renderer $renderer, $data) {

        if($mode != 'xhtml') return false;

        // validation list of available display modes
        $valid_displays = array('name', 'icon', 'color');
        // validation list of available social networks
        $valid_networks = array('twitter', 'facebook', 'googleplus',
                                'linkedin', 'pinterest', 'tumblr',
                                'reddit', 'taringa', 'stumbleupon',
                                'telegram', 'hackernews', 'xing', 'vk', 'email');

        if (in_array($data['display'], $valid_displays)) {
            $display = $data['display'];
        } else {
            $display = $this->getConf('display') ;
        }

        $renderer->doc .= '<ul class="socialite">';
        foreach ($data['networks'] as $network) {
            if (in_array($network, $valid_networks)) {
                $renderer->doc .= $this->socialite_button($display, $network);
            }
        }
        $renderer->doc .= '</ul>';

        return true;
    }

    /**
     * Render xhtml output for share buttons
     *
     * @param string    $display    The display mode
     * @param string    $network    The social network
     * @return string   Xhtml code for button.
     */
    protected function socialite_button ($display, $network) {
        global $ID;
        global $INFO;

        $url      = rawurlencode(wl($INFO['id'], '', true));
        $title    = rawurlencode(($INFO['meta']['title']) ? $INFO['meta']['title'] : $meta);
        $abstract = rawurlencode($INFO['meta']['description']['abstract']);

        $target = $this->getConf('target') ;

        switch ($network) {
            case 'twitter':
                $name = 'Twitter';
                $href = 'https://twitter.com/intent/tweet?url=' . $url . '&text='. $title;
                if ($this->getConf('twitter_user')) { $href .= "&via=" . rawurlencode($this->getConf('twitter_user')); }
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
            case 'stumbleupon':
                $name = 'StumbleUpon';
                $href = 'http://www.stumbleupon.com/submit?url='. $url .'&title=' . $title;
                break;
            case 'telegram':
                $name = 'Telegram';
                $href = 'https://telegram.me/share/url?url='. $url .'&text=' . $title;
                break;
            case 'hackernews':
                $name = 'Hacker News';
                $href = 'https://news.ycombinator.com/submitlink?u='. $url .'&t=' . $title;
                break;
            case 'xing':
                $name = 'Xing';
                $href = 'https://www.xing-share.com/app/user?op=share;sc_p=xing-share;url='. $url;
                break;
            case 'vk':
                $name = 'Vk';
                $href = 'https://vk.com/share.php?url='. $url .'&title=' . $title . '&description=' . $abstract . '&noparse=true';
                break;
            case 'email':
                $name = 'Email';
                $href = 'mailto:?subject='. $title .'&body=' . $url . ': '. $abstract;
                break;
        }

        $xhtml  = '<li class="' . $display . '">';
        $xhtml .= '<a class="' . $network . '" target="' . $target . '" href="' . $href . '">' . $name . '</a>';
        $xhtml .= '</li>';

        return $xhtml;
    }

}
