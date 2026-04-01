<?php
/*
 * element.php
 * Object in page
 * 
 */


class element_html extends element
{
	protected $mode; //view, form.
	protected $name   	= null; //unique id ?
	protected $value  	= null;
	protected $values 	= [];
	protected $type 	= '';
	protected $rows 	= 10;
	protected $required 	= false;
	
	public function __construct(){
		parent::__construct();
		if (isset($this->RenderTools))
		{
			$this->RenderTools->_SetHead('assets/vendor/ckeditor5-build-classic/ckeditor.js','js');
			$this->RenderTools->_SetHead('assets/vendor/ckeditor5-build-classic/translations/fr.js','js');
		}
	}

	public function PrepareForDBA($value){
		return htmlspecialchars($value);
	}

	public function RenderFormElement(){

		$js = "<script>
			ClassicEditor
				.create( document.querySelector( '#".$this->name."' ), {
					toolbar: {
						items: [
							'heading',
							'|',
							'bold',
							'italic',
							'link',
							'bulletedList',
							'numberedList',
							'|',
							'outdent',
							'indent',
							'|',
							'blockQuote',
							'insertTable',
							'undo',
							'redo',
							'alignment',
							'fontSize',
							'fontBackgroundColor',
							'fontColor',
							'code'
						]
					},
					language: 'fr',
					table: {
						contentToolbar: [
							'tableColumn',
							'tableRow',
							'mergeTableCells'
						]
					}
				} )
				.then( editor => {
					window.editor = editor;
				} )
				.catch( err => {
					console.error( err.stack );
				} );
		</script>";

		$this->RenderTools->_SetHead($js , 'txt');	

	

		return  "<textarea name='".$this->name."' id='".$this->name."'>".$this->value."</textarea >";
	}
	
	public function Render(){
		return htmlspecialchars_decode($this->value);
	}

}

