<?php
namespace core;

use core\MdMail;
use core\DirtyMarkdown;
use core\Helper;

class Render
{
	/** Render(Markdown) $file with $args */
	public static function mail($file, array $args = [])
	{
		$lang = "en";
		if (isset($_COOKIE["lang"])) {
			$lang = strtolower($_COOKIE["lang"]);
			if (! in_array($lang, ["nl", "en"])) {
				$lang = "en";
			}
		}

		$txt = file_get_contents(sprintf("%s%s_%s.md", TPL_MAIL, $file, $lang));
		foreach ($args as $key => $val) {
			$txt = str_replace("{{ $key }}", $val, $txt);
		}
		$txt = str_replace("{{ site }}", Helper::config("general")["baseurl"], $txt);
		$txt = str_replace("{{ company }}", Helper::config("general")["name"], $txt);
		$txt = str_replace("{{ support }}", Helper::config("general")["name"], $txt);

		$out = [
			"text" => $txt,
			"html" => file_get_contents(TPL_MAIL . "head.md") .
				DirtyMarkdown::parse($txt) .
				file_get_contents(TPL_MAIL . "foot.md")
			,
			"htmlEmbed" => DirtyMarkdown::embeds()
		];
		if (count($out["htmlEmbed"]) === 0) {
			// Force to null against cannot unmarshal array
			$out["htmlEmbed"] = null;
		}
		return $out;
	}

	// Black magic for dead-simple template rendering
	// arg0 = file
	// arg1 = args as array
	public static function php()
	{
		ob_start();
		{
			extract(func_get_arg(1));
			include func_get_arg(0);
		}
		return ob_get_clean();
	}
}
