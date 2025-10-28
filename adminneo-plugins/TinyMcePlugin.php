<?php

namespace AdminNeo;

/**
 * Use TinyMCE 7 editor for all edit fields containing "_html" in their name.
 *
 * @link https://www.tiny.cloud/docs/tinymce/latest/php-projects/
 * @link https://www.tiny.cloud/docs/tinymce/latest/basic-setup/
 * @link https://www.tiny.cloud/get-tiny/language-packages/
 *
 * @link https://www.adminneo.org/plugins/#usage
 *
 * @author Jakub Vrana, https://www.vrana.cz/
 * @author Peter Knut
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 */
class TinyMcePlugin extends Plugin
{
	/** @var string */
	private $path;

	/** @var string */
	private $licenseKey;

	public function __construct($path = "tinymce/tinymce.min.js", $licenseKey = "gpl")
	{
		$this->path = $path;
		$this->licenseKey = $licenseKey;
	}

	public function printToHead()
	{
		$lang = get_lang();
		$lang = ($lang == "zh" ? "zh-CN" : ($lang == "zh-tw" ? "zh-TW" : $lang));
		if (!file_exists(dirname($this->path) . "/langs/$lang.js")) {
			$lang = "en";
		}

		echo script_src($this->path);
		?>

		<script <?= nonce(); ?>>
			tinyMCE.init({
				license_key: '<?= js_escape($this->licenseKey); ?>',
				selector: 'textarea[data-editor="tinymce"]',
				width: 800,
				height: 600,
				entity_encoding: 'raw',
				language: '<?= $lang; ?>',
				plugins: 'image link',
				toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | link image'
			});
		</script>

		<?php
		return null;
	}

	public function getFieldInput($table, array $field, $attrs, $value, $function)
	{
		if (str_contains($field["type"], "text") && str_contains($field["field"], "_html")) {
			return "<textarea $attrs cols='50' rows='12' data-editor='tinymce' style='width: 800px; height: 600px;'>" . h($value) . "</textarea>";
		}

		return null;
	}
}
