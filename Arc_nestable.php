<?php
/**
 *
 * @package	Codeigniter Library for Nestable Menu Jquery 
 * @author	Fuad Ar-Radhi
 * @link	https://github.com/arradyscode/ci-nestable
 * @since	Version 1.0.0
 *
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Arc_nestable {

	/**
	 * Instance dari Codeigniter
	 * 
	 * @var instance
	 */
	private $arradyscode;


	/**
	 * Simpan JSON String
	 * 
	 * @var string
	 */
	private $json_string;


	/**
	 * Simpan object saat loop
	 * @var array
	 */
	private $return_object = array();


	/**
	 * Iterasi untuk menormalkan ID
	 * @var integer
	 */
	private $iteration = 1;

	
	/**
	 * Normalkan ID
	 * @var array
	 */
	private $normalid = array();


	/**
	 * Simpan data dari CI result
	 * 
	 * @var array
	 */
	private $ci_arr_parent = array();
	private $ci_arr_data = array();


	/**
	 * Init library
	 */
	public function __construct()
	{
		$this->arradyscode =& get_instance();
	}


	/**
	 * Set JSON yang akan digenerate
	 * 
	 * @param string $json_string
	 */
	public function set_json( $json_string = '')
	{
		$this->json_string = $json_string;
	}


	public function get_object()
	{
		$object = json_decode($this->json_string);
		$object = $this->recursive_process($object);

		return $this->return_object;
	}


	/**
	 * Recursive function untuk parse object JSON
	 * 
	 * @param  $objects
	 * @param  $parent
	 * @return 
	 */
	private function recursive_process($objects, $parent = null)
	{
		if (is_array($objects))
		{
			foreach ($objects as $object) {

				$arr_object = (array) $object;
				$arr_keys   = array_keys($arr_object);

				$return = array();

				foreach ($arr_keys as $key)
				{
					if($key != 'children')
						$return[$key] = $object->{$key};
				}

				$return['id'] = $this->iteration;
				$this->normalid[$object->id] = $this->iteration;

				$this->iteration++;

				$return['parent'] = @$this->normalid[$parent];				
				$this->return_object[] = $return;
				
				if(isset($object->children))
					$this->recursive_process($object->children, $object->id);

			}
		}
	}


	/**
	 * Kembalikan nestable menu
	 * 
	 * @param  $ci_result
	 * @return 
	 */
	public function get_nestable($ci_result)
	{
		// simpan data, sekalian parent dan id, digunakan untuk recursive
		foreach ($ci_result as $data) {
			$parent = (empty($data->parent)) ? 0:$data->parent;
			$this->ci_arr_parent[$parent][] = $data->id;
			$this->ci_arr_data[$data->id]['id'] = $data->id;
			$this->ci_arr_data[$data->id]['title'] = $data->title;
			$this->ci_arr_data[$data->id]['url'] = $data->url;
		}

		return $this->recursive_nestable();
	}

	
	/**
	 * Recursive untuk parse nestable menu
	 * @param  integer $parent
	 * @return
	 */
	private function recursive_nestable($parent = 0)
	{

		$exists = isset($this->ci_arr_parent[$parent]);
		$res = $exists ? "<ol class=\"dd-list\">\n":'';

		foreach ($this->ci_arr_parent[$parent] as $li) {

			$data = $this->ci_arr_data[$li];

			$res .= "<li class=\"dd-item dd3-item\" data-title=\"".$data['title']."\" data-id=\"".$data['id']."\" data-url=\"".$data['url']."\">\n";
			$res .= "\t<div class=\"dd-handle dd3-handle\">Drag</div><div class=\"dd3-content\">".$data['title'];
			$res .= "<div class=\"dd-btn dd-btn-trash btn-hapus\"><a><i class=\"fa fa-trash\"></i></a></div><div class=\"dd-btn dd-btn-edit\">
						<a href=\"".site_url('admin/menu/ubah/'.$data['id'])."\"><i class=\"fa fa-pencil\"></i></a></div></div>\n";
			$res .= @$this->recursive_nestable($li);
			$res .= "</li>\n";
		}

		$res .= $exists ? "</ol>\n":'';

		return $res;
	}


	/**
	 * Kembalikan menu dalam format config
	 * 
	 * @param  $ci_result
	 * @return 
	 */
	public function get_generated_menu($ci_result)
	{
		// simpan data, sekalian parent dan id, digunakan untuk recursive
		foreach ($ci_result as $data) {
			$parent = (empty($data->parent)) ? 0:$data->parent;
			$this->ci_arr_parent[$parent][] = $data->id;
			$this->ci_arr_data[$data->id]['id'] = $data->id;
			$this->ci_arr_data[$data->id]['title'] = $data->title;
			$this->ci_arr_data[$data->id]['url'] = base_url($data->url);
		}

		return json_decode(json_encode($this->recursive_generated_menu()));
	}


	/**
	 * Recursive untuk parse generated menu
	 * @param  integer $parent
	 * @return
	 */
	private function recursive_generated_menu($parent = 0)
	{

		$return = array();
		foreach ($this->ci_arr_parent[$parent] as $li) {

			$exists = isset($this->ci_arr_parent[$li]);

			$return[$li] = $this->ci_arr_data[$li];
			$return[$li]['child_exists'] = $exists;
			if ($exists)
				$return[$li]['child'] = @$this->recursive_generated_menu($li);
		}
		return $return;

	}

}