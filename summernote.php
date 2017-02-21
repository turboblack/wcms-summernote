<?php
/**
 * SummerNote plugin.
 *
 * It transforms all the editable areas into SummerNote inline editor.
 *
 * @author  Prakai Nadee <prakai@rmuti.acth>
 * @version 1.0.0
 */
defined('INC_ROOT') OR die('Direct access is not allowed.');

$default_contents_path = 'files';

wCMS::addListener('js', 'loadSummerNoteJS');
wCMS::addListener('css', 'loadSummerNoteCSS');
wCMS::addListener('settings', 'displaySummerNoteSettings');

$contents_path = wCMS::getConfig('contents_path');
if ( ! $contents_path) {
	wCMS::setConfig('contents_path', $default_contents_path);
	$contents_path = $default_contents_path;
}
$contents_path_n = trim($contents_path, "/");
if ($contents_path != $contents_path_n) {
	$contents_path = $contents_path_n;
	wCMS::setConfig('contents_path', $contents_path);
}
$_SESSION['contents_path'] = $contents_path;

function loadSummerNoteJS($args) {
	$script = <<<'EOT'

<!--script src="//cdnjs.cloudflare.com/ajax/libs/summernote/0.8.2/summernote.js"></script-->
<script src="plugins/summernote/summernote/summernote.js"></script>
<script src="plugins/summernote/js/files.js"></script>
<script>
$(function() {
	var s=$("span.editable").clone();
	s.each(function(a,b){
		var c=s[a].id,d=s[a].outerHTML.replace(/span/,"div");
		$("span.editable#"+c).replaceWith(d);
	});
	var editElements = {};

	$('.editable').summernote({

		airMode: true,
		popover: {
			image: [
				['imagesize', ['imageSize100', 'imageSize50', 'imageSize25']],
				['float', ['floatLeft', 'floatRight', 'floatNone']],
				['remove', ['removeMedia']]
			],
			link: [
				['link', ['linkDialogShow', 'unlink']]
			],
			air: [
				['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'clear']],
				['fontsize', ['fontsize']],
				['color', ['color']],
				['para', ['ul', 'ol', 'paragraph']],
				['style', ['style']],
				['insert', ['image', 'doc', 'link', 'video', 'hr']], // doc and image is customized code
				//['insert', ['link', 'picture', 'video', 'hr']],
				['table', ['table']],
				//['misc', ['fullscreen']]
			],
			styleTags: ['p', 'blockquote', 'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
		},
		placeholder: 'Click and write here...',
		callbacks: {
			onChange: function(contents, $editable) {
				editElements[$(this).attr('id')] = contents;
			},
			onBlur: function() {
				if (editElements[$(this).attr('id')]!=undefined) {
					var id = $(this).attr('id');
					var content = editElements[$(this).attr('id')];
					editElements[$(this).attr('id')] = undefined;
					$.post("",{
						fieldname: id,
						content: content
					});
				}
			},
			onImageUpload: function(files) {
				var $editor = $(this);
				file = files[0];
				data = new FormData();
				data.append("file", file);
				$.ajax({
					type: "POST",
					url: "plugins/summernote/file.php?do=ul&type=images",
					data: data,
					cache: false,
					contentType: false,
					processData: false,
					success: function(url) {
						$editor.summernote('insertImage', url);
					},
					error: function(data) {
						alert('Image upload error: '+data);
					}
				});
			}
		},
	});
});

</script>
EOT;
	array_push($args[0], $script);
	return $args;
}

function loadSummerNoteCSS($args) {

	echo $contents_path;

	$script = <<<'EOT'

<!--link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/summernote/0.8.2/summernote.css" type="text/css" media="screen" charset="utf-8"-->
<link rel="stylesheet" href="plugins/summernote/summernote/summernote.css" type="text/css" media="screen" charset="utf-8">
<!--link rel="stylesheet" href="plugins/summernote/css/font-awesome.min.css" type="text/css" media="screen" charset="utf-8"-->
<link rel="stylesheet" href="plugins/summernote/css/style.css" type="text/css" media="screen" charset="utf-8">
EOT;
	array_push($args[0], $script);
	return $args;
}

function displaySummerNoteSettings ($args) {
	if ( ! wCMS::$loggedIn) return $args;

	$settings = '
		<label for="contents_path" data-toggle="tooltip" data-placement="right" title="Path of uploaded files, reference to root path of CMS, eg: files">SummerNote Contents path</label>
		<span id="contents_path" class="change editText">'.wCMS::getConfig('contents_path').'</span>
	';
	array_push($args, $settings);
	return $args;
}