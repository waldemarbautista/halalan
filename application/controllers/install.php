<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Install extends CI_Controller {

	public function index()
	{
		// TODO: check system requirements like PHP GD, etc.
		$this->load->database();
		if ($this->db->table_exists('admins')) // Maybe we should check all the tables?
		{
			$error = '<p>It looks like Halalan is already installed.';
			$error .= ' Please remove application/controllers/install.php to continue.</p>';
			show_error($error);
		}

		$this->load->library('form_validation');
		$this->load->helper(array('file', 'form', 'halalan', 'password', 'url'));

		$installed = FALSE;
		$this->form_validation->set_rules('username', 'Username', 'required|alpha_numeric');
		$this->form_validation->set_rules('password', 'Password', 'required|matches[passconf]');
		$this->form_validation->set_rules('passconf', 'Confirm Password', 'required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'required');
		$this->form_validation->set_rules('first_name', 'First Name', 'required');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		if ($this->form_validation->run())
		{
			$sqls = explode(';', read_file('./halalan.sql'));
			foreach ($sqls as $sql)
			{
				$sql = trim($sql);
				if ( ! empty($sql))
				{
					$this->db->query($sql);
				}
			}

			$admin['username'] = $this->input->post('username', TRUE);
			$password = $this->input->post('password');
			$admin['password'] = password_hash($password, PASSWORD_DEFAULT);
			$admin['first_name'] = $this->input->post('first_name', TRUE);
			$admin['last_name'] = $this->input->post('last_name', TRUE);
			$admin['email'] = $this->input->post('email', TRUE);
			$admin['admin_id'] = 1;
			$admin['type'] = 'event';
			$admin['is_super'] = 1;
			$this->db->insert('admins', $admin);

			$installed = TRUE;
		}
		$data['installed'] = $installed;
		$this->load->view('install', $data);
	}

}

/* End of file install.php */
/* Location: ./application/controllers/install.php */
