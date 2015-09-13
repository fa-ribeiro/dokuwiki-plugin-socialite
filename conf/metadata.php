<?php
/**
 * Options for the lsb plugin
 *
 * @author Fernando Ribeiro <pinguim.ribeiro@gmail.com>
 */

//$meta['fixme'] = array('string');


$meta['networks']       = array('string', '_pattern' => '/^[a-zA-Z\s]*$/');

$meta['display']        = array('multichoice','_choices' => array('name', 'icon', 'color'));

$meta['twitter_user']   = array('string', '_pattern' => '/^[a-zA-Z_]*$/');
