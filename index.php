<?php

/********************************
Simple PHP File Manager
https://github.com/jcampbell1
Copyright John Campbell (jcampbell1)
License: MIT
Forked: https://github.com/xcartmods/simple-file-manager
********************************/

// Disable error report for undefined superglobals
//--------------------------------

error_reporting( error_reporting() & ~E_NOTICE );

// Security options
//--------------------------------

$THIS_FILENAME = 'manager.php'; // This file name!
if (basename(__FILE__) != $THIS_FILENAME) { exit; }

$PASSWORD = 'ch@ng3me123$';  // Set the password, to access the file manager... (optional)
$PASSWORD_STRONG = true;  // Set to true if you want to enforce a strong password - Strong passwords must contain at least 8 characters, 1 letter, 1 number and 1 special character

$allow_delete = true; // Set to false to disable delete button and delete POST request.
$delete_confirm = true; // Set to false to disable delete confirmation alert
$allow_upload = true; // Set to true to allow upload files
$allow_create_folder = true; // Set to false to disable folder creation
$allow_direct_link = true; // Set to false to only allow downloads and not direct link

$disallowed_extensions = ['php','com','bat','cmd','reg','vbs','vbe','jse','sh','jar','java','msi','ws','wsf','scf','scr','pif','hta','cpl','gadget','application','lnk'];  // must be an array. Extensions disallowed to be uploaded
$hidden_extensions = ['php','htaccess','well-known']; // must be an array of lowercase file extensions. Extensions hidden in directory index

$full_width = false; // Set to true for full width container
$bootswatch_theme = 'cerulean'; // Leave blank to use default Bootstrap theme - See: https://www.bootstrapcdn.com/bootswatch/

// Available themes...
//--------------------------------
/*
cerulean
cosmo
cyborg
darkly
flatly
journal
litera
lumen
lux
materia
minty
pulse
sandstone
simplex
sketchy
slate
solar
spacelab
superhero
united
yeti
*/

$show_credit = false;

//--------------------------------

if ($bootswatch_theme) {
	$bs_css = "https://stackpath.bootstrapcdn.com/bootswatch/4.1.3/$bootswatch_theme/bootstrap.min.css"; // https://www.bootstrapcdn.com/bootswatch/
} else {
	$bs_css = "https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"; // https://www.bootstrapcdn.com/
}

$fa_css = "https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"; // https://fontawesome.com/v4.7.0/icons/

//--------------------------------

if ($PASSWORD && $PASSWORD_STRONG) {
	$hasLetter  = preg_match('/[a-zA-Z]/',    $PASSWORD);
	$hasNumber  = preg_match('/\d/',          $PASSWORD);
	$hasSpecial = preg_match('/[^a-zA-Z\d]/', $PASSWORD);
	$hasAll     = $hasLetter && $hasNumber && $hasSpecial;
	if ($PASSWORD == "ch@ng3me123$" || $PASSWORD == "chang3me123$" || $PASSWORD == "ch@ngeme123$" || $PASSWORD == "changeme123$" || strlen($PASSWORD) < 8 || !$hasAll) {
	echo '
		<html lang="en">
		<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="robots" content="noindex, nofollow">
		<title>File Manager</title>
		<link href="'.$bs_css.'" rel="stylesheet">
		<link href="'.$fa_css.'" rel="stylesheet">
		<style>.centered { width: 300px; height: auto; padding: 1rem; transform: translate(-50%, -50%); position: absolute; top: 50%; left: 50%; overflow: hidden; }</style>
		</head>
		<body>
		<div class="centered text-center border rounded shadow-sm bg-light">
		<p><i class="fa fa-3x fa-warning text-danger"></i></p>
		<p>Please change the password in</p>
		<h4>'.$THIS_FILENAME.'</h4>
		<hr>
		<p><em><b>The password must contain...</b></em></p>
		<ul class="text-left">
		<li>At least 8 characters</li>
		<li>At least 1 letter</li>
		<li>At least 1 number</li>
		<li>At least 1 special character</li>
		</ul>
		<hr>
		<p class="m-0"><a class="btn btn-primary" href="'.$THIS_FILENAME.'">OK, I have changed it!</a></p>
		</div>
		</body>
		</html>
	';
	exit;
	}
}

