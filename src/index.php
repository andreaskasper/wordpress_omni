<?php
/*
Plugin Name: Andreas Kasper Plugins For Kyle And Sarah
Plugin URI:  
Description: Call Andreas for questions
Version:     1.0.0
Author:      Andreas Kasper
Author URI:  http://andi.dance
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * [wcr_shortcode description]
 * @param  array  pid    product id from short code
 * @return content          shortcode content if user bought product
 */
add_shortcode('wc_andi_premiumcontent', function ($atts = [], $content = null, $tag = '') {
    include(__DIR__."/inc/shortcodes/wc_andi_premiumcontent.php");
    return $o;
});



function wcr_andi_loginname($atts = [], $content = null, $tag = '') {
	if (is_user_logged_in()) {
		$current_user = wp_get_current_user();
		return 'logged in as '.$current_user->user_login;
	} else {
		return $atts["no"] ?? 'login';
	}
}
add_shortcode('wc_andi_loginname', 'wcr_andi_loginname');



function wcr_andi_youtubechat($atts = [], $content = null, $tag = '')
{
	return '<IFRAME src="https://www.youtube.com/live_chat?v='.$atts["id"].'&embed_domain='.$_SERVER["HTTP_HOST"].'" FRAMEBORDER="0" style="width:100%; height:100%;"></IFRAME>';
}
add_shortcode('wc_andi_youtubechat', 'wcr_andi_youtubechat');



function wcr_andi_livestream1_old($atts = [], $content = null, $tag = '')
{
	return '<section class="elementor-element elementor-element-064504c elementor-section-boxed elementor-section-height-default elementor-section-height-default elementor-section elementor-top-section" data-id="064504c" data-element_type="section">
						<div class="elementor-container elementor-column-gap-default">
				<div class="elementor-row">
				<div class="elementor-element elementor-element-f974b43 elementor-column elementor-col-66 elementor-top-column" data-id="f974b43" data-element_type="column">
			<div class="elementor-column-wrap  elementor-element-populated">
					<div class="elementor-widget-wrap">
				<div class="elementor-element elementor-element-6144d3c elementor-aspect-ratio-169 elementor-widget elementor-widget-video" data-id="6144d3c" data-element_type="widget" data-settings="{&quot;aspect_ratio&quot;:&quot;169&quot;}" data-widget_type="video.default">
				<div class="elementor-widget-container">
					<div class="elementor-wrapper elementor-fit-aspect-ratio elementor-open-inline">
			<iframe class="elementor-video-iframe" allowfullscreen="" title="youtube Video Player" src="https://www.youtube.com/embed/'.$atts["id"].'?feature=oembed&amp;start&amp;end&amp;wmode=opaque&amp;loop=0&amp;controls=1&amp;mute=0&amp;rel=0&amp;modestbranding=0"></iframe>		</div>
				</div>
				</div>
						</div>
			</div>
		</div>
				<div class="elementor-element elementor-element-7a2f690 elementor-column elementor-col-33 elementor-top-column" data-id="7a2f690" data-element_type="column">
			<div class="elementor-column-wrap  elementor-element-populated">
					<div class="elementor-widget-wrap">
				<div class="elementor-element elementor-element-26f0353 elementor-widget elementor-widget-shortcode" data-id="26f0353" data-element_type="widget" data-widget_type="shortcode.default">
				<div class="elementor-widget-container">
					<div class="elementor-shortcode"><iframe src="https://www.youtube.com/live_chat?v='.$atts["id"].'&amp;embed_domain='.$_SERVER["HTTP_HOST"].'" frameborder="0" style="width:100%; height:100%;"></iframe></div>
				</div>
				</div>
						</div>
			</div>
		</div>
						</div>
			</div>
		</section>';
}

function wcr_andi_livestream1($atts = [], $content = null, $tag = '')
{
	return '<table style="width:100%;"><tr><td width="66%">
    
    <div style="position: relative; width:100%; height: 0; padding-bottom: 56.25%"><div style="position: absolute; left: 0; top:0; width:100%; height:100%">
   <iframe class="elementor-video-iframe" allowfullscreen="" title="youtube Video Player" src="https://www.youtube.com/embed/'.$atts["id"].'?feature=oembed&amp;start&amp;end&amp;wmode=opaque&amp;loop=0&amp;controls=1&amp;mute=0&amp;rel=0&amp;modestbranding=0" style="width:100%; height:100%;"></iframe></div></div>
	<a href="https://youtu.be/'.$atts["id"].'" TARGET="_blank"><button>watch it on YouTube</button></a>
   
   </td><td style="vertical-align:top;"><div style="position: relative; width:100%; height:100%;">
   
   <iframe src="https://www.youtube.com/live_chat?v='.$atts["id"].'&amp;embed_domain='.$_SERVER["HTTP_HOST"].'" frameborder="0" style="width:100%; height:100%; min-height:400px;"></iframe>
   </div>
   </td></tr></table>';
}
add_shortcode('wc_andi_livestream1', 'wcr_andi_livestream1');

function wcr_andi_youtube($atts = [], $content = null, $tag = '') {
	return ' <div style="position: relative; width:100%; height: 0; padding-bottom: 56.25%"><div style="position: absolute; left: 0; top:0; width:100%; height:100%">
	<iframe class="elementor-video-iframe" allowfullscreen="" title="youtube Video Player" src="https://www.youtube.com/embed/'.$atts["id"].'?feature=oembed&amp;start&amp;end&amp;wmode=opaque&amp;loop=0&amp;controls=1&amp;mute=0&amp;rel=0&amp;modestbranding=0" style="width:100%; height:100%;"></iframe></div></div>';
	}
	add_shortcode('wc_andi_youtube', 'wcr_andi_youtube');


add_shortcode('andi_freshdesk_help', function($atts = [], $content = null, $tag = '') {
    include(__DIR__."/inc/shortcodes/andi_freshdesk_help.php");
    return $out;
});

require_once(__DIR__."/elementor.php");
