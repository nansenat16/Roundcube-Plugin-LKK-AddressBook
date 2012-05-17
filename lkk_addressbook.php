<?php

class lkk_addressbook extends rcube_plugin{
	public $task = 'mail';
	public $action = 'compose';
	private $global_address = false;
	private $addrs = array();
	private $groups = array();

	function init(){
		$rcmail = rcmail::get_instance();
		if ($rcmail->action == 'compose'){
			$this->include_script('lkk_abk.js');
			$this->include_stylesheet("skins/default/templates/lkk.css");
		}

		$this->add_texts('localization/');
		$this->register_action('plugin.lkk_abk_show',array($this,'show'));
		$this->register_handler('plugin.target',array($this,'get_t'));
		$this->register_handler('plugin.act_title',array($this,'set_t'));
		$this->register_handler('plugin.table',array($this,'tab'));
		$this->set_groups();
	}

	function show(){
		if (get_input_value('t', RCUBE_INPUT_GET) !== null) {
			$addr=rcmail::get_instance()->get_address_book('sql');
			$addr->set_pagesize(9999);
			//$rt=$addr->list_records(array('name','email','contact_id'));
			$rt=$addr->list_records(array('name','email'));
			//var_dump($rt);
			while($u=$rt->next()){
				//var_dump($u);
				$this->addrs[]=array('name'=>$u['name'],'email'=>$u['email'],'contact_id'=>$u['contact_id']);
				//$this->addrs[]=array('name'=>$u['name'],'email'=>$u['email']);
			}

			//Global AddressBook
			if($this->global_address===true){
				$addr=rcmail::get_instance()->get_address_book('global');
				$addr->set_pagesize(9999);
				//$rt=$addr->list_records(array('name','email','contact_id'));
				$rt=$addr->list_records(array('name','email'));
				while($u=$rt->next()){
					$this->addrs[]=array('name'=>$u['name'],'email'=>$u['email'],'contact_id'=>$u['contact_id']);
					//$this->addrs[]=array('name'=>$u['name'],'email'=>$u['email']);
				}
			}
			//var_dump($this->addrs);
			
			$output=rcmail::get_instance()->output;
			$output->set_pagetitle($this->gettext('title'));
			$output->send('lkk_addressbook.mini_book');
		}
	}

	function get_t(){
		return get_input_value('t', RCUBE_INPUT_GET);
	}

	function set_t(){
		//return $this->gettext('add_to').$this->gettext($this->get_t());
		return $this->gettext('add_to');//.$this->gettext($this->get_t());
	}

	function set_groups(){
		$rcmail = rcmail::get_instance();
		$db = $rcmail->db;
		$user_id = $rcmail->user->data['user_id'];
 		$sql_result = $db->query("SELECT * FROM contactgroups WHERE del=0 and user_id = ? order by name", $user_id);
		while ($sql_arr = $db->fetch_assoc($sql_result)) {
		      $this->groups[] = $sql_arr;
		}
		//var_dump($this->groups);
	}

	function get_group_select_html() {
		 $html = '<div class="group_selector_div"><span>Group: </span><select name="group_selector">';
		 $html .= '<option value="-1">All</option>';
		 foreach ($this->groups as $key => $v) {
		 	 $html .= '<option value="' . $v['contactgroup_id'] . '">' . $v['name'] . '</option>';
		 }
		 $html .= '</select>';
		 $html .= ' <input type="button" name="lkk_button_select_all_to" value="To" />';
		 $html .= '<input type="button" name="lkk_button_select_all_to_off" value="to" />';
		 $html .= ' <input type="button" name="lkk_button_select_all_cc" value="Cc" />';
		 $html .= '<input type="button" name="lkk_button_select_all_cc_off" value="cc" />';
		 $html .= ' <input type="button" name="lkk_button_select_all_bcc" value="Bcc" />';
		 $html .= '<input type="button" name="lkk_button_select_all_bcc_off" value="bcc" /></div>';
		 return $html;
	}
	
	function get_group_ids($contact_id){
		$group_ids = array();
		$rcmail = rcmail::get_instance();
		$db = $rcmail->db;
		//echo ' contact_id='.$contact_id;
 		$sql_result = $db->query("SELECT * FROM contactgroupmembers WHERE contact_id = ?", $contact_id);
		//var_dump($sql_result);
		while ($sql_arr = $db->fetch_assoc($sql_result)) {
		      //print_r($sql_arr);
		      $group_ids[] = $sql_arr['contactgroup_id'];
		}
		//var_dump($group_ids);
		return $group_ids;
	}

	function get_group_id_classes($group_ids) {
		//var_dump($group_ids);
		$class = 'group_id_' . join(' group_id_', $group_ids);
		$class = preg_replace('/(group_id_$|group_id_ )/', '', $class);
		//echo '['.$class.']';
		return $class;
        }

	function tab(){
		$html=$this->get_group_select_html();
		$addr=$this->addrs;
		$html.='<table id="addrs"><tr><th>'.$this->gettext('name').'</th><th>'.$this->gettext('mail').'</th></tr>'."\n";
		for($n=0;$n<count($addr);$n++){
			//print_r($addr);
			$group_classes = $this->get_group_id_classes($this->get_group_ids($addr[$n]['contact_id']));
			$html.='<tr class="group_id_tr '.$group_classes.'"><td>'.$addr[$n]['name'].'</td><td>';
			for($m=0;$m<count($addr[$n]['email']);$m++){
				//if ($m > 0) { $html.='<br />'; }
				$html.='<div class="email_address"><input class="mail_addr_to" type="checkbox" />';
				$html.='<input class="mail_addr_cc" type="checkbox" />';
				$html.='<input class="mail_addr_bcc" type="checkbox" />';
				$html.='<span class="mail_addr">'.$addr[$n]['email'][$m].'</span></div>';
			}
			$html.="</td></tr>\n";
		}
		$html.='</table>';
		return $html;
	}
}

?>
