<?php
/*
Plugin Name: WP-etiketter för Bloggar
Plugin URI: http://www.ostro.se/wp-etiketter-for-bloggar
Description: En enkel plugin för att konvertera Wordpress-etiketter till Bloggarlänkar (<a href='http://www.bloggar.se'>http://www.bloggar.se</a>). Modifierad att fungera med Bloggar samt översatt av Fredrik Ostrozanszky från Wordpress-tillägget <a href='http://www.geekyramblings.org/plugins/wp-tags-to-technorati/'>"WP tags to Technorati"</a>. "Wp tags to Technorati" är skrivet av David Gibbs (<a href='http://www.geekyramblings.org'>http://www.geekyramblings.org</a>). "WP-etiketter för Bloggar" fungerar även med "WP tags to Technorati" installerat. Du kan installera "WP tags to Technorati genom att läaada ner det från <a href='http://wordpress.org/extend/plugins/wp-tags-to-technorati/'>tilläggets sida.</a>
Version: 1.011
Author: Fredrik Ostrozanszky
Author URI: http://www.ostro.se
*/

/*

    Copyright 2007,2008 by David Gibbs <david@midrange.com>
    Copyright 2009 by Fredrik Ostrozanszky <info@ostro.se>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/*

Date		Rev	Modification
11/11/07	0.1	Initital version	
11/13/07	0.2	Added comment markups
11/14/07	0.3	Added class's to generated links and fixed problem
			with single tag.
11/16/07	0.4	Properly encode the generated tag url.
11/22/07	0.5	Added setting so user can change the prefix label
11/26/07	0.6	Added the ability to not include the tags in the 
			footer, so they could be manually put in a theme.
12/12/07	0.7	Added a control to allow technorati tags to open in
			a new window.  Also added version identifier to
 			comment.
02/27/08	0.8	Reformatted setup panel.
			tags2tech_get_tags_links() no longer needs to be
			echo'ed.  When invoked directly, it will output
			the links directly.  It will still work with the
			echo method though.
03/07/08	0.9	Fixed the positioning of the tags.
			tags2tech_get_tags_links() output must once again
			be echoed.
			Fixed a problem where this plug-in broke the 
			visual editor.
07/17/08	0.95	Links can now be tagged with 'rel=nofollow'.
09/07/08        1.0  	Add option to control if tags show up in RSS feed.
09/20/08        1.01  	Add option to control if tags show up on main
			page.
10/26/09        1.011  Modified to work with bloggar.se and translated to
                        Swedish. Name of plugin changed to WP-etiketter
                        för Bloggar.

*/

$etiketter2bloggar_version = 1.011;

$tag_url = "http://bloggar.se/om";

$tag_start = "\n<!-- start wp-etiketter-for-bloggar $etiketter2bloggar_version -->\n";
$tag_end = "\n<!-- end wp-etiketter-for-bloggar -->\n";

set_magic_quotes_runtime(0);

function etiketter2bloggar_content ($text) {

	$include_footer = get_option('etiketter2bloggar_footer');
	$include_feed = get_option('etiketter2bloggar_feed');
	$include_home = get_option('etiketter2bloggar_home');

	if ($include_footer && is_feed() && !$include_feed) {
		$include_footer = false;
	}	
	
	if ($include_footer && is_home() && !$include_home) {
		$include_footer = false;
	}	
	
	if ($include_footer && (!is_feed() || is_feed() && $include_feed)) {
		return $text.etiketter2bloggar_get_tags_links();
	} else {
		return $text;
	}

}

function etiketter2bloggar_get_tags_links() {

	global $tag_start,$tag_end;

	$new_window = get_option('etiketter2bloggar_new_window');

	$tags = get_the_tags();

	$tag_text = get_option('etiketter2bloggar_label')." ";
	
	$count=0;

	$tag_count=count($tags);

	if (is_array($tags)) {
		foreach($tags as $tag) {
			$count++;
			$link = etiketter2bloggar_get_link($tag->name,$new_window);
			$tag_text = $tag_text.($count>1?', ':'').$link;
		}
		$tag_links = "\n<p class='bloggar-tags'>".$tag_text."</p>\n";
	} elseif ($tags->name != "") {
		$tag_links = "\n<p class='bloggar-tags'>".$tag_text.etiketter2bloggar_get_link($tags->name,$new_window)."</p>\n";
	} else {
		$tag_links = "";
	}

	return $tag_start.$tag_links.$tag_end;
}

function etiketter2bloggar_get_link($tag,$new_window = false) {
	global $tag_url;

	$rel_nofollow = get_option('etiketter2bloggar_rel_nofollow');

	$link_rel = 'tag';

	if ($rel_nofollow) {
		$link_rel .= ',nofollow';
	}

	$encoded_tag = urlencode($tag);

	$target = $new_window?'_blank':'_self';

	$link = "<a class='bloggar-link' href='$tag_url/$encoded_tag' rel='$link_rel' target='$target'>$tag</a>";
	
	return $link;
}

