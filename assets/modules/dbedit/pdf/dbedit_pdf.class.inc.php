<?
define('FPDF_FONTPATH', dirname(realpath(__FILE__)).'/fpdf/font/');

require ('fpdf/fpdf.php');
require ('fpdi/fpdi.php');

class dbedit_PDF extends FPDI {

	var $B;
	var $I;
	var $U;
	var $HREF;
	var $fontList;
	var $issetfont;
	var $issetcolor;
	var $leftmargin;

	function __construct($orientation = 'P', $unit = 'mm', $format = 'A4') {
		$this->FPDF($orientation, $unit, $format);
		//Initialization
		$this->B = 0;
		$this->I = 0;
		$this->U = 0;
		$this->HREF = '';
		$this->fontlist = array('arial', 'times', 'courier', 'helvetica', 'symbol');
		$this->issetfont = false;
		$this->issetcolor = false;
	}

	// html parser

	function WriteHTML($html) {
		$html = str_replace(array('<br />', '<br/>'), array('<br>', '<br>'), $html);
		$html = strip_tags($html, "<b><u><a><img><p><br><ul><li><strong><tr><blockquote>"); //remove all unsupported tags
		$html = str_replace(array("\n", "\r\n", "\r"), array(' ', ' ', ' '), $html);
		$html = str_replace(array('   ', '  '), array(' ', ' '), $html);
		$html = str_replace(array('<br> ', '<p> ', '</p> ', '<ul> ', '</ul> ', '<li> ', '</li> '), array('<br>', '<p>', '</p>', '<ul>', '</ul>', '<li>', '</li>'), $html);

		//die(var_dump($html));

		$a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE); //explodes the string
		foreach ($a as $i => $e) {
			if ($i % 2 == 0) {
				//Text
				if ($this->HREF)
					$this->PutLink($this->HREF, $e);
				else
					$this->Write(4.233, stripslashes($this->txtentities($e)));
			} else {
				//Tag
				if ($e {0} == '/')
					$this->CloseTag(strtoupper(substr($e, 1)));
				else {
					//Extract attributes
					$a2 = explode(' ', $e);
					$tag = strtoupper(array_shift($a2));
					$attr = array();
					foreach ($a2 as $v)
						if (preg_match('#^([^=]*)=["\']?([^"\']*)["\']?$#i', $v, $a3))
							$attr[strtoupper($a3[1])] = $a3[2];
					$this->OpenTag($tag, $attr);
				}
			}
		}
	}

	function OpenTag($tag, $attr) {
		//Opening tag
		switch ($tag) {
			case 'STRONG':
				$this->SetStyle('B', true);
				break;
			case 'B':
			case 'U':
				$this->SetStyle($tag, true);
				break;
			case 'A':
				$this->HREF = $attr['HREF'];
				break;
			case 'IMG':
				if (isset($attr['SRC']) and (isset($attr['WIDTH']) or isset($attr['HEIGHT']))) {
					if (!isset($attr['WIDTH']))
						$attr['WIDTH'] = 0;
					if (!isset($attr['HEIGHT']))
						$attr['HEIGHT'] = 0;
					$this->Image($attr['SRC'], $this->GetX(), $this->GetY(), $this->px2mm($attr['WIDTH']), $this->px2mm($attr['HEIGHT']));
				}
				break;
			case 'TR':
			case 'BLOCKQUOTE':
			case 'BR':
				$this->Ln(4.233);
				break;
			case 'P':
				$this->Ln(8.467);
				break;
			case 'UL':
				$this->leftmargin += 4;
				$this->SetLeftMargin($this->leftmargin);
				break;
			case 'LI':
				$this->Ln(4.233);
				$this->Rect($this->leftmargin - 2, $this->GetY() + 1.484, 1.73, 1.73, 'F');
				$this->SetX($this->leftmargin);
				break;
		}
	}

	function CloseTag($tag) {
		//Closing tag
		if ($tag == 'STRONG')
			$tag = 'B';
		if ($tag == 'B' or $tag == 'U')
			$this->SetStyle($tag, false);
		if ($tag == 'A')
			$this->HREF = '';
		if ($tag == 'UL') {
			$this->leftmargin -= ($this->leftmargin < 23) ? 0 : 4;
			$this->SetLeftMargin($this->leftmargin);
			$this->Ln(1);
		}
	}

	function SetStyle($tag, $enable) {
		//Modify style and select corresponding font
		$this->$tag += ($enable ? 1 : - 1);
		$style = '';
		foreach (array('B', 'U') as $s)
			if ($this->$s > 0)
				$style .= $s;
		$this->SetFont('', $style);
	}

	function PutLink($URL, $txt) {
		//Put a hyperlink
		$this->SetTextColor(0, 0, 255);
		$this->SetStyle('U', true);
		$this->Write(4.233, $txt, $URL);
		$this->SetStyle('U', false);
		$this->SetTextColor(0);
	}

	// Angebot Funktionen
	function Header() {
		$this->SetMargins(19, 30, 25);
		$this->leftmargin = 19;
		$this->SetAutoPageBreak(TRUE, 30);
		if ($this->PageNo() == 1) {
			$this->setSourceFile(dirname(realpath(__FILE__)).'/page1.pdf');
		} else {
			$this->setSourceFile(dirname(realpath(__FILE__)).'/page2.pdf');
		}
		$tplIdx = $this->importPage(1);
		$this->useTemplate($tplIdx, 0, 0, 210);
	}

	function AddText($html) {
		$this->SetY(60);
		$this->SetFillColor(119, 170, 56);
		$this->SetFont('helvetica', '', 10);
		$this->WriteHTML($this->format($html));
	}

	// HELPERS
	// translate html-entities
	function txtentities($html) {
		$trans = get_html_translation_table(HTML_ENTITIES);
		$trans = array_flip($trans);
		$trans['&ndash;'] = '-';
		return strtr($html, $trans);
	}

	function px2mm($px) {
		return $px * 25.4 / 72;
	}

	// detect utf-8
	function detectUTF8($string) {
		return preg_match('%(?:
	    [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
	    |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
	    |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
	    |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
	    |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
	    |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
	    |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
	    )+%xs', $string);
	}

	// make sure that text is iso-latin1 coded
	function format($string, $to = array()) {
		if (!$to['encode']) {
			if ($this->detectUTF8($string)) {
				$string = utf8_decode($string);
			}
		} else {
			if (!$this->detectUTF8($string)) {
				$string = utf8_encode($string);
			}
		}
		return $string;
	}

}

?>
