<?php
defined('BASEPATH') or die('No direct script access allowed');
class Mdl_user extends CI_Model	{
	function __construct()	{
		parent::__construct();
		$this->table = 'users';
	}
	function get_user_by_id($user_id)	{
		return true;
		/*$record = $this->db->get_where($this->table, array('id' => $user_id));
		if($record->num_rows() > 0)	{
			return true;
		}	else	{
			return false;
		}*/
	}
}