function etiketter2bloggar_options_menu() {

	?>
	<div class="wrap">
	<h2>Inställningar för Etiketter till Bloggar</h2>
	<form method="post" action="options.php">
	 <!-- <?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo attribute_escape($_GET['page']); ?>"> -->
	<?php wp_nonce_field('update-options'); ?>

<table class="form-table">
 <tr>
 	<th scope="row" valign="top">Titel för Bloggaretiketter:</th>
 	<td>
	<input id="etiketter2bloggar_label" type="text" name="etiketter2bloggar_label" value="<?php echo get_option('etiketter2bloggar_label'); ?>" />
  	 	<label for="inputid">Text som visas innan etiketterna</label>
 	</td>
 </tr>
 <tr>
 	<th scope="row" valign="top">Öppna Bloggarlänkar i ett nytt fönster?</th>
 	<td>
	<input id=etiketter2bloggar_new_window" type="checkbox" name="etiketter2bloggar_new_window" <?php echo get_option('etiketter2bloggar_new_window')?'checked=checked':''; ?> /> 
  	 	<label for="etiketter2bloggar_new_window">Välj denna för att öppna länkar till Bloggar i ett nytt fönster.</label>
 	</td>
 </tr>
 <tr>
 	<th scope="row" valign="top">Inkludera etiketterna längst ner i inlägget?</th>
 	<td>
	<input id="etiketter2bloggar_footer" type="checkbox" name="etiketter2bloggar_footer" <?php echo get_option('etiketter2bloggar_footer')?'checked=checked':''; ?> /> 
  	 	<label for="etiketter2bloggar_footer">Om du vill inkludera länkarna någon annanstans på sidan måste du välja bort detta och lägga till koden <code>etiketter2bloggar_get_tags_links()</code> någon annan stans på sidan.</label>
 	</td>
 </tr>
 <tr>
 	<th scope="row" valign="top">Lägg till "nofollow" till länkarnas rel atribut?</th>
 	<td>
	<input id=etiketter2bloggar_rel_nofollow" type="checkbox" name="etiketter2bloggar_rel_nofollow" <?php echo get_option('etiketter2bloggar_rel_nofollow')?'checked=checked':''; ?> /> 
  	 	<label for="etiketter2bloggar_rel_nofollow">Välj denna för att lägga till "nofollow" till de genererade länkarnas rel atribut (de har redan värdet 'tag').</label>
 	</td>
 </tr>
 <tr>
 	<th scope="row" valign="top">Inkludera länkarna i RSS flödet?</th>
 	<td>
	<input id=etiketter2bloggar_feed" type="checkbox" name="etiketter2bloggar_feed" <?php echo get_option('etiketter2bloggar_feed')?'checked=checked':''; ?> /> 
  	 	<label for="etiketter2bloggar_feed">Välj denna för att inkludera etiketterna i sidans RSS flöde.</label>
 	</td>
 </tr>
 <tr>
 	<th scope="row" valign="top">Inkludera etiketterna på framsidan?</th>
 	<td>
	<input id=etiketter2bloggar_home" type="checkbox" name="etiketter2bloggar_home" <?php echo get_option('etiketter2bloggar_home')?'checked=checked':''; ?> /> 
  	 	<label for="etiketter2bloggar_home">Välj denna för att inkludera etiketterna på framsidan.</label>
 	</td>
 </tr>
</table>


        <p class="submit">
        <input type="submit" name="Submit" value="Spara ändringar" />
        </p>
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="etiketter2bloggar_label,etiketter2bloggar_footer,etiketter2bloggar_new_window,etiketter2bloggar_rel_nofollow,etiketter2bloggar_feed,etiketter2bloggar_home"/>
	</form>
	</div>
	<?php 

}

function etiketter2bloggar_menu(){
    add_options_page('Etiketter för Bloggar', 'Etiketter för Bloggar', 8, __FILE__, 'etiketter2bloggar_options_menu');
}

function etiketter2bloggar_checkupgrade() {
	global $etiketter2bloggar_version;

	$last_version = get_option('etiketter2bloggar_version');
	$current_version = $etiketter2bloggar_version;
	if ($current_version > $last_version) {
		echo "<!-- upgrading to $etiketter2bloggar_version -->\n";
		$label = get_option('etiketter2bloggar_label');
		echo "<!-- oldlabel = $label -->\n";
		if (substr($label,-1) <> ":") {
			$newlabel = $label.":";
			update_option('etiketter2bloggar_label',$newlabel);
		}
		update_option('etiketter2bloggar_version',$etiketter2bloggar_version);
	}

}

function etiketter2bloggar_activate()
{
        // Let's add some options
	// add_option('etiketter2bloggar_label', 'Bloggar etiketter');
}

function etiketter2bloggar_deactivate()
{
        // Clean up the options
	delete_option('etiketter2bloggar_label');
	delete_option('etiketter2bloggar_footer');
	delete_option('etiketter2bloggar_new_window');
	delete_option('etiketter2bloggar_rel_nofollow');
	delete_option('etiketter2bloggar_feed');
	delete_option('etiketter2bloggar_home');
}

add_option('etiketter2bloggar_version', $etiketter2bloggar_version);
add_option('etiketter2bloggar_label', 'Bloggar etiketter:');
add_option('etiketter2bloggar_footer', true);
add_option('etiketter2bloggar_new_window', false);
add_option('etiketter2bloggar_rel_nofollow', false);
add_option('etiketter2bloggar_feed', true);
add_option('etiketter2bloggar_home', true);
add_filter('the_content', 'etiketter2bloggar_content');
add_action('admin_menu', 'etiketter2bloggar_menu');

// register_activation_hook( __FILE__, 'etiketter2bloggar_activate' );

add_action('activate_wp-etiketter-for-bloggar/wp-etiketter-for-bloggar.php',
	'etiketter2bloggar_activate');
add_action('deactivate_wp-etiketter-for-bloggar/wp-etiketter-for-bloggar.php',
	'etiketter2bloggar_deactivate');

// add_action('wp_head','etiketter2bloggar_checkupgrade');
add_action('plugins_loaded','etiketter2bloggar_checkupgrade');

?>
