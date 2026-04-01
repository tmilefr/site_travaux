<?php
/*
 * element_img.php
 * image Object in page
 * 
 */


class element_img extends element
{	
	var $path = 'public/files/tmp/';
	var $default = 'assets/img/team/empty.jpg';
	var $class = '';
	var $typeof = 'img';
    var $width = '';

	public function PrepareForDBA($value){

		if (isset($_FILES[$this->name]["name"]) AND $_FILES[$this->name]["size"] > 0 ){
			echo debug($_FILES[$this->name]); 
			$target_dir = str_replace('application', $this->path, APPPATH );
			$target_file = $target_dir . basename($this->name.'_'.$_FILES[$this->name]["name"]);
            
			if (move_uploaded_file($_FILES[$this->name]["tmp_name"], $target_file)) {
				$this->resize($target_file);
			} else {
				die("Sorry, there was an error uploading your file.");
			}
			return basename($target_file);
		} else {
			return $_POST['memory_'.$this->name];
		}
	}

    public function resize($filename){
        // Get new sizes
        list($width, $height) = getimagesize($filename);
        $percent = ($this->width * 100 / $width)/100;
        $newwidth = $width * $percent;
        $newheight = $height * $percent;
        $thumb = imagecreatetruecolor($newwidth, $newheight);
        $source = imagecreatefromjpeg($filename);
        
        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        imagejpeg($thumb, $filename);
    }


	public function RenderFormElement(){
		return  '<input type="file" class="text-center form-control-file custom_file" id="'.$this->name.'" name="'.$this->name.'">
		<input type="hidden" name="memory_'.$this->name.'" id="memory_'.$this->name.'" value="'.$this->value.'">
		<small id="'.$this->name.'HelpBlock" class="form-text text-muted">'.$this->value.'</small>';

	}

	public function Render(){
        $filename = base_url().$this->path.$this->value;
		if (!$this->value)
			$filename = base_url().$this->default;
		return '<img src="'.$filename.'" class="team '.$this->class.'" alt="">';
	}

}