$SESSION_ID = $_SERVER['PHP_SELF'];

if($PASSWORD) {
	session_start();
	//if(!$_SESSION['_sfm_allowed']) {
	if(!$_SESSION[$SESSION_ID]) {
		// sha1, and random bytes to thwart timing attacks.  Not meant as secure hashing.
		$t = bin2hex(openssl_random_pseudo_bytes(10));
		if($_POST['p'] && sha1($t.$_POST['p']) === sha1($t.$PASSWORD)) {
			//$_SESSION['_sfm_allowed'] = true;
			$_SESSION[$SESSION_ID] = true;
			header('Location: ?');
		}
		echo '
		<html lang="en">
		<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="robots" content="noindex, nofollow">
		<title>File Manager</title>
		<link href="'.$bs_css.'" rel="stylesheet">
		<link href="'.$fa_css.'" rel="stylesheet">
		<style>.centered { width: 300px; height: auto; padding: 1rem; transform: translate(-50%, -50%); position: absolute; top: 50%; left: 50%; overflow: hidden; }</style>
		</head>
		<body>
		<div class="centered text-center border rounded shadow-sm bg-light">
		<i class="fa fa-file-text-o fa-3x mb-3"></i>
		<h3 class="mb-3"><b>File Manager</b></h3>
		<form action=? method="post" autocomplete="off" class="m-0">
		<div class="input-group">
		<input type="password" name="p" value="" class="form-control" placeholder="Password" autocomplete="off" required>
		<div class="input-group-append">
		<button class="btn btn-primary" type="submit">Login</button>
		</div>
		</div>
		</form>
		</div>
		</body>
		</html>
		';
		exit;
	}
}

// must be in UTF-8 or `basename` doesn't work
setlocale(LC_ALL,'en_US.UTF-8');

$tmp_dir = dirname($_SERVER['SCRIPT_FILENAME']);
if(DIRECTORY_SEPARATOR==='\\') $tmp_dir = str_replace('/',DIRECTORY_SEPARATOR,$tmp_dir);
$tmp = get_absolute_path($tmp_dir . '/' .$_REQUEST['file']);

if($tmp === false)
	err(404,'File or Directory Not Found');
if(substr($tmp, 0,strlen($tmp_dir)) !== $tmp_dir)
	err(403,"Forbidden");
if(strpos($_REQUEST['file'], DIRECTORY_SEPARATOR) === 0)
	err(403,"Forbidden");


if(!$_COOKIE['_sfm_xsrf'])
	setcookie('_sfm_xsrf', bin2hex(openssl_random_pseudo_bytes(16)), time() + (604800 * 30), "/"); // 86400 = 1 day
if($_POST) {
	if($_COOKIE['_sfm_xsrf'] !== $_POST['xsrf'] || !$_POST['xsrf'])
		err(403,"XSRF Failure");
}

