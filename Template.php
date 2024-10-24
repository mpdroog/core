<?php
namespace core;

// Template is used for simple page rendering with PHP
// in an opiniated way (maximizing productivity for the developer in a multi-language environment)
class Template {
	public string $base = __DIR__;

	private array $cache_langs = [];  // cached language files
	private array $missing_text = []; // sentences that currently lack a translation [lang]=[sentence1,sentence2...]

	// langfile loads the given file for a language (caching it for fast performance)
	public function langfile(string $lang, string $file): ?array {
		if ($lang === "en") user_error("already english");

		$lookup = $this->cache_langs[$lang][$file] ?? null;
		if ($lookup === null) {
			$fname = $this->base . "/lang.d/$lang/static/$file.json";
			if (! file_exists($fname)) {
				echo sprintf("WARN: Lang(%s) missing langfile\n", $fname);
			} else {
				$lookup = json_decode(file_get_contents($fname), true);
				if (! is_array($lookup)) user_error("Failed decoding $fname");
				$cache_langs[$lang][$file] = $lookup; // cache if user later on
			}
		}
		return $lookup;
	}

	// translate-func avail in templates
	public function translate(string $lang, string $file): callable {
		return function(string $english, array $args = []) use($lang, $file): string {
			if ($lang === "en") return $english;
			if (isset($args["file"])) {
				// Override file from template with underscore version
				// (i.e. used in header/footer to have one file for all pages)
				$file = sprintf("_%s.php", $args["file"]);
			}
			$lookup = $this->langfile($lang, $file);

			if (! isset($lookup[ $english ])) {
				$this->missing_text[ $lang ][ $file ][ $english ] = $english; // Hint builder we don't have this and want it
				//fwrite(STDERR, sprintf("WARN: Lang(%s/%s) missing text=%s\n", $lang, $file, $english) );
				return $english;
			}
			return $lookup[ $english ];
		};
	}

        // strip removes whitespaces to solve elements not pushed on eachother
        // i.e. products page the term selector
	public static function strip(string $htm): string {
		$htm = str_replace("\n", "", $htm);
		$htm = str_replace("\r", "", $htm);
		$htm = str_replace("\t", "", $htm);
		$htm = str_replace("  ", "", $htm);
		return $htm;
	}

	public function missing(): array {
		return $this->missing_text;
	}
}
