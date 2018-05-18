<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;


/**
 * Base class that will add several fields for 
 * @HasLifecycleCallbacks
 */
class File
{
    /**
	 * @Column(type="text")
	 */
	protected $content;
	
	/**
	 * @Column(type="string")
	 */
	protected $description;
	
	/**
	 * @Column(type="string")
	 */
	protected $original_name;
	
	/**
	 * @Column(type="string")
	 */
	protected $mime_type;
	
	/**
	 * This function takes an uploaded file and saves it down to the Entity.
	 *
	 * @param $uploadFile Array containing an HTML uploaded file with the
	 * following indices- name, type, size, tmp_name, error.
	 */
	public function processFile($uploadFile, $description){
		$this->created = new \DateTime();
		$this->updated = new \DateTime();
	
		$this->original_name = $uploadFile['name'];
		$this->mime_type = $uploadFile['type'];
	
		$this->content = base64_encode(gzcompress(file_get_contents($uploadFile['tmp_name'])));
	
		$this->description = $description;
		
		$this->save();
	}
	
	/**
	 * This returns the HTML
	 */
	public function getFile(){
		header('Content-type: ' . $this->mime_type);
		header('Content-Disposition: attachment; filename=' . $this->original_name);
	
		echo gzuncompress(base64_decode($this->content));
	
		die();
	}
}
    
