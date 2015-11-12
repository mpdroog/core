<?php
namespace core;
use core\MdMail;
use core\DirtyMarkdown;
use core\Helper;
use prj\Fn;

class Render {
	/** Render(Twig) $file with $args */
	public static function page($file, array $args = []) {
		$loader = new \Twig_Loader_Array([
			'head' => file_get_contents(TPL_PAGE . "head.tpl"),
			'foot' => file_get_contents(TPL_PAGE . "foot.tpl"),
			'index' => file_get_contents(TPL_PAGE . $file . ".tpl")
		]);
		$args['rev'] = file_get_contents(ROOT . "assets.rev");
		$args['site'] = Helper::config("general")["baseurl"];
		//$args['support'] = Helper::config("general")["support"];
		// TODO: cache?
		$twig = new \Twig_Environment($loader, ["strict_variables" => true]);
		$twig->addFunction(new \Twig_SimpleFunction("lang", function($key) {
			return Fn::lang($key);
		}));
		return $twig->render('index', $args);
	}

	/** Render(Markdown) $file with $args */
	public static function mail($file, array $args = []) {
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

		$out = [
			"text" => $txt,
			"html" => file_get_contents(TPL_MAIL . "head.md") .
				DirtyMarkdown::parse($txt) .
				file_get_contents(TPL_MAIL . "foot.md")
			,
			"htmlEmbed" => DirtyMarkdown::embeds()
		];
		// Always include uuid so user can access site
		$out["html"] = str_replace("{{ uuid }}", $args["uuid"], $out["html"]);
		return $out;
	}
}
