<?php

require_once __DIR__ . DS . 'markdown.php';

define('MARKDOWNEXTRAEXTENDED_VERSION', "0.3");

function MarkdownExtended($text, $default_claases = array()) {
    $parser = new MarkdownExtraExtended_Parser($default_claases);
    return $parser->transform($text);
}

class MarkdownExtraExtended_Parser extends MarkdownExtra_Parser {
    # Tags that are always treated as block tags:

    var $block_tags_re = 'figure|figcaption|p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|form|fieldset|iframe|hr|legend';
    var $default_classes;

    function MarkdownExtraExtended_Parser($default_classes = array()) {
        $default_classes = $default_classes;

        $this->block_gamut += array(
            "doFencedFigures" => 7,
        );

        parent::__construct();
    }

    function doBlockQuotes($text) {
        $text = preg_replace_callback('/
			(?>^[ ]*>[ ]?
				(?:\((.+?)\))?
				[ ]*(.+\n(?:.+\n)*)
			)+
			/xm', array(&$this, '_doBlockQuotes_callback'), $text);

        return $text;
    }

    function _doBlockQuotes_callback($matches) {
        $cite = $matches[1];
        $bq = '> ' . $matches[2];
        # trim one level of quoting - trim whitespace-only lines
        $bq = preg_replace('/^[ ]*>[ ]?|^[ ]+$/m', '', $bq);
        $bq = $this->runBlockGamut($bq);  # recurse

        $bq = preg_replace('/^/m', "  ", $bq);
        # These leading spaces cause problem with <pre> content,
        # so we need to fix that:
        $bq = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx', array(&$this, '_doBlockQuotes_callback2'), $bq);

        $res = "<blockquote";
        $res .= empty($cite) ? ">" : " cite=\"$cite\">";
        $res .= "\n$bq\n</blockquote>";
        return "\n" . $this->hashBlock($res) . "\n\n";
    }

    function doFencedCodeBlocks($text) {
        $less_than_tab = $this->tab_width;

        $text = preg_replace_callback('{
				(?:\n|\A)
				# 1: Opening marker
				(
					~{3,}|`{3,} # Marker: three tilde or more.
				)

				[ ]?(\w+)?(?:,[ ]?(\d+))?[ ]* \n # Whitespace and newline following marker.

				# 3: Content
				(
					(?>
						(?!\1 [ ]* \n)	# Not a closing marker.
						.*\n+
					)+
				)

				# Closing marker.
				\1 [ ]* \n
			}xm', array(&$this, '_doFencedCodeBlocks_callback'), $text);
        return $text;
    }

    function _doFencedCodeBlocks_callback($matches) {
        $codeblock = $matches[4];
        $codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
        $codeblock = preg_replace_callback('/^\n+/', array(&$this, '_doFencedCodeBlocks_newlines'), $codeblock);
        //$codeblock = "<pre><code>$codeblock</code></pre>";
        //$cb = "<pre><code";
        $cb = empty($matches[3]) ? "<pre class='prettyprint'><code" : "<pre class=\"linenums:$matches[3]\"><code";
        $cb .= empty($matches[2]) ? ">" : " class=\"language-$matches[2]\">";
        $cb .= "$codeblock</code></pre>";
        return "\n\n _doFencedCodeBlocks_callback executed with matches " . print_r($matches, true) . "\n\n" . $this->hashBlock($cb) . "\n\n";
    }

    function doFencedFigures($text) {
        $text = preg_replace_callback('{
			(?:\n|\A)
			# 1: Opening marker
			(
				={3,} # Marker: equal sign.
			)

			[ ]?(?:\[([^\]]+)\])?[ ]* \n # Whitespace and newline following marker.

			# 3: Content
			(
				(?>
					(?!\1 [ ]?(?:\[([^\]]+)\])?[ ]* \n)	# Not a closing marker.
					.*\n+
				)+
			)

			# Closing marker.
			\1 [ ]?(?:\[([^\]]+)\])?[ ]* \n
		}xm', array(&$this, '_doFencedFigures_callback'), $text);

        return $text;
    }

    function _doFencedFigures_callback($matches) {
        # get figcaption
        $topcaption = empty($matches[2]) ? null : $this->runBlockGamut($matches[2]);
        $bottomcaption = empty($matches[4]) ? null : $this->runBlockGamut($matches[4]);
        $figure = $matches[3];
        $figure = $this->runBlockGamut($figure); # recurse

        $figure = preg_replace('/^/m', "  ", $figure);
        # These leading spaces cause problem with <pre> content,
        # so we need to fix that - reuse blockqoute code to handle this:
        $figure = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx', array(&$this, '_doBlockQuotes_callback2'), $figure);

        $res = "<figure>";
        if (!empty($topcaption)) {
            $res .= "\n<figcaption>$topcaption</figcaption>";
        }
        $res .= "\n$figure\n";
        if (!empty($bottomcaption) && empty($topcaption)) {
            $res .= "<figcaption>$bottomcaption</figcaption>";
        }
        $res .= "</figure>";
        return "\n" . $this->hashBlock($res) . "\n\n";
    }

    /**
     * Extended to pass URLs through Laravel.
     *
     * @internal
     * @param  array  $matches
     * @return string
     * @uses Laravel\URL::to()
     */
    function _doAnchors_inline_callback($matches) {
        $link_text = $this->runSpanGamut($matches[2]);
        $url = $matches[3] == '' ? $matches[4] : $matches[3];
        $title = $matches[6];

        $url = $this->encodeAttribute($url);

        // BEGIN: Modification to pass URLs through Laravel
        if ($url[0] !== '#' and is_null(parse_url($url, PHP_URL_SCHEME))) {
            $url = \Laravel\URL::to($url);
        }
        // END

        $result = "<a href=\"$url\"";
        if (isset($title)) {
            $title = $this->encodeAttribute($title);
            $result .= " title=\"$title\"";
        }

        $link_text = $this->runSpanGamut($link_text);
        $result .= ">$link_text</a>";

        return $this->hashPart($result);
    }

    /**
     * Extended to pass URLs through Laravel.
     *
     * @internal
     * @param  array  $matches
     * @return string
     * @uses Laravel\URL::to_asset()
     */
    function _doImages_inline_callback($matches) {
        $alt_text = $matches[2];
        $url = $matches[3] == '' ? $matches[4] : $matches[3];
        $title = $matches[6];

        $alt_text = $this->encodeAttribute($alt_text);
        $url = $this->encodeAttribute($url);

        // BEGIN: Modification to pass URLs through Laravel
        if ($url[0] !== '#' and is_null(parse_url($url, PHP_URL_SCHEME))) {
            $url = Laravel\URL::to_asset($url);
        }
        // END

        $result = "<img src=\"$url\" alt=\"$alt_text\"";
        if (isset($title)) {
            $title = $this->encodeAttribute($title);
            $result .= " title=\"$title\""; # $title already quoted
        }
        $result .= $this->empty_element_suffix;

        return $this->hashPart($result);
    }

}