$file = $_REQUEST['file'] ?: '.';
if($_GET['do'] == 'list') {
	if (is_dir($file)) {
		$directory = $file;
		$result = [];
		$files = array_diff(scandir($directory), ['.','..']);
	    foreach($files as $entry) if($entry !== basename(__FILE__) && !in_array(strtolower(pathinfo($entry, PATHINFO_EXTENSION)), $hidden_extensions)) {
    		$i = $directory . '/' . $entry;
	    	$stat = stat($i);
	        $result[] = [
	        	'mtime' => $stat['mtime'],
	        	'size' => $stat['size'],
	        	'name' => basename($i),
	        	'path' => preg_replace('@^\./@', '', $i),
	        	'is_dir' => is_dir($i),
	        	'is_deleteable' => $allow_delete && ((!is_dir($i) && is_writable($directory)) ||
                                                           (is_dir($i) && is_writable($directory) && is_recursively_deleteable($i))),
	        	'is_readable' => is_readable($i),
	        	'is_writable' => is_writable($i),
	        	'is_executable' => is_executable($i),
	        ];
	    }
	} else {
		err(412,"Not a Directory");
	}
	echo json_encode(['success' => true, 'is_writable' => is_writable($file), 'results' =>$result]);
	exit;
} elseif ($_POST['do'] == 'delete') {
	if($allow_delete) {
		rmrf($file);
	}
	exit;
} elseif ($_POST['do'] == 'mkdir' && $allow_create_folder) {
	// don't allow actions outside root. we also filter out slashes to catch args like './../outside'
	$dir = $_POST['name'];
	$dir = str_replace('/', '', $dir);
	if(substr($dir, 0, 2) === '..')
	    exit;
	chdir($file);
	@mkdir($_POST['name']);
	exit;
} elseif ($_POST['do'] == 'upload' && $allow_upload) {
	var_dump($_POST);
	var_dump($_FILES);
	var_dump($_FILES['file_data']['tmp_name']);
	foreach($disallowed_extensions as $ext)
		if(preg_match(sprintf('/\.%s$/',preg_quote($ext)), $_FILES['file_data']['name']))
			err(403,"Files of this type are not allowed.");

	var_dump(move_uploaded_file($_FILES['file_data']['tmp_name'], $file.'/'.$_FILES['file_data']['name']));
	exit;
} elseif ($_GET['do'] == 'download') {
	$filename = basename($file);
	if ($filename != $THIS_FILENAME) {
	header('Content-Type: ' . mime_content_type($file));
	header('Content-Length: '. filesize($file));
	header(sprintf('Content-Disposition: attachment; filename=%s',
		strpos('MSIE',$_SERVER['HTTP_REFERER']) ? rawurlencode($filename) : "\"$filename\"" ));
	ob_flush();
	readfile($file);
	exit;
	}
}
function rmrf($dir) {
	if(is_dir($dir)) {
		$files = array_diff(scandir($dir), ['.','..']);
		foreach ($files as $file)
			rmrf("$dir/$file");
		rmdir($dir);
	} else {
		unlink($dir);
	}
}
function is_recursively_deleteable($d) {
	$stack = [$d];
	while($dir = array_pop($stack)) {
		if(!is_readable($dir) || !is_writable($dir))
			return false;
		$files = array_diff(scandir($dir), ['.','..']);
		foreach($files as $file) if(is_dir($file)) {
			$stack[] = "$dir/$file";
		}
	}
	return true;
}

// from: http://php.net/manual/en/function.realpath.php#84012
function get_absolute_path($path) {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }

function err($code,$msg) {
	http_response_code($code);
	echo json_encode(['error' => ['code'=>intval($code), 'msg' => $msg]]);
	exit;
}

function asBytes($ini_v) {
	$ini_v = trim($ini_v);
	$s = ['g'=> 1<<30, 'm' => 1<<20, 'k' => 1<<10];
	return intval($ini_v) * ($s[strtolower(substr($ini_v,-1))] ?: 1);
}

$MAX_UPLOAD_SIZE = min(asBytes(ini_get('post_max_size')), asBytes(ini_get('upload_max_filesize')));

if($_GET['logout']==1){
	//$_SESSION['_sfm_allowed'] = false;
	$_SESSION[$SESSION_ID] = false;
	header("Location: $THIS_FILENAME");
}

?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>File Manager</title>
<link href="<?php echo $bs_css ?>" rel="stylesheet">
<link href="<?php echo $fa_css ?>" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/1.6.11/css/lightgallery.min.css"  rel="stylesheet">
<style>

body { margin: 1em; }

table th:last-child, table td:last-child { text-align: right; }
table thead { font-size: 1.2rem; }
table thead a { display: inline-block; }

#breadcrumb { padding: 0 0; font-size: 1rem; }
#breadcrumb .separator { margin: 0 10px; }

