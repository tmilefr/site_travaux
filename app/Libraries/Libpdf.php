<?php

namespace App\Libraries;
		
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;

#[\AllowDynamicProperties]
class Libpdf {
	
	/** @var Dompdf */
	protected $dompdf;
	var $pdf_path = '';
	var $filename = '';
	
	/**
	 * Constructor of class element.
	 * @return void
	 */	
	public function __construct() {
		$this->_init();
		$this->pdf_path = rtrim(ROOTPATH,'/\\').'/public/data/pdf/';
		$this->img_path = rtrim(ROOTPATH,'/\\').'/public/assets/img/'; //base_url().'assets/img/';
		$this->pdf_url_path = base_url().'data/pdf';
	}
	
	public function _init(){
		$options = new Options();
		$options->set('enable_html5_parser', true);
		$options->set('debugPng',false);
		$options->set("enable_remote", true);
		$options->setTempDir($this->pdf_path); 
		
		$pdf = new Dompdf($options);

		$pdf->setPaper('A4', 'portrait');

		$this->dompdf = $pdf;
	}
	
	public function reset(){
		$this->dompdf = null;
		$this->_init();
	}

	function ImgBase64($img){
		$img = base64_encode(file_get_contents( $this->_get('img_path').$img));
		return '<img src="data:image/jpg;base64,'.$img.'" />';
	}

	//not sure that's good place for this ... need to do invoice lib
	/**
	 * @brief Pdf Create with $pdf data and view view
	 * @param $invoice 
	 * @return \CodeIgniter\HTTP\ResponseInterface|null
	 * 
	 * 
	 */
	function DoPdf($datas,$view_pdf,$filename, $stream = false){
		$data_view['datas'] = $datas;
		$data_view['render_object'] = service('renderObject');
		$data_view['bootstrap_tools'] = service('bootstrapTools');
		$data_view['logo'] =  $this->ImgBase64('regio.png');
		$html = view($view_pdf, $data_view);

		//echo debug($html);

		$this->filename = $filename;
		$this->makePdf($html);

		if ($stream){
			// Le PDF est renvoyé au navigateur par la réponse HTTP du contrôleur
			return service('response')
				->setContentType('application/pdf')
				->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
				->setBody($this->dompdf->output());
		}

		return null;
	}
	
	/**
	 * @brief Create PDF File with Html content
	 * @param $html 
	 * @returns 
	 * 
	 * 
	 */
	function makePdf($html){
		try{
			$this->reset();
			$this->dompdf->loadHtml($html);        
			$this->dompdf->render();
			file_put_contents($this->pdf_path.$this->filename, $this->dompdf->output()); 
		} catch (Exception $e) {
			echo 'Exception reçue : ',  $e->getMessage(), "\n";
		}
	}		
	
	/**
	 * Generic set
	 * @return void
	 */
	public function _set($field,$value){
		$this->$field = $value;
	}
	/**
	 * Generic get
	 * @return void
	 */
	public function _get($field){
		return $this->$field;
	}	
}
