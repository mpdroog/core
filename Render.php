<?php
namespace core;
use core\MdMail;
use core\DirtyMarkdown;
use core\Helper;

class Render {
	/** Render(Twig) $file with $args */
	public static function page($file, array $args = []) {
		$loader = new \Twig_Loader_Array([
			'head' => file_get_contents(ROOT . "tpl/head.tpl"),
			'foot' => file_get_contents(ROOT . "tpl/foot.tpl"),
	    	'index' => file_get_contents($file)
		]);
		$args['rev'] = file_get_contents(ROOT . "assets.rev");
		$args['site'] = Helper::config("general")["baseurl"];
		$twig = new \Twig_Environment($loader);
		return $twig->render('index', $args);
	}

	/** Render(Markdown) $file with $args */
	public static function mail($file, array $args = []) {
		$txt = file_get_contents($file);
		foreach ($args as $key => $val) {
			$txt = str_replace("{{ $key }}", $val, $txt);
		}
		$txt = str_replace("{{ site }}", Helper::config("general")["baseurl"], $txt);

		$out = [
			"text" => $txt,
			"html" => file_get_contents(ROOT . "tpl/mail/head.tpl") .
				DirtyMarkdown::parse($txt) .
				file_get_contents(ROOT . "tpl/mail/foot.tpl")
			,
			"htmlEmbed" => MdMail::embeds() // TODO: broken assumption?
		];
		// Always include uuid so user can access site
		$out["html"] = str_replace("{{ uuid }}", $args["uuid"], $out["html"]);
		return $out;
	}
}
