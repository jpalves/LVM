<?php
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
	require_once($CFG->dirroot.'/filter/dfmtex/lib.php');

	$str_preamble  = "\\usepackage[portuguese]{babel}\n"
	                ."\\usepackage[T1]{fontenc}\n"
	                ."\\usepackage[latin1]{inputenc}\n"
	                ."\\usepackage{courier}\n"
	                ."\\usepackage{amsmath,amssymb,amsfonts}\n"
	                ."\\usepackage{pstricks,pstricks-add,pst-math,pst-xkey}\n"
	                ."\\usepackage{pst-plot}\n";

	$items = array();
	$items[] = new admin_setting_heading       ('filter_dfmtex_latexheading',    get_string('latexsettings', 'admin'),   '');
	$items[] = new admin_setting_configtextarea('filter_dfmtex_latexpreamble',   get_string('latexpreamble','admin'),    '',$str_preamble);
	$items[] = new admin_setting_configtext    ('filter_dfmtex_latexbackground', get_string('backgroundcolour','admin'), '','#FFFFFF');
	$items[] = new admin_setting_configtext    ('filter_dfmtex_density',         get_string('density','admin'),          '','100', PARAM_INT);

	if (PHP_OS=='Linux') {
		$default_filter_tex_pathlatex   = "/usr/bin/latex";
		$default_filter_tex_pathdvips   = "/usr/bin/dvips";
		$default_filter_tex_pathconvert = "/usr/bin/convert";

	} else if (PHP_OS=='Darwin') {
		// most likely needs a fink install (fink.sf.net)
		$default_filter_tex_pathlatex   = "/sw/bin/latex";
		$default_filter_tex_pathdvips   = "/sw/bin/dvips";
		$default_filter_tex_pathconvert = "/sw/bin/convert";

	} else if (PHP_OS=='WINNT' or PHP_OS=='WIN32' or PHP_OS=='Windows') {
		// note: you need Ghostscript installed (standard), miktex (standard)
		// and ImageMagick (install at c:\ImageMagick)
		$default_filter_tex_pathlatex   = "c:\\texmf\\miktex\\bin\\latex.exe ";
		$default_filter_tex_pathdvips   = "c:\\texmf\\miktex\\bin\\dvips.exe ";
		$default_filter_tex_pathconvert = "c:\\imagemagick\\convert.exe ";

	} else {
		$default_filter_tex_pathlatex   = '';
		$default_filter_tex_pathdvips   = '';
		$default_filter_tex_pathconvert = '';
	}

	$items[] = new admin_setting_configexecutable('filter_dfmtex_pathlatex',  get_string('pathlatex', 'admin'),   '', $default_filter_tex_pathlatex);
	$items[] = new admin_setting_configexecutable('filter_dfmtex_pathdvips',  get_string('pathdvips', 'admin'),   '', $default_filter_tex_pathdvips);
	$items[] = new admin_setting_configexecutable('filter_dfmtex_pathconvert',get_string('pathconvert', 'admin'), '', $default_filter_tex_pathconvert);

	// Even if we offer GIF and PNG formats here, in the update callback we check whether
	// all the paths actually point to executables. If they don't, we force the setting 
	// to GIF, as that's the only format mimeTeX can produce.
	$formats = array('gif' => 'GIF', 'png' => 'PNG');
	$items[] = new admin_setting_configselect('filter_dfmtex_convertformat', get_string('convertformat', 'admin'), get_string('configconvertformat', 'admin'), 'gif', $formats);

	foreach ($items as $item) {
		$item->set_updatedcallback('filter_dfmtex_updatedcallback');
		$settings->add($item);
	}
}
?>
