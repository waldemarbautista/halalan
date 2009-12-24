<?php

class Positions extends Controller {

	var $admin;
	var $settings;

	function Positions()
	{
		parent::Controller();
		$this->admin = $this->session->userdata('admin');
		if (!$this->admin)
		{
			$error[] = e('common_unauthorized');
			$this->session->set_flashdata('error', $error);
			redirect('gate/admin');
		}
		$this->settings = $this->config->item('halalan');
		$this->load->model('Option');
		$option = $this->Option->select(1);
		if ($option['status'])
		{
			$error[] = e('admin_common_running_one');
			$error[] = e('admin_common_running_two');
			$this->session->set_flashdata('error', $error);
			redirect('admin/home');
		}
	}
	
	function index()
	{
		$data['positions'] = $this->Position->select_all();
		$admin['username'] = $this->admin['username'];
		$admin['title'] = e('admin_positions_title');
		$admin['body'] = $this->load->view('admin/positions', $data, TRUE);
		$this->load->view('admin', $admin);
	}

	function add()
	{
		$this->_position('add');
	}

	function edit($id)
	{
		$this->_position('edit', $id);
	}

	function delete($id) 
	{
		if (!$id)
			redirect('admin/positions');
		if ($this->Position->in_use($id))
		{
			$this->session->set_flashdata('messages', array('negative', e('admin_delete_position_in_use')));
		}
		else
		{
			$this->Position->delete($id);
			$this->session->set_flashdata('messages', array('positive', e('admin_delete_position_success')));
		}
		redirect('admin/positions');
	}

	function _position($case, $id = null)
	{
		if ($case == 'add')
		{
			$data['position'] = array('position'=>'', 'description'=>'', 'maximum'=>'', 'ordinality'=>'', 'abstain'=>'1', 'unit'=>'0');
		}
		else if ($case == 'edit')
		{
			if (!$id)
				redirect('admin/positions');
			$data['position'] = $this->Position->select($id);
			if (!$data['position'])
				redirect('admin/positions');
			$this->session->set_flashdata('position', $data['position']); // used in callback rules
		}
		$this->form_validation->set_rules('position', e('admin_position_position'), 'required|callback__rule_position_exists');
		$this->form_validation->set_rules('description', e('admin_position_description'));
		$this->form_validation->set_rules('maximum', e('admin_position_maximum'), 'required|is_natural_no_zero');
		$this->form_validation->set_rules('ordinality', e('admin_position_ordinality'), 'required|is_natural_no_zero');
		$this->form_validation->set_rules('abstain', e('admin_position_abstain'));
		$this->form_validation->set_rules('unit', e('admin_position_unit'));
		if ($this->form_validation->run())
		{
			$position['position'] = $this->input->post('position', TRUE);
			$position['description'] = $this->input->post('description', TRUE);
			$position['maximum'] = $this->input->post('maximum', TRUE);
			$position['ordinality'] = $this->input->post('ordinality', TRUE);
			$position['abstain'] = $this->input->post('abstain', TRUE);
			$position['unit'] = $this->input->post('unit', TRUE);
			if ($case == 'add')
			{
				$this->Position->insert($position);
				$this->session->set_flashdata('messages', array('positive', e('admin_add_position_success')));
				redirect('admin/positions/add');
			}
			else if ($case == 'edit')
			{
				$this->Position->update($position, $id);
				$this->session->set_flashdata('messages', array('positive', e('admin_edit_position_success')));
				redirect('admin/positions/edit/' . $id);
			}
		}
		$data['action'] = $case;
		$admin['title'] = e('admin_' . $case . '_position_title');
		$admin['body'] = $this->load->view('admin/position', $data, TRUE);
		$admin['username'] = $this->admin['username'];
		$this->load->view('admin', $admin);
	}

	function _rule_position_exists()
	{
		$position = trim($this->input->post('position', TRUE));
		if ($test = $this->Position->select_by_position($position))
		{
			$error = FALSE;
			if ($position = $this->session->flashdata('position')) // edit
			{
				if ($test['id'] != $position['id'])
				{
					$error = TRUE;
				}
			}
			else {
				$error = TRUE;
			}
			if ($error)
			{
				$message = e('admin_position_exists') . ' (' . $test['position'] . ')';
				$this->form_validation->set_message('_rule_position_exists', $message);
				return FALSE;
			}
		}
		else
		{
			return TRUE;
		}
	}

}

/* End of file positions.php */
/* Location: ./system/application/controllers/admin/positions.php */