#file_drop_target { border: 4px dashed #ddd; padding: 0.5rem; border-radius: 10px; }
#file_drop_target.drag_over { border: 4px dashed #28a745; }

#upload_progress { padding: 4px 0; }
#upload_progress > div { padding: 4px 0; }
.progress { background-color: rgba(0,0,0,0.1); height: 1rem; }

.no_write #mkdir, .no_write #file_drop_target { display: none; }
.sort_hide { display: none; }

td { font-size: 0.9rem; white-space: nowrap; vertical-align: middle !important; }
td.first { font-size: 1rem; white-space: normal; vertical-align: middle !important; font-weight: normal; }
td.empty { font-size: 1rem; font-style: italic; text-align: center !important; padding: 2em 0; }

.is_dir .size { color: transparent; font-size: 0; }
.is_dir .size:before { content: "--"; font-size: 1rem; color: #ccc; font-size: 1rem; }
.is_dir .download { visibility: hidden; }

.is_dir .name:before { content: "\f07b"; font-family: FontAwesome; font-size: 1.5rem; padding-right: 5px; vertical-align: middle; }
.name:before { content: "\f016"; font-family: FontAwesome; font-size: 1.25rem; padding-right: 5px; vertical-align: middle; }

.fa-14 { font-size: 14px; }

span.indicator { float: right; }

.name.is_image:before { content: "\f1c5"; }
.name.is_video:before { content: "\f1c8"; }
.name.is_audio:before { content: "\f1c7"; }
.name.is_text:before { content: "\f0f6"; }
.name.is_doc:before { content: "\f1c2"; }
.name.is_pdf:before { content: "\f1c4"; }
.name.is_xls:before { content: "\f1c3"; }
.name.is_archive:before { content: "\f1c6"; }
.name.is_code:before { content: "\f1c9"; }
.name.is_exe:before { content: "\f085"; }

</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/1.6.11/js/lightgallery-all.min.js"></script>
<script>
(function($){
	$.fn.tablesorter = function() {
		var $table = this;
		this.find('th').click(function() {
			var idx = $(this).index();
			var direction = $(this).hasClass('sort_asc');
			$table.tablesortby(idx,direction);
		});
		return this;
	};
	$.fn.tablesortby = function(idx,direction) {
		var $rows = this.find('tbody tr');
		function elementToVal(a) {
			var $a_elem = $(a).find('td:nth-child('+(idx+1)+')');
			var a_val = $a_elem.attr('data-sort') || $a_elem.text();
			return (a_val == parseInt(a_val) ? parseInt(a_val) : a_val);
		}
		$rows.sort(function(a,b){
			var a_val = elementToVal(a), b_val = elementToVal(b);
			return (a_val > b_val ? 1 : (a_val == b_val ? 0 : -1)) * (direction ? 1 : -1);
		})
		this.find('th').removeClass('sort_asc sort_desc');
		$(this).find('thead th:nth-child('+(idx+1)+')').addClass(direction ? 'sort_desc' : 'sort_asc');
		for(var i =0;i<$rows.length;i++)
			this.append($rows[i]);
		this.settablesortmarkers();
		return this;
	}
	$.fn.retablesort = function() {
		var $e = this.find('thead th.sort_asc, thead th.sort_desc');
		if($e.length)
			this.tablesortby($e.index(), $e.hasClass('sort_desc') );

		return this;
	}
	$.fn.settablesortmarkers = function() {
		this.find('thead th span.indicator').remove();
		this.find('thead th.sort_asc').append('<span class="indicator">&darr;<span>');
		this.find('thead th.sort_desc').append('<span class="indicator">&uarr;<span>');
		return this;
	}
})(jQuery);
$(function(){
	var XSRF = (document.cookie.match('(^|; )_sfm_xsrf=([^;]*)')||0)[2];
	var MAX_UPLOAD_SIZE = <?php echo $MAX_UPLOAD_SIZE ?>;
	var $tbody = $('#list');
	$(window).on('hashchange',list).trigger('hashchange');
	$('#table').tablesorter();

	$('#table').on('click','.delete',function(data) {

	<?php if($delete_confirm): ?>
	if (confirm("Are you sure you want to delete that?")){
	<?php endif; ?>
		$.post("",{'do':'delete',file:$(this).attr('data-file'),xsrf:XSRF},function(response){
			list();
		},'json');
	<?php if($delete_confirm): ?>
	}
	<?php endif; ?>

		return false;
	});

	$('#mkdir').submit(function(e) {
		var hashval = decodeURIComponent(window.location.hash.substr(1)),
			$dir = $(this).find('[name=name]');
		e.preventDefault();
		$dir.val().length && $.post('?',{'do':'mkdir',name:$dir.val(),xsrf:XSRF,file:hashval},function(data){
			list();
		},'json');
		$dir.val('');
		return false;
	});
<?php if($allow_upload): ?>
	// file upload stuff
	$('#file_drop_target').on('dragover',function(){
		$(this).addClass('drag_over');
		return false;
	}).on('dragend',function(){
		$(this).removeClass('drag_over');
		return false;
	}).on('drop',function(e){
		e.preventDefault();
		var files = e.originalEvent.dataTransfer.files;
		$.each(files,function(k,file) {
			uploadFile(file);
		});
		$(this).removeClass('drag_over');
	});
	$('input[type=file]').change(function(e) {
		e.preventDefault();
		$.each(this.files,function(k,file) {
			uploadFile(file);
		});
	});

	function uploadFile(file) {
		var folder = decodeURIComponent(window.location.hash.substr(1));

		if(file.size > MAX_UPLOAD_SIZE) {
			var $error_row = renderFileSizeErrorRow(file,folder);
			$('#upload_progress').append($error_row);
			window.setTimeout(function(){$error_row.fadeOut();},5000);
			return false;
		}

		var $row = renderFileUploadRow(file,folder);
		$('#upload_progress').append($row);
		var fd = new FormData();
		fd.append('file_data',file);
		fd.append('file',folder);
		fd.append('xsrf',XSRF);
		fd.append('do','upload');
		var xhr = new XMLHttpRequest();
		xhr.open('POST', '?');
		xhr.onload = function() {
			$row.remove();
    		list();
  		};
		xhr.upload.onprogress = function(e){
			if(e.lengthComputable) {
				$row.find('.progress-bar').css('width',(e.loaded/e.total*100 | 0)+'%' );
			}
		};
	    xhr.send(fd);
	}
	function renderFileUploadRow(file,folder) {
		return $row = $('<div/>')
			.append( $('<span class="fileuploadname" />').text( (folder ? folder+'/':'')+file.name))
			.append( $('<span class="size pull-right" />').text(formatFileSize(file.size)) )
			.append( $('<div class="progress mb-4"><div class="progress-bar bg-success progress-bar-striped progress-bar-animated"></div></div>')  )
	};

	function renderFileSizeErrorRow(file,folder) {
		return $row = $('<div class="alert alert-danger p-3" />')
			.append( $('<span class="fileuploadname" />').html( '<b>Error:</b> ' + (folder ? folder+'/':'')+file.name))
			.append( $('<span/>').html(' - file size - <b>' + formatFileSize(file.size) + '</b>'
				+' exceeds max upload size of <b>' + formatFileSize(MAX_UPLOAD_SIZE) + '</b>')  );
	}

<?php endif; ?>
	function list() {
		var hashval = window.location.hash.substr(1);
		$.get('?do=list&file='+ hashval,function(data) {
			$tbody.empty();
			$('#breadcrumb').empty().html(renderBreadcrumbs(hashval));
			if(data.success) {
				$.each(data.results,function(k,v){
					$tbody.append(renderFileRow(v));
				});
				!data.results.length && $tbody.append('<tr><td class="empty" colspan=5><i class="fa fa-folder-o"></i> This folder is empty</td></tr>')
				data.is_writable ? $('body').removeClass('no_write') : $('body').addClass('no_write');
			} else {
				console.warn(data.error.msg);
			}
			$('#table').retablesort();
		},'json');
	}

	function getFileExtension(filename){
		var extn = /^.+\.([^.]+)$/.exec(filename);
		return extn == null ? "" : extn[1];
	}

	function renderFileRow(data) {

		var $link = $('<a class="name" />')
			//.attr('href', data.is_dir ? '#' + encodeURIComponent(data.path) : './'+ encodeURIComponent(data.path))
			.attr('href', data.is_dir ? '#' + data.path : './'+data.path)
			.attr('target', data.is_dir ? '_self' : '_blank')
			.text(data.name);

		var $extn = getFileExtension(data.path);

		if ($extn == "jpg" || $extn == "jpeg" || $extn == "pjpeg" || $extn == "gif" || $extn == "png" || $extn == "svg") {
			$($link).removeAttr('target')
			.addClass('is_image')
			.attr('href',data.path)
			.attr('title',data.path).lightGallery({
				selector: 'this',
				counter: false,
				share: false,
				hash: false,
				download: false
			});
		} else if ($extn == "mp4" || $extn == "avi" || $extn == "wmv" || $extn == "mov") {
			$($link).addClass('is_video');
		} else if ($extn == "mp3" || $extn == "ogg" || $extn == "wav" || $extn == "wma") {
			$($link).addClass('is_audio');
		} else if ($extn == "txt") {
			$($link).addClass('is_text');
		} else if ($extn == "doc" || $extn == "docx") {
			$($link).addClass('is_doc');
		} else if ($extn == "pdf" || $extn == "pdfx") {
			$($link).addClass('is_pdf');
		} else if ($extn == "xls" || $extn == "xlsx") {
			$($link).addClass('is_xls');
		} else if ($extn == "zip" || $extn == "rar" || $extn == "tar" || $extn == "iso") {
			$($link).addClass('is_archive');
		} else if ($extn == "html" || $extn == "php" || $extn == "js" || $extn == "css") {
			$($link).addClass('is_code');
		} else if ($extn == "exe" || $extn == "sit") {
			$($link).addClass('is_exe');
		}

		var allow_direct_link = <?php echo $allow_direct_link?'true':'false'; ?>;
        	if (!data.is_dir && !allow_direct_link)  $link.css('pointer-events','none');
		var $dl_link = $('<a/>').attr('href','?do=download&file='+ encodeURIComponent(data.path))
			.addClass('download btn btn-sm btn-primary mr-2').attr('title','Download').html('<i class="fa fa-download fa-14"></i>');
		var $delete_link = $('<a href="#" />').attr('data-file',data.path).attr('title','Delete').addClass('delete btn btn-sm btn-danger').html('<i class="fa fa-trash fa-14"></i>');
		var perms = [];
		if(data.is_readable) perms.push('read');
		if(data.is_writable) perms.push('write');
		if(data.is_executable) perms.push('exec');
		var $html = $('<tr />')
			.addClass(data.is_dir ? 'is_dir' : '')
			.append( $('<td class="first" />').append($link) )
			.append( $('<td/>').attr('data-sort',data.is_dir ? -1 : data.size)
				.html($('<span class="size" />').text(formatFileSize(data.size))) )
			.append( $('<td/>').attr('data-sort',data.mtime).text(formatTimestamp(data.mtime)) )
			.append( $('<td/>').text(perms.join('+')) )
			.append( $('<td/>').append($dl_link).append( data.is_deleteable ? $delete_link : '') )
		return $html;
	}
	function renderBreadcrumbs(path) {
		var base = "",
			$html = $('<div/>').append( $('<i class="fa fa-home"></i> <a href=#>Home</a></div>') );
		$.each(path.split('/'),function(k,v){
			if(v) {
				var v_as_text = decodeURIComponent(v);
				$html.append( $('<span />').html('<i class="fa fa-angle-right separator"></i>') )
					.append( $('<a/>').attr('href','#'+base+v).text(v_as_text) );
				base += v + '/';
			}
		});
		return $html;
	}
	function formatTimestamp(unix_timestamp) {
		var m = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
		var d = new Date(unix_timestamp*1000);
		return [m[d.getMonth()],' ',d.getDate(),', ',d.getFullYear()," ",
			(d.getHours() % 12 || 12),":",(d.getMinutes() < 10 ? '0' : '')+d.getMinutes(),
			" ",d.getHours() >= 12 ? 'PM' : 'AM'].join('');
	}
	function formatFileSize(bytes) {
		var s = ['bytes', 'KB','MB','GB','TB','PB','EB'];
		for(var pos = 0;bytes >= 1000; pos++,bytes /= 1024);
		var d = Math.round(bytes*10);
		return pos ? [parseInt(d/10),".",d%10," ",s[pos]].join('') : bytes + ' bytes';
	}

	$('body').click(function(){
		$('#file_drop_target').removeClass('drag_over');
	});

	$('.refresh').click(function(){
		location.reload();
	});

});
</script>
</head>
<body>

<?php if(!$full_width): ?>
<div class="container p-0">
<?php endif; ?>

<div class="row align-items-center">

<div class="col-md-12 col-lg-3 mb-4 text-center text-lg-left order-1 order-lg-1">
<h3 class="mb-0"><i class="fa fa-file-text-o mr-1"></i> <a href="<?php echo $THIS_FILENAME; ?>"><b>File Manager</b></a></h3>
</div>

<div class="col-md-12 col-lg-3 mb-4 order-3 order-lg-2">
<?php if($allow_create_folder): ?>
<form action="?" method="post" id="mkdir" class="m-0 w-100">
<div class="input-group shadow-sm">
<div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-folder-open-o"></i></span></div>
<input class="form-control" id="dirname" type="text" name="name" value="" placeholder="Folder Name" autocomplete="off" required>
<div class="input-group-append"><button class="btn btn-primary" type="submit" title="Create Folder"><i class="fa fa-plus fa-14"></i></button></div>
</div>
</form>
<?php endif; ?>
</div>

<div class="col-md-12 col-lg-4 mb-4 order-4 order-lg-3">
<?php if($allow_upload): ?>
<div id="file_drop_target">
<div class="custom-file shadow-sm">
<input type="file" class="custom-file-input" id="customFile" multiple>
<label class="custom-file-label" for="customFile">Drop File(s) Here, or</label>
</div>
</div>
<?php endif; ?>
</div>

<div class="col-md-12 col-lg-2 mb-4 text-center text-lg-right order-2 order-lg-4">
<a class="btn btn-primary" href="<?php echo $THIS_FILENAME; ?>" title="Home"><i class="fa fa-home fa-14"></i></a> 
<a class="btn btn-primary refresh" href="javascript:;" title="Refresh"><i class="fa fa-refresh fa-14"></i></a> 
<?php if($PASSWORD): ?>
<a class="btn btn-danger" href="?logout=1" title="Logout"><i class="fa fa-sign-out fa-14"></i></a>
<?php endif; ?>
</div>

</div>

<div id="upload_progress"></div>

<div class="card p-3 mb-4 w-100 shadow-sm" id="breadcrumb">&nbsp;</div>

<div class="table-responsive mb-4">
<table class="table table-bordered table-striped table-hover shadow-sm" id="table">
<thead>
<tr>
<th><a href="javascript:;">Name</a></th>
<th><a href="javascript:;">Size</a></th>
<th><a href="javascript:;">Modified</a></th>
<th><a href="javascript:;">Permissions</a></th>
<th><a href="javascript:;">Actions</a></th>
</tr>
</thead>
<tbody id="list"></tbody>
</table>
</div>

<?php if($show_credit): ?>
<p class="text-center"><small class="text-muted">simple php filemanager by <a href="https://github.com/jcampbell1" target="_blank">jcampbell1</a>. Fork by <a href="https://github.com/xcartmods/simple-file-manager" target="_blank">xcartmods</a></small></p>
<?php endif; ?>

<?php if($full_width): ?>
</div>
<?php endif; ?>

</body>
</html>
